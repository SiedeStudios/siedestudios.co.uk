<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClientProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientFile;
use App\Models\ClientProject;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search'));

        return Inertia::render('Admin/Clients/Index', [
            'clients' => Client::query()
                ->withCount(['users', 'projects', 'projects as active_projects_count' => fn ($q) => $q->whereNotIn('status', [ClientProjectStatus::Completed, ClientProjectStatus::OnHold])])
                ->when($search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('company', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                ->latest('updated_at')
                ->paginate(20)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $client = Client::create($this->validated($request));
        $client->issueInvite();

        return redirect()->route('admin.clients.show', $client)->with('success', "{$client->name} added — send them the invite link below.");
    }

    public function show(Client $client): Response
    {
        $client->load(['users', 'projects.milestones', 'files.user', 'files.project:id,title', 'testimonials.project:id,title']);

        return Inertia::render('Admin/Clients/Show', [
            'client' => [
                ...$client->only(['id', 'name', 'company', 'email', 'notes']),
                'invite_url' => $client->inviteUrl(),
                'invite_expires_at' => $client->inviteUrl() ? $client->invite_expires_at->toIso8601String() : null,
                'created_at' => $client->created_at?->toIso8601String(),
            ],
            'users' => $client->users->map(fn (User $u) => [
                ...$u->only(['id', 'name', 'discord_username', 'avatar']),
                'last_login_at' => $u->last_login_at?->toIso8601String(),
            ]),
            'projects' => $client->projects->map->summaryArray(),
            'invoices' => Invoice::where('client_id', $client->id)->with('clientProject:id,title')->latest('id')->get()->map(fn (Invoice $i) => [
                ...$i->only(['reference', 'total', 'currency']),
                'status' => $i->status->value,
                'due_at' => $i->due_at?->toDateString(),
                'project' => $i->clientProject?->title,
            ]),
            'files' => $client->files->map->toPortalArray('admin.client-files.download'),
            'testimonials' => $client->testimonials->map(fn ($t) => [
                'id' => $t->id,
                'status' => $t->status->value,
                'project' => $t->project?->title,
                'url' => route('testimonial.show', $t->token),
            ]),
            'statuses' => ClientProjectStatus::options(),
            'case_studies' => Project::orderBy('title')->get(['id', 'title']),
            'upload' => ['mimes' => ClientFile::MIMES, 'max_mb' => ClientFile::MAX_KB / 1024],
        ]);
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $client->update($this->validated($request));

        return back()->with('success', 'Client saved.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        // Portal accounts lose access (users.client_id is nulled); files are removed from disk.
        $client->files->each->delete();
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', "{$client->name} deleted.");
    }

    public function invite(Client $client): RedirectResponse
    {
        $client->issueInvite();

        return back()->with('success', 'New invite link created — previous links no longer work.');
    }

    public function revokeInvite(Client $client): RedirectResponse
    {
        $client->revokeInvite();

        return back()->with('success', 'Invite link revoked.');
    }

    public function removeUser(Client $client, User $user): RedirectResponse
    {
        abort_unless($user->client_id === $client->id, 404);
        $user->forceFill(['client_id' => null])->save();

        return back()->with('success', "{$user->name} no longer has portal access.");
    }

    public function upload(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:'.ClientFile::MIMES, 'max:'.ClientFile::MAX_KB],
            'project_id' => ['nullable', 'integer', Rule::exists('client_projects', 'id')->where('client_id', $client->id)],
        ]);

        $project = isset($data['project_id']) ? ClientProject::find($data['project_id']) : null;
        $file = ClientFile::storeUpload($request->file('file'), $client, $project, $request->user());

        return back()->with('success', "{$file->name} shared with {$client->displayName()}.");
    }

    public function download(ClientFile $file): StreamedResponse
    {
        return $file->download();
    }

    public function deleteFile(ClientFile $file): RedirectResponse
    {
        $file->delete();

        return back()->with('success', 'File deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:150'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
