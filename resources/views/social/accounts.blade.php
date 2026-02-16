@extends('layouts.dashboard')

@section('title', 'Social Accounts - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Connected accounts</h1>
        <p class="mt-1 text-slate-600">Manage your social media accounts for posting</p>
    </div>
</div>

<div class="max-w-lg mb-6">
    <form method="POST" action="{{ route('social.accounts.store') }}" class="flex gap-2">
        @csrf
        <select name="platform" required class="px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
            @foreach(\App\Models\SocialAccount::platforms() as $slug => $name)
            <option value="{{ $slug }}">{{ $name }}</option>
            @endforeach
        </select>
        <input type="text" name="name" placeholder="Account name (optional)" class="px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 flex-1">
        <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">Add</button>
    </form>
    <p class="mt-2 text-xs text-slate-500">Add placeholder accounts. Configure FACEBOOK_APP_ID, LINKEDIN_CLIENT_ID, etc. in .env for live API posting.</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Platform</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($accounts as $acc)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ ucfirst($acc->platform) }}</td>
                <td class="px-6 py-4 text-slate-600">{{ $acc->name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full {{ $acc->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-800' }}">
                        {{ $acc->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    <form method="POST" action="{{ route('social.accounts.destroy', $acc) }}" class="inline" onsubmit="return confirm('Remove this account?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Remove</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-slate-500">No accounts yet. Add one above or use default .env API keys.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<a href="{{ route('social.index') }}" class="inline-block mt-6 text-sm text-slate-600 hover:text-slate-900">← Back to posts</a>
@endsection
