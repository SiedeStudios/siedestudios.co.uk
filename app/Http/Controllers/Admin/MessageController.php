<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = $request->string('filter')->toString() === 'unread' ? 'unread' : null;

        return Inertia::render('Admin/Messages/Index', [
            'messages' => Message::query()
                ->when($filter, fn ($q) => $q->unread())
                ->latest('id')
                ->paginate(20)
                ->withQueryString(),
            'filter' => $filter,
        ]);
    }

    public function show(Message $message): Response
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return Inertia::render('Admin/Messages/Show', ['message' => $message]);
    }

    public function toggleRead(Message $message): RedirectResponse
    {
        $message->update(['read_at' => $message->read_at ? null : now()]);

        return back();
    }

    public function destroy(Message $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted.');
    }
}
