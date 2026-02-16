@extends('layouts.dashboard')

@section('title', 'Add User - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Add user</h1>
    <p class="mt-1 text-slate-600">Invite a new member to your organization</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 max-w-lg">
    <form method="POST" action="{{ route('users.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-slate-700 mb-1">Role</label>
            <select name="role" id="role" required
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">Admin can manage organization profile and users. Staff has limited access.</p>
            @error('role')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input type="password" name="password" id="password" required
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">Create user</button>
            <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
