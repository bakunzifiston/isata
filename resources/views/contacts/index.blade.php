@extends('layouts.dashboard')

@section('title', 'Contacts - ' . config('app.name'))

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="admin-page-title">Contacts</h1>
        <p class="admin-page-subtitle">Organization-wide contact directory — assign contacts to events from here or per event.</p>
    </div>
    <a href="{{ route('contacts.create') }}" class="admin-btn-primary">
        Add contact
    </a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Company</th>
                <th>Events</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contacts as $contact)
            <tr>
                <td class="whitespace-nowrap font-medium text-ink">{{ $contact->name }}</td>
                <td class="whitespace-nowrap">{{ $contact->email }}</td>
                <td class="whitespace-nowrap">{{ $contact->phone ?? '—' }}</td>
                <td class="whitespace-nowrap">{{ $contact->company ?? '—' }}</td>
                <td class="whitespace-nowrap">{{ $contact->attendees_count }}</td>
                <td class="whitespace-nowrap text-right">
                    <a href="{{ route('contacts.edit', $contact) }}" class="admin-link">Edit</a>
                    <form method="POST" action="{{ route('contacts.destroy', $contact) }}" class="inline ml-4" onsubmit="return confirm('Remove this contact?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Remove</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-12 text-center text-ink/50">No contacts yet. Add one or import guests on an event.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $contacts->links() }}</div>
@endsection
