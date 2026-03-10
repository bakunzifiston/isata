@extends('layouts.super-admin')

@section('title', 'Organizations')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Organizations</h1>
        <p class="mt-1 text-slate-600">Manage all organizations on the platform</p>
    </div>
</div>

<form method="GET" class="mb-6 flex gap-4">
    <select name="status" onchange="this.form.submit()" class="px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500">
        <option value="">All</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active only</option>
        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive only</option>
    </select>
</form>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Events</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Users</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($organizations as $org)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-900">{{ $org->name }}</p>
                        <p class="text-xs text-slate-500">{{ $org->slug }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $org->email ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <form method="POST" action="{{ route('super-admin.organizations.assign-plan', $org) }}" class="inline">
                            @csrf
                            <select name="subscription_plan_id" onchange="this.form.submit()" class="text-sm rounded-lg border-slate-300 py-1">
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ $org->subscription_plan_id == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $org->events_count }}</td>
                    <td class="px-6 py-4 text-sm text-slate-600">{{ $org->users_count }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $org->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                            {{ $org->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <form method="POST" action="{{ route('super-admin.organizations.toggle', $org) }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm font-medium {{ $org->is_active ? 'text-red-600 hover:text-red-800' : 'text-emerald-600 hover:text-emerald-800' }}">
                                {{ $org->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-slate-500">No organizations</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($organizations->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">{{ $organizations->links() }}</div>
    @endif
</div>
@endsection
