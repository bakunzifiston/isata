@extends('layouts.dashboard')

@section('title', 'Attendees - ' . $event->name . ' - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <div class="flex items-center gap-3">
            <a href="{{ route('events.show', $event) }}" class="text-slate-600 hover:text-slate-900">← {{ $event->name }}</a>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 mt-2">Attendees</h1>
        <p class="mt-1 text-slate-600">{{ $event->attendees()->count() }} attendee(s)</p>
    </div>
    <div class="flex gap-2">
        <button type="button" onclick="document.getElementById('import-modal').classList.remove('hidden')"
            class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">
            Import
        </button>
        <button type="button" onclick="document.getElementById('add-modal').classList.remove('hidden')"
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
            Add attendee
        </button>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Phone</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Organization</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">RSVP</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($attendees as $attendee)
            <tr class="hover:bg-slate-50" id="attendee-row-{{ $attendee->id }}">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $attendee->name }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $attendee->email }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $attendee->phone ?? '—' }}</td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $attendee->organization ?? '—' }}</td>
                <td class="px-6 py-4">
                    @include('attendees._rsvp-badge', ['status' => $attendee->rsvp_status])
                </td>
                <td class="px-6 py-4 text-right text-sm">
                    <button type="button" onclick="openEditModal({{ json_encode($attendee) }})" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                    <form method="POST" action="{{ route('events.attendees.destroy', [$event, $attendee]) }}" class="inline ml-4" onsubmit="return confirm('Remove this attendee?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Remove</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                    No attendees yet. Add manually or import from CSV.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($attendees->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">{{ $attendees->links() }}</div>
    @endif
</div>

{{-- Add modal --}}
<div id="add-modal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Add attendee</h2>
        <form method="POST" action="{{ route('events.attendees.store', $event) }}">
            @csrf
            @include('attendees._form-fields')
            <div class="flex gap-3 mt-6">
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">Add</button>
                <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit modal --}}
<div id="edit-modal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Edit attendee</h2>
        <form id="edit-form" method="POST">
            @csrf
            @method('PUT')
            @include('attendees._form-fields', ['attendee' => new \App\Models\Attendee()])
            <div class="flex gap-3 mt-6">
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">Save</button>
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- Import modal --}}
<div id="import-modal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center z-50 p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Import attendees</h2>
        <p class="text-sm text-slate-600 mb-4">CSV must have "name" and "email" columns. Optional: phone, organization.</p>

        <form method="POST" action="{{ route('events.attendees.import.csv', $event) }}" enctype="multipart/form-data" class="mb-6">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Upload CSV file</label>
                <input type="file" name="csv_file" accept=".csv,.txt" required
                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-medium file:bg-indigo-50 file:text-indigo-700">
                @error('csv_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">Import from CSV</button>
        </form>

        <div class="border-t border-slate-200 pt-4">
            <label class="block text-sm font-medium text-slate-700 mb-2">Or paste data (name, email, phone, organization per line)</label>
            <form method="POST" action="{{ route('events.attendees.import.bulk', $event) }}">
                @csrf
                <textarea name="bulk_data" rows="6" placeholder="John Doe, john@example.com, +1234567890, Acme Inc.&#10;Jane Smith, jane@example.com"
                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 text-sm font-mono"></textarea>
                <button type="submit" class="mt-2 px-4 py-2 rounded-lg border border-slate-300 text-slate-700 font-medium hover:bg-slate-50">Bulk import</button>
            </form>
        </div>

        <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')"
            class="mt-4 text-sm text-slate-600 hover:text-slate-900">Close</button>
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
