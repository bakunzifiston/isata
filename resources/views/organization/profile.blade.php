@extends('layouts.dashboard')

@section('title', 'Organization Profile - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Organization Profile</h1>
    <p class="mt-1 text-slate-600">Manage your organization details</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden max-w-2xl">
    @if(auth()->user()->isOrganizationAdmin())
    <form method="POST" action="{{ route('organization.profile.update') }}" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Logo --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Logo</label>
            <div class="flex items-center gap-4">
                @if($organization->logo_url)
                    <img src="{{ $organization->logo_url }}" alt="Logo" class="w-16 h-16 rounded-lg object-cover border border-slate-200">
                @else
                    <div class="w-16 h-16 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <input type="file" name="logo" accept="image/*" class="block text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1 text-xs text-slate-500">PNG, JPG up to 2MB</p>
                </div>
            </div>
            @error('logo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Organization name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $organization->name) }}" required
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $organization->email) }}"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $organization->phone) }}"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="address" class="block text-sm font-medium text-slate-700 mb-1">Address</label>
            <textarea name="address" id="address" rows="3"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('address', $organization->address) }}</textarea>
            @error('address')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if($organization->subscriptionPlan)
        <div class="pt-4 border-t border-slate-200">
            <p class="text-sm text-slate-500">Subscription plan: <span class="font-medium text-slate-700">{{ $organization->subscriptionPlan->name }}</span></p>
        </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
                Save changes
            </button>
            <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
    @else
    <div class="p-6 space-y-6">
        <div>
            <p class="text-sm font-medium text-slate-500">Logo</p>
            @if($organization->logo_url)
                <img src="{{ $organization->logo_url }}" alt="Logo" class="mt-2 w-16 h-16 rounded-lg object-cover border border-slate-200">
            @else
                <p class="mt-2 text-sm text-slate-400">No logo</p>
            @endif
        </div>
        <div><p class="text-sm font-medium text-slate-500">Name</p><p class="mt-1 text-slate-900">{{ $organization->name }}</p></div>
        <div><p class="text-sm font-medium text-slate-500">Email</p><p class="mt-1 text-slate-900">{{ $organization->email ?? '—' }}</p></div>
        <div><p class="text-sm font-medium text-slate-500">Phone</p><p class="mt-1 text-slate-900">{{ $organization->phone ?? '—' }}</p></div>
        <div><p class="text-sm font-medium text-slate-500">Address</p><p class="mt-1 text-slate-900">{{ $organization->address ?? '—' }}</p></div>
        @if($organization->subscriptionPlan)
        <div><p class="text-sm font-medium text-slate-500">Subscription</p><p class="mt-1 text-slate-900">{{ $organization->subscriptionPlan->name }}</p></div>
        @endif
        <a href="{{ route('dashboard') }}" class="inline-block px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Back</a>
    </div>
    @endif
</div>
@endsection
