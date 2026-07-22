@extends('layouts.super-admin')

@section('title', 'Users')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">Users</h1>
        <p class="admin-page-subtitle">Manage all users across all organizations</p>
    </div>
</div>

<div class="admin-table-wrap">
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink/50">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="px-6 py-4">
                        <p class="font-medium text-ink">{{ $user->name }}</p>
                        <p class="text-xs text-ink/50">#{{ $user->id }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $user->role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-dawn-2 text-ink/75' }}">
                            {{ ucfirst($user->role) }}
                            @if($user->organization_id === null)
                                <span class="ml-1 text-[10px] uppercase tracking-wide text-amber-700">(System)</span>
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-ink/65">
                        @if($user->organization)
                            <div>
                                <p class="font-medium text-ink text-xs sm:text-sm">{{ $user->organization->name }}</p>
                                <p class="text-xs text-ink/50">{{ $user->organization->slug }}</p>
                            </div>
                        @else
                            <span class="text-xs text-ink/50">System</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-ink/65">
                        {{ optional($user->created_at)->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        @if($user->id === auth()->id())
                            <span class="text-xs text-ink/45 italic">This is you</span>
                        @else
                            <form method="POST" action="{{ route('super-admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this user? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-ink/50">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-dawn-2/80">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection

