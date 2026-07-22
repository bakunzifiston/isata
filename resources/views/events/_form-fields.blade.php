@php
    $__eventFormat = old(
        'event_format',
        $event->event_format
            ?: (filled($event->meeting_link) && blank($event->venue)
                ? \App\Models\Event::FORMAT_ONLINE
                : \App\Models\Event::FORMAT_PHYSICAL)
    );
@endphp

<div class="space-y-5">
    <div>
        <label for="name" class="admin-label mb-1">Event name *</label>
        <input type="text" name="name" id="name" value="{{ old('name', $event->name) }}" required
            class="admin-input">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="admin-label mb-1">Description</label>
        <textarea name="description" id="description" rows="4"
            class="admin-input">{{ old('description', $event->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label for="date" class="admin-label mb-1">Date *</label>
            <input type="date" name="date" id="date" value="{{ old('date', $event->date?->format('Y-m-d')) }}" required
                class="admin-input">
            @error('date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="time" class="admin-label mb-1">Time</label>
            <input type="time" name="time" id="time" value="{{ old('time', $event->time_formatted) }}"
                class="admin-input">
            @error('time')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <span class="admin-label mb-2">Event format *</span>
        <div class="flex flex-wrap gap-4">
            <label class="flex items-center cursor-pointer">
                <input type="radio" name="event_format" value="physical" id="event_format_physical"
                    {{ $__eventFormat === 'physical' ? 'checked' : '' }}
                    class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                <span class="ml-2 text-sm text-ink/75">In person</span>
            </label>
            <label class="flex items-center cursor-pointer">
                <input type="radio" name="event_format" value="online" id="event_format_online"
                    {{ $__eventFormat === 'online' ? 'checked' : '' }}
                    class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                <span class="ml-2 text-sm text-ink/75">Online</span>
            </label>
        </div>
        @error('event_format')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div id="event-venue-field" class="{{ $__eventFormat === 'online' ? 'hidden' : '' }}">
        <label for="venue" class="admin-label mb-1">Location</label>
        <input type="text" name="venue" id="venue" value="{{ old('venue', $event->venue) }}"
            class="admin-input"
            placeholder="Venue name or full address">
        @error('venue')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div id="event-meeting-link-field" class="{{ $__eventFormat === 'physical' ? 'hidden' : '' }}">
        <label for="meeting_link" class="admin-label mb-1">Meeting link</label>
        <input type="url" name="meeting_link" id="meeting_link" value="{{ old('meeting_link', $event->meeting_link) }}"
            class="admin-input"
            placeholder="https://…">
        @error('meeting_link')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <script>
    (function () {
        var physical = document.getElementById('event_format_physical');
        var online = document.getElementById('event_format_online');
        var venueWrap = document.getElementById('event-venue-field');
        var linkWrap = document.getElementById('event-meeting-link-field');
        function sync() {
            var isPhysical = physical && physical.checked;
            if (venueWrap) venueWrap.classList.toggle('hidden', !isPhysical);
            if (linkWrap) linkWrap.classList.toggle('hidden', isPhysical);
        }
        [physical, online].forEach(function (el) { if (el) el.addEventListener('change', sync); });
        sync();
    })();
    </script>

    <div>
        <label class="admin-label mb-2">Status</label>
        <div class="flex gap-4">
            <label class="flex items-center">
                <input type="radio" name="status" value="draft" {{ old('status', $event->status) === 'draft' ? 'checked' : '' }}
                    class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                <span class="ml-2 text-sm text-ink/75">Draft</span>
            </label>
            <label class="flex items-center">
                <input type="radio" name="status" value="scheduled" {{ old('status', $event->status) === 'scheduled' ? 'checked' : '' }}
                    class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                <span class="ml-2 text-sm text-ink/75">Scheduled</span>
            </label>
            @if($event->exists)
            <label class="flex items-center">
                <input type="radio" name="status" value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'checked' : '' }}
                    class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                <span class="ml-2 text-sm text-ink/75">Cancelled</span>
            </label>
            <label class="flex items-center">
                <input type="radio" name="status" value="completed" {{ old('status', $event->status) === 'completed' ? 'checked' : '' }}
                    class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                <span class="ml-2 text-sm text-ink/75">Completed</span>
            </label>
            @endif
        </div>
        @error('status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if(!empty($showReminders) && ($templates ?? collect())->isNotEmpty())
    <div class="pt-6 border-t border-dawn-2/80">
        <h3 class="admin-label mb-3">Auto reminders</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="reminder_24hr_template_id" class="admin-label mb-1">24 hours before</label>
                <select name="reminder_24hr_template_id" id="reminder_24hr_template_id" class="admin-input">
                    <option value="">— No reminder —</option>
                    @foreach(($templates ?? collect()) as $t)
                    <option value="{{ $t->id }}" {{ ($event->reminderSettings->reminder_24hr_template_id ?? null) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="reminder_1hr_template_id" class="admin-label mb-1">1 hour before</label>
                <select name="reminder_1hr_template_id" id="reminder_1hr_template_id" class="admin-input">
                    <option value="">— No reminder —</option>
                    @foreach(($templates ?? collect()) as $t)
                    <option value="{{ $t->id }}" {{ ($event->reminderSettings->reminder_1hr_template_id ?? null) == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @endif
</div>
