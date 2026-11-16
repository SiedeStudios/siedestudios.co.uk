<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\TestimonialStatus;
use App\Models\Client;
use App\Models\ClientFile;
use App\Models\ClientProject;
use App\Models\Invoice;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function discord(string $id, string $name = 'Jane Client'): void
    {
        $user = (new SocialiteUser)->map(['id' => $id, 'nickname' => 'jane', 'name' => $name, 'avatar' => null]);
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->andReturn($user);
        Socialite::shouldReceive('driver')->with('discord')->andReturn($provider);
    }

    private function clientUser(?Client $client = null, string $discordId = '222222222222222222'): User
    {
        $client ??= Client::create(['name' => 'Jane', 'company' => 'Acme']);

        return User::create(['name' => 'Jane', 'discord_id' => $discordId, 'client_id' => $client->id]);
    }

    public function test_invite_links_a_discord_account_once(): void
    {
        $client = Client::create(['name' => 'Jane', 'company' => 'Acme']);
        $token = $client->issueInvite();

        $this->get("/portal/invite/{$token}")->assertOk()->assertInertia(fn ($p) => $p->component('Portal/Invite')->where('client.company', 'Acme'));

        $this->discord('333333333333333333');
        $this->get('/auth/discord/callback?code=x')->assertRedirect('/portal');

        $user = User::firstWhere('discord_id', '333333333333333333');
        $this->assertSame($client->id, $user->client_id);
        $this->assertFalse($user->is_admin);
        $this->assertAuthenticatedAs($user);
        $this->assertNull($client->fresh()->invite_token, 'invites are single use');

        $this->post('/logout');
        $this->get("/portal/invite/{$token}")->assertInertia(fn ($p) => $p->where('client', null));
    }

    public function test_expired_invites_do_not_work(): void
    {
        $client = Client::create(['name' => 'Jane']);
        $token = $client->issueInvite();
        $this->travel(Client::INVITE_DAYS + 1)->days();

        $this->get("/portal/invite/{$token}")->assertInertia(fn ($p) => $p->where('client', null));
        $this->discord('333333333333333333');
        $this->get('/auth/discord/callback?code=x')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_strangers_cannot_sign_in_to_the_portal(): void
    {
        $this->get('/auth/discord?for=portal');
        $this->discord('444444444444444444');

        $this->get('/auth/discord/callback?code=x')->assertRedirect('/portal/login')->assertSessionHas('error');
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_returning_clients_sign_in_without_an_invite(): void
    {
        $user = $this->clientUser();
        $this->discord($user->discord_id, 'New Name');

        $this->get('/auth/discord/callback?code=x')->assertRedirect('/portal');
        $this->assertSame('New Name', $user->fresh()->name);
    }

    public function test_admins_still_land_in_the_admin_panel(): void
    {
        $this->discord('111111111111111111');
        $this->get('/auth/discord/callback?code=x')->assertRedirect('/admin');
    }

    public function test_portal_requires_a_client_account(): void
    {
        $this->get('/portal')->assertRedirect('/portal/login');
        $this->actingAs($this->admin())->get('/portal')->assertRedirect('/admin/clients');
    }

    public function test_clients_are_kept_out_of_admin(): void
    {
        $this->actingAs($this->clientUser())->get('/admin')->assertRedirect('/portal');
        $this->assertAuthenticated();
    }

    public function test_dashboard_shows_only_this_clients_published_items(): void
    {
        $user = $this->clientUser();
        $other = Client::create(['name' => 'Other']);
        $project = ClientProject::create(['client_id' => $user->client_id, 'title' => 'New site']);
        $project->milestones()->createMany([['title' => 'Design', 'completed_at' => now()], ['title' => 'Build']]);
        ClientProject::create(['client_id' => $other->id, 'title' => 'Not mine']);

        Invoice::create(['reference' => 'SS-OPEN', 'client_id' => $user->client_id, 'client_name' => 'Jane', 'currency' => 'GBP', 'total' => 100, 'status' => InvoiceStatus::Open]);
        Invoice::create(['reference' => 'SS-DRAFT', 'client_id' => $user->client_id, 'client_name' => 'Jane', 'currency' => 'GBP', 'total' => 50, 'status' => InvoiceStatus::Draft]);
        Invoice::create(['reference' => 'SS-THEIRS', 'client_id' => $other->id, 'client_name' => 'Other', 'currency' => 'GBP', 'total' => 50, 'status' => InvoiceStatus::Open]);
        Testimonial::create(['client_id' => $user->client_id, 'name' => 'Jane']);

        $this->actingAs($user)->get('/portal')->assertOk()->assertInertia(fn ($p) => $p
            ->component('Portal/Dashboard')
            ->has('projects', 1)
            ->where('projects.0.title', 'New site')
            ->where('projects.0.progress', 50)
            ->where('projects.0.next_milestone.title', 'Build')
            ->has('invoices', 1)
            ->where('invoices.0.reference', 'SS-OPEN')
            ->has('testimonials', 1));
    }

    public function test_clients_cannot_open_other_clients_projects_or_files(): void
    {
        $user = $this->clientUser();
        $other = Client::create(['name' => 'Other']);
        $theirs = ClientProject::create(['client_id' => $other->id, 'title' => 'Secret']);
        $admin = $this->admin();
        $file = ClientFile::storeUpload(UploadedFile::fake()->create('plan.pdf', 10), $other, $theirs, $admin);

        $this->actingAs($user);
        $this->get("/portal/projects/{$theirs->id}")->assertNotFound();
        $this->post("/portal/projects/{$theirs->id}/updates", ['body' => 'hi'])->assertNotFound();
        $this->get("/portal/files/{$file->id}")->assertNotFound();
        $this->delete("/portal/files/{$file->id}")->assertNotFound();
        $this->post('/portal/files', ['file' => UploadedFile::fake()->create('x.pdf', 5), 'project_id' => $theirs->id])->assertSessionHasErrors('project_id');
    }

    public function test_clients_upload_download_and_delete_their_own_files(): void
    {
        $user = $this->clientUser();
        $project = ClientProject::create(['client_id' => $user->client_id, 'title' => 'Site']);

        $this->actingAs($user)->post('/portal/files', ['file' => UploadedFile::fake()->create('logo.png', 20, 'image/png'), 'project_id' => $project->id])->assertSessionHas('success');
        $file = ClientFile::sole();
        Storage::disk('local')->assertExists($file->path);
        $this->assertSame($project->id, $file->client_project_id);

        $download = $this->get("/portal/files/{$file->id}")->assertOk();
        $this->assertStringContainsString('attachment', $download->headers->get('Content-Disposition'));
        $this->assertSame('application/octet-stream', $download->headers->get('Content-Type'));

        $this->delete("/portal/files/{$file->id}")->assertSessionHas('success');
        Storage::disk('local')->assertMissing($file->path);
    }

    public function test_clients_cannot_delete_files_the_studio_shared_or_upload_scripts(): void
    {
        $user = $this->clientUser();
        $file = ClientFile::storeUpload(UploadedFile::fake()->create('contract.pdf', 10), $user->client, null, $this->admin());

        $this->actingAs($user)->delete("/portal/files/{$file->id}")->assertForbidden();
        $this->actingAs($user)->post('/portal/files', ['file' => UploadedFile::fake()->create('shell.php', 1, 'application/x-php')])->assertSessionHasErrors('file');
    }

    public function test_client_messages_appear_on_the_timeline(): void
    {
        $user = $this->clientUser();
        $project = ClientProject::create(['client_id' => $user->client_id, 'title' => 'Site']);

        $this->actingAs($user)->post("/portal/projects/{$project->id}/updates", ['body' => 'Can we make the logo bigger?'])->assertSessionHas('success');
        $this->actingAs($user)->get("/portal/projects/{$project->id}")->assertInertia(fn ($p) => $p
            ->component('Portal/Project')->where('updates.0.body', 'Can we make the logo bigger?')->where('updates.0.author.is_studio', false));
    }

    public function test_admin_manages_clients_projects_and_milestones(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/clients', ['name' => 'Bob', 'company' => 'Bobco'])->assertRedirect();
        $client = Client::sole();
        $this->assertNotNull($client->inviteUrl(), 'new clients get an invite link');

        $this->post("/admin/clients/{$client->id}/projects", ['title' => 'Shop'])->assertRedirect();
        $project = ClientProject::sole();
        $this->post("/admin/client-projects/{$project->id}/milestones", ['title' => 'Design']);
        $this->post("/admin/client-projects/{$project->id}/milestones", ['title' => 'Launch']);
        $this->patch('/admin/client-milestones/'.$project->milestones()->first()->id, ['done' => true]);
        $this->assertSame(50, $project->fresh()->progress());

        $this->patch("/admin/client-projects/{$project->id}", ['title' => 'Shop', 'status' => 'completed']);
        $this->assertNotNull($project->fresh()->completed_at);
        $this->assertSame(100, $project->fresh()->progress());

        $this->post("/admin/client-projects/{$project->id}/updates", ['body' => 'Launched!'])->assertSessionHas('success');
        $this->post("/admin/client-projects/{$project->id}/testimonial")->assertSessionHas('success');
        $this->assertSame(TestimonialStatus::Invited, Testimonial::firstWhere('client_id', $client->id)->status);

        foreach (['/admin/clients', "/admin/clients/{$client->id}", "/admin/client-projects/{$project->id}", "/admin/invoices/create?client={$client->id}&project={$project->id}"] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_invoices_can_be_linked_to_a_client_project(): void
    {
        $this->actingAs($this->admin());
        $client = Client::create(['name' => 'Bob']);
        $project = ClientProject::create(['client_id' => $client->id, 'title' => 'Shop']);
        $otherProject = ClientProject::create(['client_id' => Client::create(['name' => 'X'])->id, 'title' => 'Nope']);

        $this->post('/admin/invoices', [
            'client_id' => $client->id, 'client_project_id' => $otherProject->id, 'client_name' => 'Bob', 'currency' => 'GBP',
            'items' => [['description' => 'Work', 'quantity' => 1, 'unit_price' => 10]],
        ])->assertSessionHasErrors('client_project_id');

        $this->post('/admin/invoices', [
            'client_id' => $client->id, 'client_project_id' => $project->id, 'client_name' => 'Bob', 'currency' => 'GBP',
            'items' => [['description' => 'Work', 'quantity' => 1, 'unit_price' => 10]],
        ])->assertRedirect();
        $this->assertSame($project->id, Invoice::sole()->client_project_id);
    }

    public function test_deleting_a_client_ends_portal_access(): void
    {
        $user = $this->clientUser();
        $this->actingAs($this->admin())->delete("/admin/clients/{$user->client_id}")->assertRedirect('/admin/clients');

        $this->assertNull($user->fresh()->client_id);
        $this->actingAs($user->fresh())->get('/portal')->assertRedirect('/portal/login');
    }
}
