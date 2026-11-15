<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\ProjectStatus;
use App\Enums\TestimonialStatus;
use App\Models\Invoice;
use App\Models\Message;
use App\Models\Project;
use App\Models\Service;
use App\Models\Share;
use App\Models\Testimonial;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_render(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn ($page) => $page->component('Home')->has('projects', 4)->has('services', 4));
        $this->get('/projects')->assertOk()->assertInertia(fn ($page) => $page->component('Projects/Index')->has('categories', 3));
        $this->get('/projects/sandervk-tours')->assertOk()->assertInertia(fn ($page) => $page->component('Projects/Show')->where('project.title', 'SanderVK Tours'));
        $this->get('/services')->assertOk()->assertInertia(fn ($page) => $page->component('Services'));
        $this->get('/contact?service=gmod-addons')->assertOk()->assertInertia(fn ($page) => $page->component('Contact')->where('preselected', Service::firstWhere('slug', 'gmod-addons')->id));
        $this->get('/pay')->assertOk();
    }

    public function test_draft_projects_are_hidden_from_visitors(): void
    {
        Project::where('slug', 'sandervk-com')->update(['status' => ProjectStatus::Draft]);

        $this->get('/projects/sandervk-com')->assertNotFound();
        $this->actingAs($this->admin())->get('/projects/sandervk-com')->assertOk();
    }

    public function test_contact_form_stores_message(): void
    {
        $service = Service::first();

        $this->post('/contact', [
            'name' => 'Visitor', 'email' => 'v@example.com', 'service_id' => $service->id,
            'budget' => '£250 – £1,000', 'body' => 'I would like a custom addon for my server please.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('messages', ['email' => 'v@example.com', 'service_name' => $service->title]);
    }

    public function test_contact_form_validates_and_blocks_honeypot(): void
    {
        $this->post('/contact', ['name' => '', 'email' => 'nope', 'body' => 'short'])->assertSessionHasErrors(['name', 'email', 'body']);
        $this->post('/contact', ['name' => 'Bot', 'email' => 'b@example.com', 'body' => str_repeat('spam ', 10), 'website' => 'http://spam'])->assertSessionHasErrors('website');
        $this->assertSame(0, Message::count());
    }

    public function test_invoice_pay_page_and_lookup(): void
    {
        $this->settings(['paypal_email' => 'pay@example.com']);
        $invoice = Invoice::create(['reference' => 'SS-2611-XYZ999', 'client_name' => 'C', 'currency' => 'GBP', 'total' => 50, 'status' => InvoiceStatus::Open]);

        $this->get('/pay/SS-2611-XYZ999')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Pay/Show')
            ->where('checkout.fields.invoice', 'SS-2611-XYZ999')
            ->where('checkout.fields.amount', '50.00')
            ->where('checkout.fields.business', 'pay@example.com')
            ->where('checkout.fields.notify_url', route('paypal.ipn')));

        $this->post('/pay', ['reference' => 'ss-2611-xyz999'])->assertRedirect('/pay/SS-2611-XYZ999');
        $this->post('/pay', ['reference' => 'missing'])->assertSessionHasErrors('reference');

        $invoice->update(['status' => InvoiceStatus::Draft]);
        $this->get('/pay/SS-2611-XYZ999')->assertNotFound();
    }

    public function test_paid_invoice_has_no_checkout(): void
    {
        $this->settings(['paypal_email' => 'pay@example.com']);
        Invoice::create(['reference' => 'SS-PAID', 'client_name' => 'C', 'currency' => 'GBP', 'total' => 50, 'status' => InvoiceStatus::Paid, 'paid_at' => now()]);

        $this->get('/pay/SS-PAID')->assertInertia(fn ($page) => $page->where('checkout', null)->where('invoice.status', 'paid'));
    }

    public function test_testimonial_invite_can_be_submitted_once(): void
    {
        $testimonial = Testimonial::create(['name' => 'Jane Client', 'status' => TestimonialStatus::Invited]);

        $this->get("/testimonial/{$testimonial->token}")->assertOk()->assertInertia(fn ($page) => $page->where('submitted', false)->missing('invite.token'));

        $this->post("/testimonial/{$testimonial->token}", ['rating' => 5, 'body' => 'Brilliant work, delivered early and exactly as specified.'])->assertSessionHas('success');
        $this->assertSame(TestimonialStatus::Pending, $testimonial->fresh()->status);

        $this->post("/testimonial/{$testimonial->token}", ['rating' => 1, 'body' => 'Trying to overwrite the testimonial text.'])->assertSessionHas('error');
        $this->assertSame(5, $testimonial->fresh()->rating);
        $this->get('/testimonial/not-a-real-token')->assertNotFound();
    }

    public function test_only_approved_testimonials_are_public(): void
    {
        Testimonial::create(['name' => 'Approved', 'status' => TestimonialStatus::Approved, 'rating' => 5, 'body' => 'Great']);
        Testimonial::create(['name' => 'Pending', 'status' => TestimonialStatus::Pending, 'rating' => 5, 'body' => 'Hidden']);

        $this->get('/')->assertInertia(fn ($page) => $page->has('testimonials', 1)->where('testimonials.0.name', 'Approved')->missing('testimonials.0.token'));
    }

    public function test_share_views_password_burn_and_expiry(): void
    {
        $share = Share::create(['content' => 'print("hi")', 'language' => 'lua']);
        $this->get("/s/{$share->slug}")->assertOk()->assertInertia(fn ($page) => $page->component('Share/Show'));
        $this->get("/s/{$share->slug}/raw")->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->assertSee('print("hi")', false);
        $this->assertSame(2, $share->fresh()->views);

        $locked = Share::create(['content' => 'secret', 'password' => 'hunter2']);
        $this->get("/s/{$locked->slug}")->assertInertia(fn ($page) => $page->component('Share/Locked')->missing('content'));
        $this->get("/s/{$locked->slug}/raw")->assertForbidden();
        $this->post("/s/{$locked->slug}/unlock", ['password' => 'wrong'])->assertSessionHasErrors('password');
        $this->post("/s/{$locked->slug}/unlock", ['password' => 'hunter2'])->assertRedirect("/s/{$locked->slug}");
        $this->get("/s/{$locked->slug}")->assertInertia(fn ($page) => $page->component('Share/Show'));

        $burn = Share::create(['content' => 'once', 'burn_after_read' => true]);
        $this->get("/s/{$burn->slug}")->assertOk();
        $this->get("/s/{$burn->slug}")->assertNotFound();

        $expired = Share::create(['content' => 'old', 'expires_at' => now()->subMinute()]);
        $this->get("/s/{$expired->slug}")->assertStatus(410);
    }
}
