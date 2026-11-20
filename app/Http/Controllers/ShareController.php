<?php

namespace App\Http\Controllers;

use App\Models\Share;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class ShareController extends Controller
{
    public function show(Request $request, Share $share): InertiaResponse
    {
        abort_if($share->isExpired(), 410);

        if ($share->isProtected() && ! $this->isUnlocked($request, $share)) {
            return Inertia::render('Share/Locked', ['slug' => $share->slug, 'title' => $share->title]);
        }

        $payload = [
            'slug' => $share->slug,
            'title' => $share->title,
            'content' => $share->content,
            'language' => $share->language,
            'views' => $share->views + 1,
            'created_at' => $share->created_at->toIso8601String(),
            'expires_at' => $share->expires_at?->toIso8601String(),
            'burned' => $share->burn_after_read,
            'lines' => substr_count($share->content, "\n") + 1,
            'size' => strlen($share->content),
        ];

        $this->consume($share);

        return Inertia::render('Share/Show', ['share' => $payload]);
    }

    public function raw(Request $request, Share $share): Response
    {
        abort_if($share->isExpired(), 410);
        abort_if($share->isProtected() && ! $this->isUnlocked($request, $share), 403);

        $content = $share->content;
        $this->consume($share);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    public function unlock(Request $request, Share $share): RedirectResponse
    {
        $request->validate(['password' => ['required', 'string']]);

        if (! Hash::check($request->input('password'), $share->password)) {
            return back()->withErrors(['password' => 'That password is incorrect.']);
        }

        $request->session()->push('shares.unlocked', $share->id);

        return redirect()->route('share.show', $share);
    }

    private function isUnlocked(Request $request, Share $share): bool
    {
        return $request->user()?->is_admin || in_array($share->id, $request->session()->get('shares.unlocked', []), true);
    }

    private function consume(Share $share): void
    {
        if ($share->burn_after_read) {
            $share->delete();

            return;
        }

        $share->increment('views');
    }
}
