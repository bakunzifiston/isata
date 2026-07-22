@extends('layouts.super-admin')

@section('title', 'Organizations')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">Organizations</h1>
        <p class="admin-page-subtitle">Manage all organizations on the platform</p>
    </div>
</div>

@if($hasIsActive ?? false)
<form method="GET" class="mb-6 flex gap-4">
    <select name="status" onchange="this.form.submit()" class="admin-input">
        <option value="">All</option>
        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active only</option>
        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive only</option>
    </select>
</form>
@endif

<div class="admin-table-wrap">
    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Events</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Users</th>
                    @if($hasIsActive ?? false)
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink/50">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($organizations as $org)
                <tr>
                    <td class="px-6 py-4">
                        <p class="font-medium text-ink">{{ $org->name }}</p>
                        <p class="text-xs text-ink/50">{{ $org->slug }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $org->email ?? '—' }}</td>
                    <td class="px-6 py-4">
                        <form method="POST" action="{{ route('super-admin.organizations.assign-plan', $org) }}" class="inline">
                            @csrf
                            <select name="subscription_plan_id" onchange="this.form.submit()" class="rounded-lg border border-dawn-2 py-1 text-sm">
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ $org->subscription_plan_id == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $org->events_count ?? 0 }}</td>
                    <td class="px-6 py-4 text-sm text-ink/65">{{ $org->users_count ?? 0 }}</td>
                    @if($hasIsActive ?? false)
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
                    @endif
                </tr>
                @empty
                <tr><td colspan="{{ ($hasIsActive ?? false) ? 7 : 5 }}" class="px-6 py-12 text-center text-ink/50">No organizations</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($organizations->hasPages())
    <div class="px-6 py-4 border-t border-dawn-2/80">{{ $organizations->links() }}</div>
    @endif
</div>
@endsection
