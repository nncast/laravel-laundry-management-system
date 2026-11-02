<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The minified CSS/JS must be made from the current sources (`npm run minify`);
 * asset_v() serves them only while they match, and the source otherwise.
 */
class AssetsTest extends TestCase
{
    public function test_minified_assets_match_their_sources(): void
    {
        foreach (['css/theme.css', 'css/app.css', 'js/app.js'] as $source) {
            $min = preg_replace('/\.(css|js)$/', '.min.$1', $source);
            $hash = sha1(str_replace("\r\n", "\n", file_get_contents(public_path($source))));

            $this->assertStringContainsString("sha1:{$hash}", (string) fgets(fopen(public_path($min), 'r')), "$min is out of date: run npm run minify");
            $this->assertStringContainsString($min, asset_v($source));
        }
    }
}
