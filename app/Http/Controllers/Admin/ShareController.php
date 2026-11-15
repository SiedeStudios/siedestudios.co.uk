<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShareRequest;
use App\Models\Share;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ShareController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Shares/Index', [
            'shares' => Share::latest('id')->paginate(25)->through(fn (Share $s) => [
                'id' => $s->id,
                'slug' => $s->slug,
                'title' => $s->title,
                'language' => $s->language,
                'views' => $s->views,
                'protected' => $s->isProtected(),
                'burn_after_read' => $s->burn_after_read,
                'expired' => $s->isExpired(),
                'expires_at' => $s->expires_at?->toIso8601String(),
                'created_at' => $s->created_at->toIso8601String(),
                'url' => route('share.show', $s),
                'size' => strlen($s->content),
            ]),
        ]);
    }

    public function create(): Response
    {
        return $this->form(null);
    }

    public function store(ShareRequest $request): RedirectResponse
    {
        $share = new Share($request->safe()->only(['title', 'content', 'language', 'burn_after_read']));
        $share->user_id = $request->user()->id;
        $share->password = $request->validated('password') ?: null;
        $share->expires_at = $this->expiry($request->validated('expiry'));
        $share->save();

        return redirect()->route('admin.shares.edit', $share)->with('success', 'Share created — link copied below.');
    }

    public function edit(Share $share): Response
    {
        return $this->form($share);
    }

    public function update(ShareRequest $request, Share $share): RedirectResponse
    {
        $share->fill($request->safe()->only(['title', 'content', 'language', 'burn_after_read']));

        if ($request->boolean('remove_password')) {
            $share->password = null;
        } elseif (filled($request->validated('password'))) {
            $share->password = $request->validated('password');
        }

        if ($request->validated('expiry') !== null) {
            $share->expires_at = $this->expiry($request->validated('expiry'));
        }

        $share->save();

        return back()->with('success', 'Share updated.');
    }

    public function destroy(Share $share): RedirectResponse
    {
        $share->delete();

        return redirect()->route('admin.shares.index')->with('success', 'Share deleted.');
    }

    private function form(?Share $share): Response
    {
        return Inertia::render('Admin/Shares/Form', [
            'share' => $share ? [
                ...$share->only(['id', 'slug', 'title', 'content', 'language', 'burn_after_read', 'views']),
                'protected' => $share->isProtected(),
                'expires_at' => $share->expires_at?->toIso8601String(),
                'url' => route('share.show', $share),
                'raw_url' => route('share.raw', $share),
            ] : null,
            'languages' => Share::LANGUAGES,
            'expiries' => ShareRequest::EXPIRY,
        ]);
    }

    private function expiry(?string $key): ?Carbon
    {
        $interval = ShareRequest::EXPIRY[$key ?? 'never'] ?? null;

        return $interval ? now()->add($interval) : null;
    }
}
