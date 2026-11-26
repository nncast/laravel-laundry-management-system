// Writes public/css/*.min.css and public/js/*.min.js for the shared assets (`npm run minify`).
// Each minified file starts with the SHA-1 of its source; asset_v() only serves it while
// that still matches, so an edited source is never shadowed by an old minified copy.
//
// The JavaScript is obfuscated, not only minified: the browser gets renamed, string-encoded,
// compacted code while app.js stays readable in the repo. It hides how the code reads, not
// what it does. The settings keep to the Content-Security-Policy (nothing evaluated) and keep
// global names (page scripts call window.t, openDialog, money …), as SmartKMS's do.
import { execSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { readFileSync, unlinkSync, writeFileSync } from 'node:fs';

const files = ['public/css/theme.css', 'public/css/app.css', 'public/js/app.js'];

const OBFUSCATOR = [
    '--compact true', '--identifier-names-generator hexadecimal', '--rename-globals false',
    '--string-array true', '--string-array-encoding base64', '--string-array-threshold 0.75',
    '--split-strings false', '--control-flow-flattening false', '--dead-code-injection false',
    '--self-defending false', '--debug-protection false', '--disable-console-output false',
    '--transform-object-keys false', '--unicode-escape-sequence false',
].join(' ');

function obfuscate(source) {
    const out = source.replace(/\.js$/, '.obfuscating.js');
    execSync(`npx --yes javascript-obfuscator@4.1.1 ${source} --output ${out} ${OBFUSCATOR}`, { stdio: 'pipe' });
    const code = readFileSync(out, 'utf8');
    unlinkSync(out);
    return code;
}

for (const source of files) {
    // Line endings normalised, so a checkout with CRLF or LF gives the same hash
    const hash = createHash('sha1').update(readFileSync(source, 'utf8').replace(/\r\n/g, '\n')).digest('hex');
    const minified = source.endsWith('.js')
        ? obfuscate(source)
        : execSync(`npx --yes esbuild@0.24.0 ${source} --minify --log-level=warning`, { encoding: 'utf8' });
    const target = source.replace(/\.(css|js)$/, '.min.$1');
    writeFileSync(target, `/*! source sha1:${hash} */\n${minified}`);
    console.log(`${target}: ${minified.length} bytes`);
}
