<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Full database backup (schema + data) as a portable .sql file, and restore
 * from a file produced by this controller.
 *
 * Works on MySQL/MariaDB and SQLite without needing mysqldump/sqlite3 binaries.
 */
class BackupController extends Controller
{
    /** Framework tables whose contents are transient and never backed up/restored. */
    private const SKIP_TABLES = [
        'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens',
    ];

    /** How many server-side copies to keep in storage/app/backups. */
    private const KEEP_BACKUPS = 10;

    private const HEADER = '-- Laundry Management System Database Backup';

    public function download()
    {
        try {
            $path = $this->createBackupFile();
        } catch (\Throwable $e) {
            Log::error('Backup failed: ' . $e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Failed to create backup: ' . $e->getMessage());
        }

        Log::info('Backup created: ' . basename($path), ['staff_id' => Session::get('staff.id')]);

        // Keep the server copy (used for "last backup" and as a safety net)
        return response()->download($path, basename($path), [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:51200', // 50 MB
            'confirm' => 'accepted',
        ], [
            'confirm.accepted' => 'Please confirm that you want to replace all current data.',
        ]);

        $sql = file_get_contents($request->file('backup_file')->getRealPath());

        if ($sql === false || !str_starts_with(ltrim($sql, "\xEF\xBB\xBF \r\n"), self::HEADER)) {
            return back()->with('error', 'This file is not a backup created by this system.');
        }

        $driver = DB::connection()->getDriverName();
        if (!preg_match('/^-- Driver: (\w+)/m', $sql, $m) || $m[1] !== $driver) {
            return back()->with('error', 'This backup was created for a different database type and cannot be restored here.');
        }

        // Always take a safety backup before replacing anything
        try {
            $safety = $this->createBackupFile('pre_restore_');
        } catch (\Throwable $e) {
            Log::error('Pre-restore backup failed: ' . $e->getMessage());

            return back()->with('error', 'Could not create a safety backup, restore aborted: ' . $e->getMessage());
        }

        try {
            $this->runStatements($this->splitStatements($sql, $driver), $driver);
        } catch (\Throwable $e) {
            Log::error('Restore failed: ' . $e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'Restore failed: ' . $e->getMessage()
                . ' A safety backup was saved as ' . basename($safety) . '.');
        }

        Log::warning('Database restored from backup', ['staff_id' => Session::get('staff.id')]);

        app()->forgetInstance('system.settings');

        // The staff account in the session may no longer exist after the restore
        Session::flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', 'Database restored successfully. Please log in again.');
    }

    /**
     * Information about the most recent backup on the server, for the settings page.
     */
    public static function lastBackupTime(): ?int
    {
        $files = glob(self::backupDir() . DIRECTORY_SEPARATOR . 'backup_*.sql') ?: [];
        if (!$files) {
            return null;
        }

        return max(array_map('filemtime', $files));
    }

    private static function backupDir(): string
    {
        return storage_path('app' . DIRECTORY_SEPARATOR . 'backups');
    }

    /**
     * Write a full backup to storage/app/backups and return its path.
     */
    private function createBackupFile(string $prefix = ''): string
    {
        @set_time_limit(300);

        $dir = self::backupDir();
        File::ensureDirectoryExists($dir, 0755);

        $driver = DB::connection()->getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb', 'sqlite'], true)) {
            throw new \RuntimeException("Backups are not supported for the '{$driver}' database driver.");
        }

        $path = $dir . DIRECTORY_SEPARATOR . $prefix . 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Cannot write to ' . $dir . '. Check folder permissions.');
        }

        try {
            $this->writeDump($handle, $driver);
        } catch (\Throwable $e) {
            fclose($handle);
            @unlink($path);
            throw $e;
        }

        fclose($handle);
        $this->pruneOldBackups($dir);

