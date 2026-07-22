@extends('layouts.dashboard')

@section('title', 'Social Accounts - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">Connected accounts</h1>
        <p class="admin-page-subtitle">Manage your social media accounts for posting</p>
    </div>
</div>

<div class="max-w-lg mb-6">
    <form method="POST" action="{{ route('social.accounts.store') }}" class="flex gap-2">
        @csrf
        <select name="platform" required class="admin-input">
            @foreach(\App\Models\SocialAccount::platforms() as $slug => $name)
            <option value="{{ $slug }}">{{ $name }}</option>
            @endforeach
        </select>
        <input type="text" name="name" placeholder="Account name (optional)" class="admin-input flex-1">
        <button type="submit" class="admin-btn-primary">Add</button>
    </form>
    <p class="mt-2 text-xs text-ink/50">Add placeholder accounts. Configure FACEBOOK_APP_ID, LINKEDIN_CLIENT_ID, etc. in .env for live API posting.</p>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Platform</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink/50">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accounts as $acc)
            <tr>
                <td class="px-6 py-4 font-medium text-ink">{{ ucfirst($acc->platform) }}</td>
                <td class="px-6 py-4 text-ink/65">{{ $acc->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $acc->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-dawn-2 text-ink/70' }}">
                        {{ $acc->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <form method="POST" action="{{ route('social.accounts.destroy', $acc) }}" class="inline" onsubmit="return confirm('Remove this account?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-ink/50">No accounts yet. Add one above or use default .env API keys.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<a href="{{ route('social.index') }}" class="inline-block mt-6 text-sm text-ink/65 hover:text-ink">← Back to posts</a>
@endsection
