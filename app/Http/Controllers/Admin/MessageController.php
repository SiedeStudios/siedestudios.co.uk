<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Mail\MessageReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Message::query();

        // Handle search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        // Handle status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Get messages with pagination
        $messages = $query->latest()->paginate(15);

        // Get counts for filters
        $counts = [
            'all' => Message::count(),
            'new' => Message::new()->count(),
            'read' => Message::read()->count(),
            'replied' => Message::replied()->count(),
        ];

        return view('admin.messages.index', compact('messages', 'counts'));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\View\View
     */
    public function show(Message $message)
    {
        // Mark as read if it's new
        if ($message->isNew()) {
            $message->markAsRead();
        }

        return view('admin.messages.show', compact('message'));
    }

    /**
     * Reply to a message.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reply(Request $request, Message $message)
    {
        $validated = $request->validate([
            'reply' => 'required|string|min:10',
        ]);

        try {
            // Update message status and save reply
            $message->markAsReplied($validated['reply']);

            // Send email reply
            Mail::to($message->email)->send(new MessageReply($message));

            return redirect()->route('admin.messages.show', $message)
                ->with('success', 'Your reply has been sent successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to send reply: ' . $e->getMessage());
            
            return redirect()->route('admin.messages.show', $message)
                ->with('error', 'Failed to send reply. Please try again later.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')
            ->with('success', 'Message deleted successfully.');
    }

    /**
     * Mark a message as read.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead(Message $message)
    {
        $message->markAsRead();

        return redirect()->back()->with('success', 'Message marked as read.');
    }

    /**
     * Mark multiple messages as read.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkMarkAsRead(Request $request)
    {
        $validated = $request->validate([
            'messages' => 'required|array',
            'messages.*' => 'exists:messages,id',
        ]);

        Message::whereIn('id', $validated['messages'])
            ->where('status', 'new')
            ->update(['status' => 'read']);

        return redirect()->back()->with('success', 'Selected messages marked as read.');
    }

    /**
     * Delete multiple messages.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'messages' => 'required|array',
            'messages.*' => 'exists:messages,id',
        ]);

        Message::whereIn('id', $validated['messages'])->delete();

        return redirect()->back()->with('success', 'Selected messages deleted.');
    }
}