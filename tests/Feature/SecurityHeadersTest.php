<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The headers securityheaders.com grades, and a Content-Security-Policy the pages keep to. */
class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        SystemSetting::create(['business_name' => 'Test Laundry']);
    }

    private function assertProtected($response): string
    {
        foreach (['X-Frame-Options' => 'SAMEORIGIN', 'X-Content-Type-Options' => 'nosniff',
                  'Referrer-Policy' => 'strict-origin-when-cross-origin'] as $name => $value) {
            $response->assertHeader($name, $value);
        }
        $this->assertStringContainsString('camera=()', $response->headers->get('Permissions-Policy'));
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-([^']+)'/", $csp);
        $this->assertStringNotContainsString('unsafe-inline', explode(';', explode('script-src', $csp)[1])[0]);
        preg_match("/'nonce-([^']+)'/", $csp, $nonce);

        // Every script on the page runs: inline ones carry this request's nonce.
        preg_match_all('/<script(?![^>]*\bsrc=)(?![^>]*type="application\/json")([^>]*)>/', $response->getContent(), $inline);
        $this->assertNotEmpty($inline[1]);
        foreach ($inline[1] as $attributes) {
            $this->assertStringContainsString('nonce="' . $nonce[1] . '"', $attributes);
        }

        return $csp;
    }

    public function test_the_sign_in_page_is_protected(): void
    {
        $this->assertProtected($this->get(route('login')));
    }

    public function test_a_signed_in_page_is_protected(): void
    {
        $admin = Staff::create(['name' => 'Admin', 'username' => 'admin', 'password' => 'secret123', 'role' => 'admin', 'is_active' => true]);
        $response = $this->withSession(['staff' => ['id' => $admin->id, 'name' => $admin->name, 'role' => 'admin', 'locale' => null]])
            ->get(route('dashboard'));
        $response->assertOk();
        $this->assertProtected($response);
    }

    public function test_hsts_only_over_https(): void
    {
        $this->get(route('login'))->assertHeaderMissing('Strict-Transport-Security');
        $this->get(str_replace('http://', 'https://', route('login')))->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_no_view_has_an_inline_event_handler(): void
    {
        // The policy runs none: onclick="…" would silently do nothing.
        foreach (glob(resource_path('views/{,*/,*/*/}*.blade.php'), GLOB_BRACE) as $file) {
            $this->assertDoesNotMatchRegularExpression('/<[^>]+\son[a-z]+\s*=\s*["\']/i', file_get_contents($file), $file);
        }
    }
}
