<form method="POST" action="{{ $route }}" class="space-y-5">
    @csrf
    @method($method)

    @include('events._form-fields', ['event' => $event, 'showReminders' => $showReminders ?? false, 'templates' => $templates ?? collect()])

    <div class="flex gap-3 pt-4">
        <button type="submit" class="admin-btn-primary">
            {{ $event->exists ? 'Update event' : 'Create event' }}
        </button>
        <a href="{{ $event->exists ? route('events.show', $event) : route('events.index') }}" class="admin-btn-secondary">
            Cancel
        </a>
    </div>
</form>
