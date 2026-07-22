<div class="max-w-xl admin-card p-6">
    <form method="POST" action="{{ $route }}" class="space-y-5">
        @csrf
        @method($method)

        <div>
            <label for="label" class="admin-label mb-1">Label (optional)</label>
            <input type="text" name="label" id="label" value="{{ old('label', $identity->label) }}"
                class="admin-input"
                placeholder="e.g. Support, RSVP team">
            @error('label')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="from_name" class="admin-label mb-1">Display name *</label>
            <input type="text" name="from_name" id="from_name" value="{{ old('from_name', $identity->from_name) }}" required
                class="admin-input">
            @error('from_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="from_email" class="admin-label mb-1">From email *</label>
            <input type="email" name="from_email" id="from_email" value="{{ old('from_email', $identity->from_email) }}" required
                class="admin-input">
            <p class="mt-1 text-xs text-ink/50">Use an address your organization is allowed to send from (often verified with your mail provider).</p>
            @error('from_email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="admin-btn-primary">Save</button>
            <a href="{{ route('email-senders.index') }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
