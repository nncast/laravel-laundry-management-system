<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Read a Y-m-d date from the request, falling back to $default when missing/invalid.
     */
    protected function dateInput(Request $request, string $key, string $default): string
    {
        $value = (string) $request->input($key, '');

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && checkdate(
            (int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4)
        )) {
            return $value;
        }

        return $default;
    }

    /**
     * Stream rows as a CSV download (UTF-8 with BOM so Excel shows ₱ correctly).
     *
     * @param iterable<array> $rows
     */
    protected function csvDownload(string $filename, iterable $rows)
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                fputcsv($out, array_map([$this, 'csvCell'], $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Neutralise spreadsheet formula injection (=, +, -, @ at the start of text).
     */
    protected function csvCell($value)
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)
            && !is_numeric($value)) {
            return "'" . $value;
        }

        return $value;
    }
}
