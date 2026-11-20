<?php

namespace App\Http\Controllers\Portal;

use App\Enums\InvoiceStatus;
use App\Enums\ProjectStatus;
use App\Enums\TestimonialStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientFile;
use App\Models\ClientProject;
use App\Models\ClientUpdate;
use App\Models\Invoice;
use App\Services\DiscordNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $client = $this->client($request);
        $projects = $client->projects()->with('milestones')->get();
        $projectIds = $projects->pluck('id');

        return Inertia::render('Portal/Dashboard', [
            'client' => $client->only(['name', 'company']),
            'projects' => $projects->map->summaryArray(),
            'invoices' => $this->invoices($client->invoices()->with('clientProject:id,title')),
            'testimonials' => $client->testimonials()->where('status', TestimonialStatus::Invited)->with('project:id,title')->get()
                ->map(fn ($t) => ['url' => route('testimonial.show', $t->token), 'project' => $t->project?->title]),
            'updates' => ClientUpdate::whereIn('client_project_id', $projectIds)->with('user', 'project:id,title')->latest('id')->limit(6)->get()
                ->map(fn (ClientUpdate $u) => [...$u->toPortalArray(), 'project' => ['id' => $u->client_project_id, 'title' => $u->project->title]]),
            'files' => $client->files()->with('project:id,title', 'user')->limit(8)->get()->map->toPortalArray('portal.files.download'),
            'upload' => ['mimes' => ClientFile::MIMES, 'max_mb' => ClientFile::MAX_KB / 1024],
        ]);
    }

    public function project(Request $request, ClientProject $project): Response
    {
        $this->authorizeProject($request, $project);
        $project->load('milestones', 'updates.user', 'files.user', 'caseStudy:id,title,slug,status');

        return Inertia::render('Portal/Project', [
            'project' => [
                ...$project->summaryArray(),
                'case_study' => $project->caseStudy?->status === ProjectStatus::Published ? ['title' => $project->caseStudy->title, 'url' => route('projects.show', $project->caseStudy)] : null,
            ],
            'milestones' => $project->milestones->map->toPortalArray(),
            'updates' => $project->updates->map->toPortalArray(),
            'files' => $project->files->map->toPortalArray('portal.files.download'),
            'invoices' => $this->invoices($project->invoices()),
            'upload' => ['mimes' => ClientFile::MIMES, 'max_mb' => ClientFile::MAX_KB / 1024],
            'me' => $request->user()->id,
        ]);
    }

    public function postUpdate(Request $request, ClientProject $project, DiscordNotifier $notifier): RedirectResponse
    {
        $this->authorizeProject($request, $project);
        $body = $request->validate(['body' => ['required', 'string', 'max:5000']])['body'];

        $update = $project->updates()->create(['user_id' => $request->user()->id, 'body' => $body]);
        $notifier->portalMessage($project, $update);

        return back()->with('success', 'Message sent.');
    }

    public function upload(Request $request, DiscordNotifier $notifier): RedirectResponse
    {
        $client = $this->client($request);
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:'.ClientFile::MIMES, 'max:'.ClientFile::MAX_KB],
            'project_id' => ['nullable', 'integer', Rule::exists('client_projects', 'id')->where('client_id', $client->id)],
        ]);

        $project = isset($data['project_id']) ? ClientProject::find($data['project_id']) : null;
        $file = ClientFile::storeUpload($request->file('file'), $client, $project, $request->user());
        $notifier->portalUpload($client, $file);

        return back()->with('success', "{$file->name} uploaded.");
    }

    public function download(Request $request, ClientFile $file): StreamedResponse
    {
        abort_unless($file->client_id === $request->user()->client_id, 404);

        return $file->download();
    }

    public function deleteFile(Request $request, ClientFile $file): RedirectResponse
    {
        abort_unless($file->client_id === $request->user()->client_id, 404);
        abort_unless($file->user_id === $request->user()->id, 403, 'You can only remove files you uploaded.');

        $file->delete();

        return back()->with('success', 'File removed.');
    }

    private function client(Request $request): Client
    {
        return $request->user()->client;
    }

    private function authorizeProject(Request $request, ClientProject $project): void
    {
        abort_unless($project->client_id === $request->user()->client_id, 404);
    }

    private function invoices($query): array
    {
        return $query->where('status', '!=', InvoiceStatus::Draft)->get()->map(fn (Invoice $i) => [
            'reference' => $i->reference,
            'total' => $i->total,
            'currency' => $i->currency,
            'status' => $i->status->value,
            'overdue' => $i->status === InvoiceStatus::Open && $i->due_at?->isPast(),
            'issued_at' => $i->issued_at?->toDateString(),
            'due_at' => $i->due_at?->toDateString(),
            'project' => $i->relationLoaded('clientProject') ? $i->clientProject?->title : null,
            'url' => route('pay.show', $i),
        ])->all();
    }
}
