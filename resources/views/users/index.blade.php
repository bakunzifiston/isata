@extends('layouts.dashboard')

@section('title', 'Users - ' . config('app.name'))

@section('content')
<div class="flex justify-between items-center mb-8">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Users</h1>
        <p class="mt-1 text-slate-600">Manage organization members and roles</p>
    </div>
    @if(auth()->user()->isOrganizationAdmin())
    <a href="{{ route('users.create') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
        Add user
    </a>
    @endif
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Phone</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Role</th>
                @if(auth()->user()->isOrganizationAdmin())
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($users as $user)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ $user->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $user->email }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $user->phone ?? '—' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $user->role === 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-800' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                @if(auth()->user()->isOrganizationAdmin())
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline ml-4" onsubmit="return confirm('Remove this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                    </form>
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ auth()->user()->isOrganizationAdmin() ? 5 : 4 }}" class="px-6 py-12 text-center text-slate-500">No users yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
