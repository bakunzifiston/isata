@extends('layouts.dashboard')

@section('title', 'Email sender addresses · ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">Email sender addresses</h1>
        <p class="admin-page-subtitle">Save From names and emails to use when creating email messages.</p>
    </div>
    <a href="{{ route('email-senders.create') }}" class="admin-btn-primary shrink-0">
        Add sender
    </a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Label</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">From name</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">From email</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink/50">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($senderIdentities as $identity)
            <tr>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $identity->label ?: '—' }}</td>
                <td class="px-6 py-4 font-medium text-ink">{{ $identity->from_name }}</td>
                <td class="px-6 py-4 text-sm text-ink/65">{{ $identity->from_email }}</td>
                <td class="px-6 py-4 text-right text-sm space-x-2">
                    <a href="{{ route('email-senders.edit', $identity) }}" class="admin-link">Edit</a>
                    <form method="POST" action="{{ route('email-senders.destroy', $identity) }}" class="inline" onsubmit="return confirm('Remove this sender address?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-ink/50">
                    No saved senders yet. Add at least one to send email campaigns with a recognizable From address.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($senderIdentities->hasPages())
    <div class="px-6 py-4 border-t border-dawn-2/80">{{ $senderIdentities->links() }}</div>
    @endif
</div>

<a href="{{ route('messages.hub') }}" class="inline-block mt-6 text-sm text-ink/65 hover:text-ink">← Messages</a>
@endsection
