@extends('layouts.super-admin')

@section('title', 'Users')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Users</h1>
        <p class="mt-1 text-slate-600">Manage all users across all organizations</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($users as $user)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-900">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">#{{ $user->id }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $user->role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                            {{ ucfirst($user->role) }}
                            @if($user->organization_id === null)
                                <span class="ml-1 text-[10px] uppercase tracking-wide text-amber-700">(System)</span>
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        @if($user->organization)
                            <div>
                                <p class="font-medium text-slate-900 text-xs sm:text-sm">{{ $user->organization->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->organization->slug }}</p>
                            </div>
                        @else
                            <span class="text-xs text-slate-500">System</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">
                        {{ optional($user->created_at)->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        @if($user->id === auth()->id())
                            <span class="text-xs text-slate-400 italic">This is you</span>
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
                    <td colspan="6" class="px-6 py-12 text-center text-slate-500">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection

