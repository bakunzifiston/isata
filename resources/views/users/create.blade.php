@extends('layouts.dashboard')

@section('title', 'Add User - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Add user</h1>
    <p class="admin-page-subtitle">Invite a new member to your organization</p>
</div>

<div class="admin-card p-6 max-w-lg">
    <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="admin-label mb-1">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                class="admin-input">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="admin-label mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                class="admin-input">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="admin-label mb-1">Phone</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                class="admin-input">
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="role" class="admin-label mb-1">Role</label>
            <select name="role" id="role" required
                class="admin-input">
                <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            <p class="mt-1 text-xs text-ink/50">Admin can manage organization profile and users. Staff has limited access.</p>
            @error('role')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="admin-label mb-1">Password</label>
            <input type="password" name="password" id="password" required
                class="admin-input">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="admin-label mb-1">Confirm password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                class="admin-input">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="admin-btn-primary">Create user</button>
            <a href="{{ route('users.index') }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
