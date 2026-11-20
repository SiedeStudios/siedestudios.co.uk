<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\TestimonialStatus;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Share;
use App\Models\Testimonial;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs($this->admin());
    }

    public function test_all_admin_pages_render(): void
    {
        foreach (['/admin', '/admin/projects', '/admin/projects/create', '/admin/categories', '/admin/services', '/admin/services/create',
            '/admin/testimonials', '/admin/testimonials/create', '/admin/invoices', '/admin/invoices/create', '/admin/payments',
            '/admin/shares', '/admin/shares/create', '/admin/messages', '/admin/settings', '/admin/settings/payments'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_invoice_is_created_with_reference_and_correct_total(): void
    {
        $this->settings(['invoice_prefix' => 'SIEDE']);

        $this->post('/admin/invoices', [
            'client_name' => 'Client', 'currency' => 'GBP', 'publish' => true,
            'items' => [
                ['description' => 'Design', 'quantity' => 1, 'unit_price' => 199.99],
                ['description' => 'Hours', 'quantity' => 2.5, 'unit_price' => 40.10],
            ],
        ])->assertRedirect();

        $invoice = Invoice::with('items')->sole();
        $this->assertStringStartsWith('SIEDE-', $invoice->reference);
        $this->assertSame('300.24', $invoice->total);
        $this->assertSame(InvoiceStatus::Open, $invoice->status);
        $this->assertCount(2, $invoice->items);
    }

    public function test_paid_invoices_cannot_be_edited_or_deleted(): void
    {
        $invoice = Invoice::create(['reference' => 'SS-1', 'client_name' => 'C', 'currency' => 'GBP', 'total' => 10, 'status' => InvoiceStatus::Paid]);

        $this->put("/admin/invoices/{$invoice->reference}", ['client_name' => 'X', 'currency' => 'GBP', 'items' => [['description' => 'a', 'quantity' => 1, 'unit_price' => 1]]])->assertForbidden();
        $this->delete("/admin/invoices/{$invoice->reference}")->assertSessionHas('error');
        $this->assertModelExists($invoice);
    }

    public function test_project_with_cover_and_screenshots(): void
    {
        $category = ProjectCategory::create(['name' => 'Web', 'slug' => 'web']);

        $this->post('/admin/projects', [
            'title' => 'New Platform', 'summary' => 'A platform.', 'status' => 'published',
            'project_category_id' => $category->id, 'tags' => 'Laravel, Vue', 'is_featured' => '1',
            'cover' => UploadedFile::fake()->image('cover.png', 1600, 1000),
        ])->assertRedirect();

        $project = Project::sole();
        $this->assertSame('new-platform', $project->slug);
        $this->assertSame(['Laravel', 'Vue'], $project->tags);
        $this->assertNotNull($project->published_at);
        Storage::disk('public')->assertExists($project->cover_image);

        $this->post("/admin/projects/{$project->id}/images", ['images' => [UploadedFile::fake()->image('a.png'), UploadedFile::fake()->image('b.jpg')]])->assertSessionHas('success');
        $this->assertSame(2, $project->images()->count());

        $this->delete("/admin/projects/{$project->id}")->assertRedirect('/admin/projects');
        Storage::disk('public')->assertMissing($project->cover_image);
    }

    public function test_testimonial_invite_and_moderation(): void
    {
        $project = Project::create(['title' => 'P', 'slug' => 'p', 'summary' => 's', 'status' => 'published']);

        $this->post('/admin/testimonials', [
            'name' => 'Jane', 'company' => 'Acme', 'project_id' => $project->id,
            'avatar' => UploadedFile::fake()->image('jane.jpg', 200, 200),
        ])->assertRedirect();

        $testimonial = Testimonial::sole();
        $this->assertSame(TestimonialStatus::Invited, $testimonial->status);
        $this->assertSame(40, strlen($testimonial->token));
        Storage::disk('public')->assertExists($testimonial->avatar);

        $testimonial->update(['status' => TestimonialStatus::Pending, 'body' => 'Great', 'rating' => 5]);
        $this->patch("/admin/testimonials/{$testimonial->id}/status", ['status' => 'approved'])->assertSessionHas('success');
        $this->assertNotNull($testimonial->fresh()->approved_at);
        $this->assertCount(1, $project->approvedTestimonials()->get());
    }

    public function test_settings_are_saved_with_uploads(): void
    {
        $this->post('/admin/settings/general', ['site_name' => 'Siede', 'site_tagline' => 'New tagline here', 'is_available' => '0', 'founded_year' => '2020'])->assertSessionHas('success');
        $this->post('/admin/settings/branding', ['favicon' => UploadedFile::fake()->image('icon.png', 64, 64), 'accent_color' => '#B0102F'])->assertSessionHas('success');

        $settings = app(Settings::class);
        $settings->flush();
        $this->assertSame('Siede', $settings->get('site_name'));
        $this->assertFalse($settings->bool('is_available'));
        $this->assertSame('#B0102F', $settings->get('accent_color'));
        Storage::disk('public')->assertExists($settings->get('favicon'));

        $this->post('/admin/settings/branding', ['accent_color' => 'red'])->assertSessionHasErrors('accent_color');
        $this->post('/admin/settings/notifications', ['discord_webhook_url' => 'https://evil.example.com/hook'])->assertSessionHasErrors('discord_webhook_url');
        $this->get('/admin/settings/nope')->assertNotFound();
    }

    public function test_secret_settings_are_never_shared_publicly(): void
    {
        $this->settings(['paypal_email' => 'secret-pay@example.com', 'discord_webhook_url' => 'https://discord.com/api/webhooks/1/secret']);

        auth()->logout();
        $this->get('/contact')->assertInertia(fn ($page) => $page->missing('site.paypal_email')->missing('site.discord_webhook_url')->missing('site.admin_discord_ids'));
    }

    public function test_share_creation_with_password_and_expiry(): void
    {
        $this->post('/admin/shares', ['title' => 'cfg.lua', 'content' => 'return {}', 'language' => 'lua', 'password' => 'letmein', 'expiry' => '1d'])->assertRedirect();

        $share = Share::sole();
        $this->assertTrue($share->isProtected());
        $this->assertNotSame('letmein', $share->password);
        $this->assertTrue($share->expires_at->between(now()->addHours(23), now()->addHours(25)));
    }
}
