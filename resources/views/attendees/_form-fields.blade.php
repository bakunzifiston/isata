@php
    $attendee = $attendee ?? new \App\Models\Attendee();
@endphp
<div>
    <label for="name" class="admin-label mb-1">Name *</label>
    <input type="text" name="name" id="name" value="{{ old('name', $attendee->name) }}" required class="admin-input">
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="email" class="admin-label mb-1">Email *</label>
    <input type="email" name="email" id="email" value="{{ old('email', $attendee->email) }}" required class="admin-input">
    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="phone" class="admin-label mb-1">Phone</label>
    <input type="text" name="phone" id="phone" value="{{ old('phone', $attendee->phone) }}" class="admin-input">
</div>
<div>
    <label for="organization" class="admin-label mb-1">Organization</label>
    <input type="text" name="organization" id="organization" value="{{ old('organization', $attendee->organization) }}" class="admin-input">
</div>
<div>
    <label class="admin-label mb-2">RSVP status</label>
    <div class="flex flex-wrap gap-3">
        @foreach(\App\Models\Attendee::rsvpStatuses() as $value => $label)
        <label class="flex items-center">
            <input type="radio" name="rsvp_status" value="{{ $value }}" {{ old('rsvp_status', $attendee->rsvp_status ?? 'pending') === $value ? 'checked' : '' }}
                class="rounded border-dawn-2 text-coral focus:ring-coral/20">
            <span class="ml-2 text-sm text-ink/75">{{ $label }}</span>
        </label>
        @endforeach
    </div>
</div>
