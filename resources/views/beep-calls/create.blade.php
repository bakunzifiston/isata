@extends('layouts.dashboard')

@section('title', 'Schedule Beep Call - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Schedule voice reminder</h1>
    <p class="admin-page-subtitle">Upload or record audio, then schedule calls to attendees</p>
</div>

<div class="max-w-2xl space-y-6">
    <div class="admin-card p-6">
        <form method="POST" action="{{ route('beep-calls.store') }}" enctype="multipart/form-data" id="beep-form">
            @csrf

            <div class="mb-6">
                <label for="event_id" class="admin-label mb-1">Event</label>
                <select name="event_id" id="event_id" required class="admin-input" onchange="window.location.href='{{ route('beep-calls.create') }}?event_id='+this.value">
                    <option value="">— Select event —</option>
                    @foreach($events as $e)
                    <option value="{{ $e->id }}" {{ ($preselectedEventId ?? '') == $e->id ? 'selected' : '' }}>
                        {{ $e->name }} ({{ $e->date->format('M j, Y') }})
                    </option>
                    @endforeach
                </select>
                @error('event_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="admin-label mb-2">Attendees (with phone or email)</label>
                @if($attendees->isEmpty())
                <p class="text-sm text-ink/50">Select an event first to see attendees.</p>
                @else
                <div class="max-h-48 overflow-y-auto border border-dawn-2/80 rounded-lg p-3 space-y-2">
                    @foreach($attendees as $a)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="attendee_ids[]" value="{{ $a->id }}" class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                        <span class="text-sm">{{ $a->name }} — {{ $a->phone ?: $a->email }}</span>
                    </label>
                    @endforeach
                </div>
                <button type="button" onclick="document.querySelectorAll('input[name=\'attendee_ids[]\']').forEach(c=>c.checked=true)" class="mt-2 text-xs admin-link">Select all</button>
                @endif
                @error('attendee_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="admin-label mb-2">Audio</label>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-ink/50 mb-1">Upload file</p>
                        <input type="file" name="audio_file" id="audio_file" accept=".mp3,.wav,.m4a,.ogg"
                            class="block w-full text-sm text-ink/50 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-medium file:bg-coral/10 file:text-coral">
                    </div>
                    <p class="text-xs text-ink/50">— or —</p>
                    <div>
                        <p class="text-xs text-ink/50 mb-1">Record</p>
                        <div class="flex items-center gap-2">
                            <button type="button" id="record-btn" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                                Record
                            </button>
                            <button type="button" id="stop-btn" disabled class="admin-btn-secondary opacity-50 cursor-not-allowed">
                                Stop
                            </button>
                            <span id="record-status" class="text-sm text-ink/50"></span>
                        </div>
                        <audio id="record-preview" class="mt-2 w-full" controls style="display:none"></audio>
                        <input type="hidden" name="audio_path" id="audio_path">
                    </div>
                </div>
                @error('audio_file')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="call_schedule" class="admin-label mb-1">Call schedule</label>
                <input type="datetime-local" name="call_schedule" id="call_schedule" required
                    min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}"
                    value="{{ old('call_schedule', now()->addHour()->format('Y-m-d\TH:i')) }}"
                    class="admin-input">
                @error('call_schedule')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="admin-btn-primary">Schedule calls</button>
                <a href="{{ route('beep-calls.index') }}" class="admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const recordBtn = document.getElementById('record-btn');
    const stopBtn = document.getElementById('stop-btn');
    const statusEl = document.getElementById('record-status');
    const previewEl = document.getElementById('record-preview');
    const audioPathInput = document.getElementById('audio_path');
    const audioFileInput = document.getElementById('audio_file');

    let mediaRecorder = null;
    let chunks = [];

    recordBtn?.addEventListener('click', async function() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorder = new MediaRecorder(stream);
            chunks = [];

            mediaRecorder.ondataavailable = e => { if (e.data.size) chunks.push(e.data); };
            mediaRecorder.onstop = async () => {
                stream.getTracks().forEach(t => t.stop());
                const blob = new Blob(chunks, { type: 'audio/webm' });
                previewEl.src = URL.createObjectURL(blob);
                previewEl.style.display = 'block';
                statusEl.textContent = 'Uploading...';

                const formData = new FormData();
                formData.append('audio', blob, 'recording.webm');
                formData.append('_token', document.querySelector('input[name="_token"]').value);

                const res = await fetch('{{ route("beep-calls.upload-audio") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.path) {
                    audioPathInput.value = data.path;
                    audioFileInput.value = '';
                    statusEl.textContent = 'Recorded. Ready to schedule.';
                } else {
                    statusEl.textContent = 'Upload failed.';
                }
            };

            mediaRecorder.start();
            recordBtn.disabled = true;
            stopBtn.disabled = false;
            stopBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            statusEl.textContent = 'Recording...';
        } catch (e) {
            statusEl.textContent = 'Microphone access denied. Please upload a file instead.';
        }
    });

    stopBtn?.addEventListener('click', function() {
        if (mediaRecorder && mediaRecorder.state !== 'inactive') {
            mediaRecorder.stop();
            recordBtn.disabled = false;
            stopBtn.disabled = true;
            stopBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    });

    audioFileInput?.addEventListener('change', function() {
        if (this.files.length) audioPathInput.value = '';
    });
});
</script>
@endpush
