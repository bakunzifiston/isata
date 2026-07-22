<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function __construct(
        protected ContactService $contactService
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Contact::class);

        $contacts = auth()->user()->organization
            ->contacts()
            ->withCount('attendees')
            ->orderBy('name')
            ->paginate(20);

        return view('contacts.index', compact('contacts'));
    }

    public function create(): View
    {
        $this->authorize('create', Contact::class);

        return view('contacts.create');
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $this->contactService->createForOrganization(
            auth()->user()->organization,
            $request->validated()
        );

        return redirect()->route('contacts.index')
            ->with('status', 'Contact added successfully.');
    }

    public function edit(Contact $contact): View
    {
        $this->authorize('update', $contact);

        return view('contacts.edit', compact('contact'));
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $this->contactService->updateContact($contact, $request->validated());

        return redirect()->route('contacts.index')
            ->with('status', 'Contact updated successfully.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        if ($contact->attendees()->exists()) {
            return redirect()->route('contacts.index')
                ->with('error', 'Remove this contact from all events before deleting.');
        }

        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('status', 'Contact removed.');
    }
}
