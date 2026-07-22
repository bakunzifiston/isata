@extends('layouts.dashboard')

@section('title', 'Users - ' . config('app.name'))

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="admin-page-title">Users</h1>
        <p class="admin-page-subtitle">Manage organization members and roles</p>
    </div>
    @if(auth()->user()->isOrganizationAdmin())
    <a href="{{ route('users.create') }}" class="admin-btn-primary">
        Add user
    </a>
    @endif
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Phone</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Role</th>
                @if(auth()->user()->isOrganizationAdmin())
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink/50">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-ink">{{ $user->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink/65">{{ $user->email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-ink/65">{{ $user->phone ?? '—' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $user->role === 'admin' ? 'bg-coral/15 text-coral' : 'bg-dawn-2 text-ink/70' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                @if(auth()->user()->isOrganizationAdmin())
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <a href="{{ route('users.edit', $user) }}" class="admin-link">Edit</a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline ml-4" onsubmit="return confirm('Remove this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Remove</button>
                    </form>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ auth()->user()->isOrganizationAdmin() ? 5 : 4 }}" class="px-6 py-12 text-center text-ink/50">No users yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
