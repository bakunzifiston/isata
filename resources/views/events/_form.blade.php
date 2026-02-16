<form method="POST" action="{{ $route }}" class="space-y-5">
    @csrf
    @method($method)

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Event name *</label>
        <input type="text" name="name" id="name" value="{{ old('name', $event->name) }}" required
            class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
        <textarea name="description" id="description" rows="4"
            class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">{{ old('description', $event->description) }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label for="date" class="block text-sm font-medium text-slate-700 mb-1">Date *</label>
            <input type="date" name="date" id="date" value="{{ old('date', $event->date?->format('Y-m-d')) }}" required
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="time" class="block text-sm font-medium text-slate-700 mb-1">Time</label>
            <input type="time" name="time" id="time" value="{{ old('time', $event->time_formatted) }}"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            @error('time')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="venue" class="block text-sm font-medium text-slate-700 mb-1">Venue</label>
        <input type="text" name="venue" id="venue" value="{{ old('venue', $event->venue) }}"
            class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            placeholder="Physical location or address">
        @error('venue')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="meeting_link" class="block text-sm font-medium text-slate-700 mb-1">Meeting link</label>
        <input type="url" name="meeting_link" id="meeting_link" value="{{ old('meeting_link', $event->meeting_link) }}"
            class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            placeholder="https://meet.example.com/...">
        @error('meeting_link')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
        <div class="flex gap-4">
            <label class="flex items-center">
                <input type="radio" name="status" value="draft" {{ old('status', $event->status) === 'draft' ? 'checked' : '' }}
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-slate-700">Draft</span>
            </label>
            <label class="flex items-center">
                <input type="radio" name="status" value="scheduled" {{ old('status', $event->status) === 'scheduled' ? 'checked' : '' }}
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-slate-700">Scheduled</span>
            </label>
            @if($event->exists)
            <label class="flex items-center">
                <input type="radio" name="status" value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'checked' : '' }}
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-slate-700">Cancelled</span>
            </label>
            <label class="flex items-center">
                <input type="radio" name="status" value="completed" {{ old('status', $event->status) === 'completed' ? 'checked' : '' }}
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-slate-700">Completed</span>
            </label>
            @endif
        </div>
        <p class="mt-1 text-xs text-slate-500">Draft: save for later (offline-ready). Scheduled: publishes and counts toward usage.</p>
        @error('status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if(!empty($showReminders) && ($templates ?? collect())->isNotEmpty())
    <div class="pt-6 border-t border-slate-200">
        <h3 class="text-sm font-medium text-slate-700 mb-3">Auto reminders</h3>
        <p class="text-xs text-slate-500 mb-4">Send automatic reminders to attendees before the event.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="reminder_24hr_template_id" class="block text-sm font-medium text-slate-700 mb-1">24 hours before</label>
                <select name="reminder_24hr_template_id" id="reminder_24hr_template_id" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                    <option value="">— No reminder —</option>
                    @foreach(($templates ?? collect()) as $t)
                    <option value="{{ $t->id }}" {{ ($event->reminderSettings->reminder_24hr_template_id ?? null) == $t->id ? 'selected' : '' }}>
                        {{ $t->name }} ({{ $t->channel->name }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="reminder_1hr_template_id" class="block text-sm font-medium text-slate-700 mb-1">1 hour before</label>
                <select name="reminder_1hr_template_id" id="reminder_1hr_template_id" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                    <option value="">— No reminder —</option>
                    @foreach(($templates ?? collect()) as $t)
                    <option value="{{ $t->id }}" {{ ($event->reminderSettings->reminder_1hr_template_id ?? null) == $t->id ? 'selected' : '' }}>
                        {{ $t->name }} ({{ $t->channel->name }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @endif

    <div class="flex gap-3 pt-4">
        <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
            {{ $event->exists ? 'Update event' : 'Create event' }}
        </button>
        <a href="{{ $event->exists ? route('events.show', $event) : route('events.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50">
            Cancel
        </a>
    </div>
</form>
