<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Message;
use App\Models\Service;
use App\Services\DiscordNotifier;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function show(Request $request): Response
    {
        $services = Service::active()->ordered()->get(['id', 'title', 'slug']);
        $preselect = $services->firstWhere('slug', $request->string('service')->toString());

        return Inertia::render('Contact', [
            'services' => $services,
            'budgets' => ContactRequest::BUDGETS,
            'preselected' => $preselect?->id,
        ]);
    }

    public function store(ContactRequest $request, DiscordNotifier $discord, Settings $settings): RedirectResponse
    {
        $data = $request->safe()->except('website');
        $service = isset($data['service_id']) ? Service::find($data['service_id']) : null;

        $message = Message::create([
            ...$data,
            'service_name' => $service?->title ?? 'General enquiry',
            'ip_address' => $request->ip(),
        ]);

        if ($settings->bool('notify_messages')) {
            $discord->newMessage($message);
        }

        return back()->with('success', 'Message received — I\'ll be in touch soon.');
    }
}