        return $path;
    }

    private function writeDump($handle, string $driver): void
    {
        $pdo = DB::connection()->getPdo();
        $tables = $this->tables($driver);

        fwrite($handle, self::HEADER . "\n");
        fwrite($handle, '-- Driver: ' . $driver . "\n");
        fwrite($handle, '-- Generated: ' . now()->toDateTimeString() . "\n");
        fwrite($handle, '-- Tables: ' . count($tables) . "\n\n");

        if ($driver === 'sqlite') {
            fwrite($handle, "PRAGMA foreign_keys=OFF;\n\n");
        } else {
            fwrite($handle, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
        }

        foreach ($tables as $table) {
            $quoted = $this->quoteIdentifier($table, $driver);

            fwrite($handle, "-- Table: {$table}\n");
            fwrite($handle, "DROP TABLE IF EXISTS {$quoted};\n");
            fwrite($handle, rtrim($this->createStatement($table, $driver), ";\n") . ";\n");

            if ($driver === 'sqlite') {
                $indexes = DB::select(
                    "SELECT sql FROM sqlite_master WHERE type = 'index' AND tbl_name = ? AND sql IS NOT NULL",
                    [$table]
                );
                foreach ($indexes as $index) {
                    fwrite($handle, rtrim($index->sql, ";\n") . ";\n");
                }
            }

            $columns = null;
            $batch = [];
            foreach (DB::table($table)->cursor() as $row) {
                $row = (array) $row;
                $columns ??= implode(', ', array_map(fn ($c) => $this->quoteIdentifier($c, $driver), array_keys($row)));
                $batch[] = '(' . implode(', ', array_map(fn ($v) => $this->quoteValue($v, $pdo), $row)) . ')';

                if (count($batch) >= 100) {
                    fwrite($handle, "INSERT INTO {$quoted} ({$columns}) VALUES\n" . implode(",\n", $batch) . ";\n");
                    $batch = [];
                }
            }
            if ($batch) {
                fwrite($handle, "INSERT INTO {$quoted} ({$columns}) VALUES\n" . implode(",\n", $batch) . ";\n");
            }

            fwrite($handle, "\n");
        }

        fwrite($handle, $driver === 'sqlite' ? "PRAGMA foreign_keys=ON;\n" : "SET FOREIGN_KEY_CHECKS=1;\n");
        fwrite($handle, "-- Backup completed\n");
    }

    /** @return string[] */
    private function tables(string $driver): array
    {
        if ($driver === 'sqlite') {
            $names = array_column(DB::select(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
            ), 'name');
        } else {
            $names = array_map(
                fn ($row) => array_values((array) $row)[0],
                DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")
            );
            sort($names);
        }

        return array_values(array_diff($names, self::SKIP_TABLES));
    }

    private function createStatement(string $table, string $driver): string
    {
        if ($driver === 'sqlite') {
            return DB::selectOne("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?", [$table])->sql;
        }

        $row = (array) DB::selectOne('SHOW CREATE TABLE ' . $this->quoteIdentifier($table, $driver));

        return $row['Create Table'] ?? array_values($row)[1];
    }

    private function quoteIdentifier(string $name, string $driver): string
    {
        return $driver === 'sqlite'
            ? '"' . str_replace('"', '""', $name) . '"'
            : '`' . str_replace('`', '``', $name) . '`';
    }

    private function quoteValue($value, \PDO $pdo): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }

    /**
     * Split a dump into statements, respecting quoted strings and comments.
     *
     * @return string[]
     */
    private function splitStatements(string $sql, string $driver): array
    {
        $statements = [];
        $current = '';
        $quote = null;
        $length = strlen($sql);
        $backslashEscapes = $driver !== 'sqlite';

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($quote !== null) {
                $current .= $char;
                if ($backslashEscapes && $char === '\\' && $i + 1 < $length) {
                    $current .= $sql[++$i];
                } elseif ($char === $quote) {
                    $quote = null;
                }
                continue;
            }

            // Skip "-- comment" lines outside of strings
            if ($char === '-' && ($sql[$i + 1] ?? '') === '-' && trim($current) === '') {
                $end = strpos($sql, "\n", $i);
                $i = $end === false ? $length : $end;
                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $current .= $char;
                continue;
            }

            if ($char === ';') {
                if (trim($current) !== '') {
                    $statements[] = trim($current);
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $statements[] = trim($current);
        }

        return $statements;
    }

    private function runStatements(array $statements, string $driver): void
    {
        if ($driver === 'sqlite') {
            // PRAGMA foreign_keys cannot be changed inside a transaction
            DB::statement('PRAGMA foreign_keys=OFF');

            // SQLite silently ignores this PRAGMA inside an open transaction. With
            // foreign keys still on, DROP TABLE would cascade-delete restored rows
            // (e.g. dropping "staffs" deletes every order), so refuse to continue.
            if ((int) (array_values((array) DB::selectOne('PRAGMA foreign_keys'))[0] ?? 1) !== 0) {
                throw new \RuntimeException('Could not disable foreign key checks for the restore.');
            }

            DB::beginTransaction();
            try {
                foreach ($statements as $statement) {
                    if (stripos($statement, 'PRAGMA foreign_keys') === 0) {
                        continue;
                    }
                    DB::unprepared($statement);
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            } finally {
                DB::statement('PRAGMA foreign_keys=ON');
            }

            return;
        }

        // MySQL DDL auto-commits, so a transaction would not help here; the
        // pre-restore safety backup is the rollback path.
        try {
            foreach ($statements as $statement) {
                DB::unprepared($statement);
            }
        } finally {
            DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    private function pruneOldBackups(string $dir): void
    {
        foreach (['backup_*.sql', 'pre_restore_backup_*.sql'] as $pattern) {
            $files = glob($dir . DIRECTORY_SEPARATOR . $pattern) ?: [];
            usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
            foreach (array_slice($files, self::KEEP_BACKUPS) as $old) {
                @unlink($old);
            }
        }
    }
}
