// Writes public/css/*.min.css and public/js/*.min.js for the shared assets (`npm run minify`).
// Each minified file starts with the SHA-1 of its source; asset_v() only serves it while
// that still matches, so an edited source is never shadowed by an old minified copy.
import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { readFileSync, writeFileSync } from 'node:fs';

const files = ['public/css/theme.css', 'public/css/app.css', 'public/js/app.js'];

for (const source of files) {
    // Line endings normalised, so a checkout with CRLF or LF gives the same hash
    const hash = createHash('sha1').update(readFileSync(source, 'utf8').replace(/\r\n/g, '\n')).digest('hex');
    const minified = execSync(`npx --yes esbuild@0.24.0 ${source} --minify --log-level=warning`, { encoding: 'utf8' });
    const target = source.replace(/\.(css|js)$/, '.min.$1');
    writeFileSync(target, `/*! source sha1:${hash} */\n${minified}`);
    console.log(`${target}: ${minified.length} bytes`);
}
