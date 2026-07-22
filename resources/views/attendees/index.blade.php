@extends('layouts.dashboard')

@section('title', 'Attendees - ' . $event->name . ' - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('events.show', $event) }}" class="admin-link-muted">← {{ $event->name }}</a>
        </div>
        <h1 class="admin-page-title mt-2">Attendees</h1>
        <p class="admin-page-subtitle">{{ $event->attendees()->count() }} attendee(s)</p>
    </div>
    <div class="flex gap-2">
        <button type="button" onclick="document.getElementById('import-modal').classList.remove('hidden')"
            class="admin-btn-secondary">
            Import
        </button>
        <button type="button" onclick="document.getElementById('add-modal').classList.remove('hidden')"
            class="admin-btn-primary">
            Add attendee
        </button>
    </div>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Organization</th>
                <th>RSVP</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendees as $attendee)
            <tr id="attendee-row-{{ $attendee->id }}">
                <td class="font-medium text-ink">{{ $attendee->name }}</td>
                <td>{{ $attendee->email }}</td>
                <td>{{ $attendee->phone ?? '—' }}</td>
                <td>{{ $attendee->organization ?? '—' }}</td>
                <td>
                    @include('attendees._rsvp-badge', ['status' => $attendee->rsvp_status])
                </td>
                <td class="text-right">
                    <button type="button" onclick="openEditModal({{ json_encode($attendee) }})" class="admin-link">Edit</button>
                    <form method="POST" action="{{ route('events.attendees.destroy', [$event, $attendee]) }}" class="inline ml-4" onsubmit="return confirm('Remove this attendee?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Remove</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-12 text-center text-ink/50">
                    No attendees yet. Add manually or import from CSV.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($attendees->hasPages())
    <div class="border-t border-dawn-2/80 px-6 py-4">{{ $attendees->links() }}</div>
    @endif
</div>

{{-- Add modal --}}
<div id="add-modal" class="admin-modal-overlay hidden" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="admin-modal" onclick="event.stopPropagation()">
        <h2 class="mb-4 text-lg font-semibold text-ink">Add attendee</h2>
        <form method="POST" action="{{ route('events.attendees.store', $event) }}">
            @csrf
            @include('attendees._form-fields')
            <div class="mt-6 flex gap-3">
                <button type="submit" class="admin-btn-primary">Add</button>
                <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')" class="admin-btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit modal --}}
<div id="edit-modal" class="admin-modal-overlay hidden" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="admin-modal" onclick="event.stopPropagation()">
        <h2 class="mb-4 text-lg font-semibold text-ink">Edit attendee</h2>
        <form id="edit-form" method="POST">
            @csrf
            @method('PUT')
            @include('attendees._form-fields', ['attendee' => new \App\Models\Attendee()])
            <div class="mt-6 flex gap-3">
                <button type="submit" class="admin-btn-primary">Save</button>
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="admin-btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Import modal --}}
<div id="import-modal" class="admin-modal-overlay hidden" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="admin-modal admin-modal-lg" onclick="event.stopPropagation()">
        <h2 class="mb-4 text-lg font-semibold text-ink">Import attendees</h2>
        <p class="mb-4 text-sm text-ink/65">CSV must have "name" and "email" columns. Optional: phone, organization.</p>

        <form method="POST" action="{{ route('events.attendees.import.csv', $event) }}" enctype="multipart/form-data" class="mb-6">
            @csrf
            <div class="mb-4">
                <label class="admin-label mb-1">Upload CSV file</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required
                    class="block w-full text-sm text-ink/60 file:mr-4 file:rounded-lg file:border-0 file:bg-coral/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-coral">
                @error('csv_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="admin-btn-primary">Import from CSV</button>
        </form>

        <div class="border-t border-dawn-2/80 pt-4">
            <label class="admin-label mb-2">Or paste data (name, email, phone, organization per line)</label>
            <form method="POST" action="{{ route('events.attendees.import.bulk', $event) }}">
                @csrf
                <textarea name="bulk_data" rows="6" placeholder="John Doe, john@example.com, +1234567890, Acme Inc.&#10;Jane Smith, jane@example.com"
                    class="admin-input font-mono text-sm"></textarea>
                <button type="submit" class="admin-btn-secondary mt-2">Bulk import</button>
            </form>
        </div>

        <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')"
            class="admin-link-muted mt-4 text-sm">Close</button>
    </div>
</div>

@push('scripts')
<script>
function openEditModal(attendee) {
    const form = document.getElementById('edit-form');
    form.action = '{{ route("events.attendees.index", $event) }}/' + attendee.id;
    form.querySelector('[name=name]').value = attendee.name;
    form.querySelector('[name=email]').value = attendee.email;
    form.querySelector('[name=phone]').value = attendee.phone || '';
    form.querySelector('[name=organization]').value = attendee.organization || '';
    form.querySelector('[name=rsvp_status][value="' + attendee.rsvp_status + '"]').checked = true;
    document.getElementById('edit-modal').classList.remove('hidden');
}
</script>
@endpush
@endsection
