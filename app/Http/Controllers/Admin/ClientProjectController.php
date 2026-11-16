<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClientProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientFile;
use App\Models\ClientMilestone;
use App\Models\ClientProject;
use App\Models\ClientUpdate;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClientProjectController extends Controller
{
    public function store(Request $request, Client $client): RedirectResponse
    {
        $project = $client->projects()->create([...$this->validated($request), 'started_at' => $request->input('started_at') ?: now()]);

        return redirect()->route('admin.client-projects.show', $project)->with('success', 'Project created — add milestones so the client can follow progress.');
    }

    public function show(ClientProject $project): Response
    {
        $project->load('client', 'milestones', 'updates.user', 'files.user');

        return Inertia::render('Admin/Clients/Project', [
            'project' => [
                ...$project->summaryArray(),
                ...$project->only(['client_id', 'project_id']),
            ],
            'client' => [...$project->client->only(['id', 'name', 'company']), 'portal_users' => $project->client->users()->count()],
            'milestones' => $project->milestones->map->toPortalArray(),
            'updates' => $project->updates->map->toPortalArray(),
            'files' => $project->files->map->toPortalArray('admin.client-files.download'),
            'invoices' => Invoice::where('client_project_id', $project->id)->latest('id')->get()->map(fn (Invoice $i) => [
                ...$i->only(['reference', 'total', 'currency']),
                'status' => $i->status->value,
                'due_at' => $i->due_at?->toDateString(),
            ]),
            'statuses' => ClientProjectStatus::options(),
            'case_studies' => Project::orderBy('title')->get(['id', 'title']),
            'upload' => ['mimes' => ClientFile::MIMES, 'max_mb' => ClientFile::MAX_KB / 1024],
        ]);
    }

    public function update(Request $request, ClientProject $project): RedirectResponse
    {
        $project->update($this->validated($request));

        return back()->with('success', 'Project saved.');
    }

    public function destroy(ClientProject $project): RedirectResponse
    {
        $client = $project->client;
        $project->delete();

        return redirect()->route('admin.clients.show', $client)->with('success', 'Project deleted.');
    }

    public function addMilestone(Request $request, ClientProject $project): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:160'], 'due_at' => ['nullable', 'date']]);
        $project->milestones()->create([...$data, 'sort_order' => (int) $project->milestones()->max('sort_order') + 1]);

        return back();
    }

    public function updateMilestone(Request $request, ClientMilestone $milestone): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:160'],
            'due_at' => ['sometimes', 'nullable', 'date'],
            'done' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('done', $data)) {
            $milestone->completed_at = $data['done'] ? ($milestone->completed_at ?? now()) : null;
            unset($data['done']);
        }

        $milestone->fill($data)->save();

        return back();
    }

    public function deleteMilestone(ClientMilestone $milestone): RedirectResponse
    {
        $milestone->delete();

        return back();
    }

    public function postUpdate(Request $request, ClientProject $project): RedirectResponse
    {
        $project->updates()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validate(['body' => ['required', 'string', 'max:5000']])['body'],
        ]);

        return back()->with('success', 'Update posted to the client\'s portal.');
    }

    public function deleteUpdate(ClientUpdate $update): RedirectResponse
    {
        $update->delete();

        return back()->with('success', 'Update deleted.');
    }

    public function uploadFile(Request $request, ClientProject $project): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:'.ClientFile::MIMES, 'max:'.ClientFile::MAX_KB]]);
        $file = ClientFile::storeUpload($request->file('file'), $project->client, $project, $request->user());

        return back()->with('success', "{$file->name} shared.");
    }

    public function requestTestimonial(ClientProject $project): RedirectResponse
    {
        $project->loadMissing('client');

        $existing = Testimonial::where('client_id', $project->client_id)
            ->when($project->project_id, fn ($q) => $q->where('project_id', $project->project_id))
            ->where('status', 'invited')
            ->exists();

        if ($existing) {
            return back()->with('error', 'There\'s already an open testimonial request for this client — it shows on their portal.');
        }

        Testimonial::create([
            'client_id' => $project->client_id,
            'project_id' => $project->project_id,
            'name' => $project->client->name,
            'company' => $project->client->company,
        ]);

        return back()->with('success', 'Testimonial requested — it\'s waiting on the client\'s portal.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', Rule::enum(ClientProjectStatus::class)],
            'staging_url' => ['nullable', 'url', 'max:300'],
            'started_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);
    }
}
