<form method="POST" action="{{ $route }}" class="space-y-5">
    @csrf
    @method($method)

    <div>
        <label for="name" class="admin-label mb-1">Name *</label>
        <input type="text" name="name" id="name" value="{{ old('name', $contact?->name) }}" required class="admin-input">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="admin-label mb-1">Email *</label>
        <input type="email" name="email" id="email" value="{{ old('email', $contact?->email) }}" required class="admin-input">
        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="phone" class="admin-label mb-1">Phone</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $contact?->phone) }}" class="admin-input">
        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="company" class="admin-label mb-1">Company</label>
        <input type="text" name="company" id="company" value="{{ old('company', $contact?->company) }}" class="admin-input">
        @error('company')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="flex gap-3">
        <button type="submit" class="admin-btn-primary">Save</button>
        <a href="{{ route('contacts.index') }}" class="admin-btn-secondary">Cancel</a>
    </div>
</form>
