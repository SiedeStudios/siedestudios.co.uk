<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TestimonialStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Project;
use App\Models\Testimonial;
use App\Support\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->string('status')->toString();
        $status = TestimonialStatus::tryFrom($status)?->value;

        $counts = Testimonial::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => Testimonial::with('project:id,title')
                ->when($status, fn ($q) => $q->where('status', $status))
                ->orderByRaw("case status when 'pending' then 0 when 'invited' then 1 when 'approved' then 2 else 3 end")
                ->latest('id')
                ->get()
                ->map(fn (Testimonial $t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'company' => $t->company,
                    'role' => $t->role,
                    'avatar' => $t->avatar_url,
                    'body' => $t->body,
                    'rating' => $t->rating,
                    'status' => $t->status->value,
                    'is_featured' => $t->is_featured,
                    'project' => $t->project?->title,
                    'invite_url' => route('testimonial.show', $t->token),
                    'submitted_at' => $t->submitted_at?->toIso8601String(),
                ]),
            'counts' => $counts,
            'filter' => $status,
        ]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(TestimonialRequest $request): RedirectResponse
    {
        $testimonial = new Testimonial(['status' => TestimonialStatus::Invited]);
        $this->fill($testimonial, $request);
        $testimonial->save();

        return redirect()->route('admin.testimonials.edit', $testimonial)
            ->with('success', 'Invite created — copy the link and send it to '.$testimonial->name.'.');
    }

    public function edit(Testimonial $testimonial): Response
    {
        return $this->form($testimonial);
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->fill($testimonial, $request);
        $testimonial->save();

        return back()->with('success', 'Testimonial saved.');
    }

    public function status(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $status = TestimonialStatus::from($request->validate([
            'status' => ['required', Rule::enum(TestimonialStatus::class)],
        ])['status']);

        $testimonial->update([
            'status' => $status,
            'approved_at' => $status === TestimonialStatus::Approved ? now() : null,
        ]);

        return back()->with('success', 'Testimonial '.$status->value.'.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        Media::delete($testimonial->avatar);
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
    }

    private function form(?Testimonial $testimonial): Response
    {
        return Inertia::render('Admin/Testimonials/Form', [
            'testimonial' => $testimonial ? [
                ...$testimonial->only(['id', 'name', 'company', 'role', 'project_id', 'body', 'rating', 'is_featured']),
                'status' => $testimonial->status->value,
                'avatar' => $testimonial->avatar_url,
                'invite_url' => route('testimonial.show', $testimonial->token),
                'submitted_at' => $testimonial->submitted_at?->toIso8601String(),
            ] : null,
            'projects' => Project::ordered()->get(['id', 'title']),
            'statuses' => array_column(TestimonialStatus::cases(), 'value'),
        ]);
    }

    private function fill(Testimonial $testimonial, TestimonialRequest $request): void
    {
        $data = $request->safe()->except(['avatar', 'remove_avatar']);

        if (isset($data['status'])) {
            $status = TestimonialStatus::from($data['status']);
            if ($status === TestimonialStatus::Approved && $testimonial->status !== TestimonialStatus::Approved) {
                $data['approved_at'] = now();
            }
        } else {
            unset($data['status']);
        }

        $testimonial->fill($data);

        if ($request->boolean('remove_avatar') || $request->hasFile('avatar')) {
            Media::delete($testimonial->getOriginal('avatar'));
            $testimonial->avatar = null;
        }

        if ($request->hasFile('avatar')) {
            $testimonial->avatar = $request->file('avatar')->store('avatars', 'public');
        }
    }
}
