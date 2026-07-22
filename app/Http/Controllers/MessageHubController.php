<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MessageHubController extends Controller
{
    /** All messages across the organization’s events (sidebar entry point). */
    public function index(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403, 'No organization associated with your account.');
        }

        if (Schema::hasTable('messages')) {
            $messages = Message::query()
                ->with(['channel', 'event', 'sentBy', 'senderIdentity'])
                ->whereHas('event', fn ($q) => $q->where('organization_id', $organization->id))
                ->latest()
                ->paginate(20);
        } else {
            $messages = new LengthAwarePaginator(
                collect(),
                0,
                20,
                1,
                ['path' => request()->url(), 'query' => request()->query()]
            );
        }

        $quickCreateEvent = $organization->events()->latest('updated_at')->first();

        return view('messages.hub', [
            'messages' => $messages,
            'quickCreateEvent' => $quickCreateEvent,
        ]);
    }
}
