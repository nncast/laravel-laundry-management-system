<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\Staff;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    private Staff $admin;

    protected function setUp(): void
    {
        parent::setUp();

        SystemSetting::create(['business_name' => 'Test Laundry']);
        $this->admin = Staff::create(['name' => 'Admin', 'username' => 'admin', 'password' => 'secret123', 'role' => 'admin', 'is_active' => true]);
    }

    private function as(Staff $staff): static
    {
        return $this->withSession(['staff' => ['id' => $staff->id, 'name' => $staff->name, 'role' => $staff->role, 'locale' => $staff->locale]]);
    }

    /** Every string the app shows: __('…'), t('…'), and both sides of __(cond ? '…' : '…'). */
    private function usedKeys(): array
    {
        $string = "'((?:[^'\\\\]|\\\\.)*)'|\"((?:[^\"\\\\]|\\\\.)*)\"";
        $files = array_merge(
            glob(app_path('{,*/,*/*/,*/*/*/}*.php'), GLOB_BRACE),
            glob(resource_path('views/{,*/}*.blade.php'), GLOB_BRACE),
            [public_path('js/app.js')],
        );
        $keys = ['Receipt', 'Claim ticket', 'Job order'];
        foreach ($files as $file) {
            $source = file_get_contents($file);
            preg_match_all("/(?<![\\w\$>.])(?:__|t|trans_choice)\\(\\s*(?:$string)/", $source, $calls, PREG_SET_ORDER);
            preg_match_all("/(?<![\\w\$>.])(?:__|t)\\(\\s*[^'\"(),]*?\\?\\s*(?:$string)\\s*:\\s*(?:$string)/", $source, $choices, PREG_SET_ORDER);
            foreach ([...$calls, ...$choices] as $match) {
                foreach (array_slice($match, 1) as $text) {
                    if ($text !== '') {
                        $keys[] = stripcslashes($text);
                    }
                }
            }
        }
        foreach ([config('currencies'), config('locales')] as $list) {
            $keys = array_merge($keys, array_column($list, 'name'));
        }

        return array_unique($keys);
    }

    public function test_every_string_is_translated_in_every_language(): void
    {
        $keys = $this->usedKeys();
        $this->assertGreaterThan(600, count($keys));
        $placeholders = fn (string $text) => preg_match_all('/:[a-z]+/', $text, $m) ? collect($m[0])->sort()->values()->all() : [];

        foreach (array_keys(config('locales')) as $locale) {
            if ($locale === 'en') {
                continue;
            }
            $lines = json_decode(file_get_contents(lang_path("$locale.json")), true);
            $this->assertIsArray($lines, "lang/$locale.json is not valid JSON");
            $this->assertSame([], array_values(array_diff($keys, array_keys($lines))), "Missing in lang/$locale.json");
            foreach ($keys as $key) {
                $this->assertSame($placeholders($key), $placeholders($lines[$key]), "Placeholders differ in $locale: $key");
            }
            $this->assertFileExists(lang_path("$locale/validation.php"));
            $this->assertArrayHasKey('required', require lang_path("$locale/validation.php"));
        }
    }

    public function test_staff_pick_a_language_that_stays_on_their_account(): void
    {
        $this->as($this->admin)->get('/dashboard')->assertSee('Orders')->assertSee('<html lang="en" dir="ltr">', false);

        $this->as($this->admin)->from('/dashboard')->post('/language', ['locale' => 'fil'])->assertRedirect('/dashboard');
        $this->assertSame('fil', $this->admin->fresh()->locale);

        // The same account on another device: its language comes from the account
        $this->withSession(['staff' => ['id' => $this->admin->id, 'name' => 'Admin', 'role' => 'admin', 'locale' => 'fil']])
            ->get('/orders')->assertSee('Mga order')->assertSee('<html lang="fil"', false);

        $this->as($this->admin)->post('/language', ['locale' => 'xx'])->assertSessionHasErrors('locale');
    }

    public function test_guests_pick_a_language_on_the_sign_in_page(): void
    {
        $this->get(route('login'))->assertSee('Sign in');
        $this->post('/language', ['locale' => 'ja']);
        $this->get(route('login'))->assertSee('サインイン')->assertSee('<html lang="ja"', false);
        $this->post('/login', ['username' => 'admin', 'password' => 'wrong'])->assertSessionHas('error', 'ユーザー名またはパスワードが正しくありません。');
    }

    public function test_admins_choose_the_languages_default_and_fallback(): void
    {
        $this->as($this->admin)->post('/settings/language', ['enabled_locales' => ['en', 'es'], 'default_locale' => 'es', 'fallback_locale' => 'en'])
            ->assertSessionHas('success');
        $settings = SystemSetting::first();
        $this->assertSame(['en', 'es'], $settings->enabled_locales);

        // A staff member without a choice gets the default; a language that was switched off is ignored
        $this->as($this->admin)->get('/dashboard')->assertSee('Pedidos');
        $this->admin->update(['locale' => 'ja']);
        $this->as($this->admin->fresh())->get('/dashboard')->assertSee('Pedidos')->assertDontSee('ダッシュボード');
        $this->as($this->admin)->post('/language', ['locale' => 'ja'])->assertSessionHasErrors('locale');

        $this->as($this->admin)->post('/settings/language', ['enabled_locales' => ['en'], 'default_locale' => 'es', 'fallback_locale' => 'en'])
            ->assertSessionHasErrors('default_locale');
        $this->as($this->admin)->post('/settings/language', ['enabled_locales' => [], 'default_locale' => 'en', 'fallback_locale' => 'en'])
            ->assertSessionHasErrors('enabled_locales');
    }

    public function test_a_missing_translation_falls_back_to_the_fallback_language(): void
    {
        SystemSetting::first()->update(['fallback_locale' => 'es']);
        $this->as($this->admin)->get('/dashboard');   // sets up the request's languages

        app()->setLocale('fil');
        app('translator')->addLines(['*.Only in Spanish' => 'Solo en español'], 'es');
        $this->assertSame('Solo en español', __('Only in Spanish'));
        $this->assertSame('Mga order', __('Orders'));
        app()->setLocale('en');
        $this->assertSame('Only in Spanish', __('Only in Spanish'));   // English is the source, never a fallback
    }

    public function test_browser_strings_come_with_the_fallback_underneath(): void
    {
        $script = $this->get('/lang/fil.js?fallback=es')->assertOk()->assertHeader('Content-Type', 'application/javascript; charset=utf-8')->getContent();
        $this->assertStringStartsWith('window.I18N = {', $script);
        $this->assertStringContainsString('"Undo":"I-undo"', $script);
        $this->get('/lang/xx.js')->assertNotFound();
        $this->assertSame('window.I18N = {};', $this->get('/lang/en.js')->getContent());
    }

    public function test_automatic_formats_follow_the_language(): void
    {
        SystemSetting::first()->update(['number_format' => 'auto', 'date_format' => 'auto']);
        $this->as($this->admin)->post('/settings/region', ['currency' => 'EUR', 'number_format' => 'auto', 'date_format' => 'auto', 'time_format' => '24h'])
            ->assertSessionHas('success');

        $this->admin->update(['locale' => 'es']);
        $this->as($this->admin->fresh())->get('/dashboard')->assertOk();
        $this->assertSame('€1.234,50', money(1234.5));
        $this->assertSame('3 nov. 2026', fmt_date('2026-11-03'));
    }

    public function test_printed_documents_follow_the_language(): void
    {
        $type = ServiceType::create(['name' => 'Laundry', 'is_active' => true]);
        $service = Service::create(['name' => 'Wash', 'service_type_id' => $type->id, 'price' => 100, 'is_active' => true]);
        $customer = Customer::create(['name' => 'Juan']);
        $order = Order::create(['customer_id' => $customer->id, 'staff_id' => $this->admin->id, 'status' => 'pending']);
        $order->items()->create(['service_id' => $service->id, 'price' => 100, 'rate' => 1, 'qty' => 1]);

        $this->admin->update(['locale' => 'zh_CN']);
        $this->as($this->admin->fresh())->get("/orders/{$order->id}/print/ticket")->assertSee('取件单')->assertSee('<html lang="zh-CN"', false);
    }
}
