<?php

namespace Tests\Feature;

use App\Enums\TestimonialStatus;
use App\Models\Message;
use App\Models\Testimonial;
use App\Support\Retention;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_show_the_business_details(): void
    {
        $this->settings(['legal_name' => 'Jane Example', 'business_address' => "1 High Street\nLondon", 'ico_number' => 'ZB123456', 'contact_email' => 'hi@example.com']);

        foreach (['/privacy' => 'Legal/Privacy', '/cookies' => 'Legal/Cookies', '/terms' => 'Legal/Terms'] as $url => $component) {
            $this->get($url)->assertOk()->assertInertia(fn ($page) => $page
                ->component($component)
                ->where('business.legal_name', 'Jane Example')
                ->where('business.ico_number', 'ZB123456')
                ->where('business.email', 'hi@example.com'));
        }
    }

    public function test_privacy_email_overrides_contact_email(): void
    {
        $this->settings(['contact_email' => 'hi@example.com', 'privacy_email' => 'privacy@example.com']);

        $this->get('/privacy')->assertInertia(fn ($page) => $page->where('business.email', 'privacy@example.com')->where('business.contact_email', 'hi@example.com'));
    }

    public function test_cookie_table_matches_configuration(): void
    {
        $this->get('/cookies')->assertInertia(fn ($page) => $page->where('analytics', false)->has('cookies', 3)->where('cookies.0.name', config('session.cookie')));

        $this->settings(['google_analytics_id' => 'G-TEST123']);
        $this->get('/cookies')->assertInertia(fn ($page) => $page->where('analytics', true)->has('cookies', 5)->where('cookies.4.type', 'Analytics (optional)'));
    }

    public function test_analytics_never_loads_before_consent(): void
    {
        $this->settings(['google_analytics_id' => 'G-TEST123']);

        $html = $this->get('/')->getContent();
        $this->assertStringNotContainsString('googletagmanager.com', $html, 'GA must only be injected by the consent banner after "Accept"');
    }

    public function test_testimonials_need_and_record_consent(): void
    {
        $t = Testimonial::create(['name' => 'Jane']);

        $this->post("/testimonial/{$t->token}", ['rating' => 5, 'body' => 'Brilliant work from start to finish.'])->assertSessionHasErrors('consent');
        $this->assertSame(TestimonialStatus::Invited, $t->fresh()->status);

        $this->post("/testimonial/{$t->token}", ['rating' => 5, 'body' => 'Brilliant work from start to finish.', 'consent' => '1'])->assertSessionHasNoErrors();
        $this->assertNotNull($t->fresh()->consented_at);
    }

    public function test_retention_periods_are_enforced(): void
    {
        $fresh = Message::create(['name' => 'New', 'email' => 'a@b.c', 'body' => 'Hello there, new enquiry.', 'ip_address' => '203.0.113.1']);
        $recent = Message::create(['name' => 'Recent', 'email' => 'a@b.c', 'body' => 'Hello there, recent one.', 'ip_address' => '203.0.113.2']);
        $recent->forceFill(['created_at' => now()->subDays(Retention::ENQUIRY_IP_DAYS + 1)])->save();
        $old = Message::create(['name' => 'Old', 'email' => 'a@b.c', 'body' => 'Hello there, old enquiry.', 'ip_address' => '203.0.113.3']);
        $old->forceFill(['created_at' => now()->subMonths(Retention::ENQUIRY_MONTHS + 1)])->save();

        $staleInvite = Testimonial::create(['name' => 'Never replied']);
        $staleInvite->forceFill(['created_at' => now()->subMonths(Retention::UNUSED_INVITE_MONTHS + 1)])->save();
        $published = Testimonial::create(['name' => 'Kept', 'status' => TestimonialStatus::Approved]);
        $published->forceFill(['created_at' => now()->subYears(3)])->save();

        $this->artisan('privacy:prune')->assertSuccessful();

        $this->assertSame('203.0.113.1', $fresh->fresh()->ip_address, 'recent IPs kept for abuse checks');
        $this->assertNull($recent->fresh()->ip_address, 'IP removed after the IP retention period');
        $this->assertModelMissing($old);
        $this->assertModelMissing($staleInvite);
        $this->assertModelExists($published);
    }

    public function test_dashboard_flags_missing_legal_details(): void
    {
        $this->actingAs($this->admin());

        $this->get('/admin')->assertInertia(fn ($page) => $page->where('legal_gaps', ['your legal name', 'a business address', 'your ICO registration number']));

        $this->settings(['legal_name' => 'Jane Example', 'business_address' => '1 High St', 'ico_number' => 'ZB1', 'legal_entity' => 'Limited company']);
        $this->get('/admin')->assertInertia(fn ($page) => $page->where('legal_gaps', ['your company number']));
    }

    public function test_legal_settings_validate_select_options(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/settings/legal', ['legal_entity' => 'Pirate ship', 'legal_jurisdiction' => 'England and Wales'])
            ->assertSessionHasErrors('legal_entity');
    }
}
