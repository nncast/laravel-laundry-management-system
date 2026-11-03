<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    /**
     * Switch the language: kept for this browser and, for signed-in staff, on
     * their account so it follows them to other devices.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(app('system.settings')->enabledLocales())],
        ]);

        session(['locale' => $validated['locale']]);
        if ($id = session('staff.id')) {
            Staff::whereKey($id)->update(['locale' => $validated['locale']]);
            session(['staff.locale' => $validated['locale']]);
        }

        return back();
    }

    /**
     * A language's strings for the browser (window.I18N, read by t() in
     * app.js), with the fallback language's under them. The URL carries the
     * file's version, so browsers keep it until a translation changes.
     */
    public function script(Request $request, string $locale)
    {
        abort_unless(array_key_exists($locale, config('locales')), 404);

        $read = fn (string $code) => $code !== 'en' && is_file($path = lang_path("{$code}.json"))
            ? json_decode(file_get_contents($path), true)
            : [];
        $fallback = $request->query('fallback');
        $lines = $read($locale) + (is_string($fallback) && array_key_exists($fallback, config('locales')) ? $read($fallback) : []);

        return response('window.I18N = ' . json_encode((object) $lines, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) . ';', 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
