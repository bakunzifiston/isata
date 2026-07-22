<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmailSenderIdentityRequest;
use App\Http\Requests\UpdateEmailSenderIdentityRequest;
use App\Models\EmailSenderIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailSenderIdentityController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', EmailSenderIdentity::class);

        $organization = auth()->user()->organization;

        return view('email-senders.index', [
            'senderIdentities' => $organization->emailSenderIdentities()->orderBy('label')->orderBy('from_name')->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', EmailSenderIdentity::class);

        return view('email-senders.create', [
            'identity' => new EmailSenderIdentity,
        ]);
    }

    public function store(StoreEmailSenderIdentityRequest $request): RedirectResponse
    {
        $organization = auth()->user()->organization;

        $organization->emailSenderIdentities()->create($request->validated());

        return redirect()->route('email-senders.index')
            ->with('status', 'Sender address saved.');
    }

    public function edit(EmailSenderIdentity $identity): View
    {
        $this->authorize('update', $identity);

        return view('email-senders.edit', [
            'identity' => $identity,
        ]);
    }

    public function update(UpdateEmailSenderIdentityRequest $request, EmailSenderIdentity $identity): RedirectResponse
    {
        $identity->update($request->validated());

        return redirect()->route('email-senders.index')
            ->with('status', 'Sender address updated.');
    }

    public function destroy(EmailSenderIdentity $identity): RedirectResponse
    {
        $this->authorize('delete', $identity);

        $identity->delete();

        return redirect()->route('email-senders.index')
            ->with('status', 'Sender address removed.');
    }
}
