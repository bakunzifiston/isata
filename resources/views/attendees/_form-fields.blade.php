@php
    $attendee = $attendee ?? new \App\Models\Attendee();
@endphp
<div>
    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
    <input type="text" name="name" id="name" value="{{ old('name', $attendee->name) }}" required
        class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
    <input type="email" name="email" id="email" value="{{ old('email', $attendee->email) }}" required
        class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
    <input type="text" name="phone" id="phone" value="{{ old('phone', $attendee->phone) }}"
        class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
</div>
<div>
    <label for="organization" class="block text-sm font-medium text-slate-700 mb-1">Organization</label>
    <input type="text" name="organization" id="organization" value="{{ old('organization', $attendee->organization) }}"
        class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
</div>
<div>
    <label class="block text-sm font-medium text-slate-700 mb-2">RSVP status</label>
    <div class="flex flex-wrap gap-3">
        @foreach(\App\Models\Attendee::rsvpStatuses() as $value => $label)
        <label class="flex items-center">
            <input type="radio" name="rsvp_status" value="{{ $value }}" {{ old('rsvp_status', $attendee->rsvp_status ?? 'pending') === $value ? 'checked' : '' }}
                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
            <span class="ml-2 text-sm text-slate-700">{{ $label }}</span>
        </label>
        @endforeach
    </div>
</div>
