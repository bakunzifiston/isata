@extends('layouts.dashboard')

@section('title', 'Organization Profile - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Organization Profile</h1>
    <p class="admin-page-subtitle">Manage your organization details</p>
</div>

<div class="admin-table-wrap max-w-2xl">
    @if(auth()->user()->isOrganizationAdmin())
    <form method="POST" action="{{ route('organization.profile.update') }}" enctype="multipart/form-data" class="p-6 space-y-6">
        @csrf
        @method('PUT')

        {{-- Logo --}}
        <div>
            <label class="admin-label mb-2">Logo</label>
            <div class="flex items-center gap-4">
                @if($organization->logo_url)
                    <img src="{{ $organization->logo_url }}" alt="Logo" class="w-16 h-16 rounded-lg object-cover border border-dawn-2/80">
                @else
                    <div class="w-16 h-16 rounded-lg bg-dawn-2 flex items-center justify-center text-ink/45">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <input type="file" name="logo" accept="image/*" class="block text-sm text-ink/50 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-coral/10 file:text-coral hover:file:bg-coral/15">
                    <p class="mt-1 text-xs text-ink/50">PNG, JPG up to 2MB</p>
                </div>
            </div>
            @error('logo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="admin-label mb-1">Organization name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $organization->name) }}" required
                class="admin-input">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="admin-label mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $organization->email) }}"
                class="admin-input">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="admin-label mb-1">Phone</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $organization->phone) }}"
                class="admin-input">
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="address" class="admin-label mb-1">Address</label>
            <textarea name="address" id="address" rows="3"
                class="admin-input">{{ old('address', $organization->address) }}</textarea>
            @error('address')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if($organization->subscriptionPlan)
        <div class="pt-4 border-t border-dawn-2/80">
            <p class="text-sm text-ink/50">Subscription plan: <span class="font-medium text-ink/75">{{ $organization->subscriptionPlan->name }}</span></p>
        </div>
        @endif

        <div class="flex gap-3">
            <button type="submit" class="admin-btn-primary">
                Save changes
            </button>
            <a href="{{ route('dashboard') }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
    @else
    <div class="p-6 space-y-6">
        <div>
            <p class="text-sm font-medium text-ink/50">Logo</p>
            @if($organization->logo_url)
                <img src="{{ $organization->logo_url }}" alt="Logo" class="mt-2 w-16 h-16 rounded-lg object-cover border border-dawn-2/80">
            @else
                <p class="mt-2 text-sm text-ink/45">No logo</p>
            @endif
        </div>
        <div><p class="text-sm font-medium text-ink/50">Name</p><p class="mt-1 text-ink">{{ $organization->name }}</p></div>
        <div><p class="text-sm font-medium text-ink/50">Email</p><p class="mt-1 text-ink">{{ $organization->email ?? '—' }}</p></div>
        <div><p class="text-sm font-medium text-ink/50">Phone</p><p class="mt-1 text-ink">{{ $organization->phone ?? '—' }}</p></div>
        <div><p class="text-sm font-medium text-ink/50">Address</p><p class="mt-1 text-ink">{{ $organization->address ?? '—' }}</p></div>
        @if($organization->subscriptionPlan)
        <div><p class="text-sm font-medium text-ink/50">Subscription</p><p class="mt-1 text-ink">{{ $organization->subscriptionPlan->name }}</p></div>
        @endif
        <a href="{{ route('dashboard') }}" class="inline-block admin-btn-secondary">Back</a>
    </div>
    @endif
</div>
@endsection
