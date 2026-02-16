@extends('layouts.dashboard')

@section('title', 'Schedule Beep Call - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-900">Schedule voice reminder</h1>
    <p class="mt-1 text-slate-600">Upload or record audio, then schedule calls to attendees</p>
</div>

<div class="max-w-2xl space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('beep-calls.store') }}" enctype="multipart/form-data" id="beep-form">
            @csrf

            <div class="mb-6">
                <label for="event_id" class="block text-sm font-medium text-slate-700 mb-1">Event</label>
                <select name="event_id" id="event_id" required class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500" onchange="window.location.href='{{ route('beep-calls.create') }}?event_id='+this.value">
                    <option value="">— Select event —</option>
                    @foreach($events as $e)
                    <option value="{{ $e->id }}" {{ ($preselectedEventId ?? '') == $e->id ? 'selected' : '' }}>
                        {{ $e->name }} ({{ $e->date->format('M j, Y') }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">Attendees (with phone or email)</label>
                @if($attendees->isEmpty())
                <p class="text-sm text-slate-500">Select an event first to see attendees.</p>
                @else
                <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3 space-y-2">
                    @foreach($attendees as $a)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="attendee_ids[]" value="{{ $a->id }}" class="rounded border-slate-300 text-indigo-600">
                        <span class="text-sm">{{ $a->name }} — {{ $a->phone ?: $a->email }}</span>
                    </label>
                    @endforeach
                </div>
                <button type="button" onclick="document.querySelectorAll('input[name=\'attendee_ids[]\']').forEach(c=>c.checked=true)" class="mt-2 text-xs text-indigo-600 hover:text-indigo-900">Select all</button>
                @endif
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">Audio</label>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Upload file</p>
                        <input type="file" name="audio_file" id="audio_file" accept=".mp3,.wav,.m4a,.ogg"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-medium file:bg-indigo-50 file:text-indigo-700">
                    </div>
                    <p class="text-xs text-slate-500">— or —</p>
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Record</p>
                        <div class="flex items-center gap-2">
                            <button type="button" id="record-btn" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-medium hover:bg-red-700">
                                Record
                            </button>
                            <button type="button" id="stop-btn" disabled class="px-4 py-2 rounded-lg bg-slate-600 text-white text-sm font-medium opacity-50 cursor-not-allowed">
                                Stop
                            </button>
                            <span id="record-status" class="text-sm text-slate-500"></span>
                        </div>
                        <audio id="record-preview" class="mt-2 w-full" controls style="display:none"></audio>
                        <input type="hidden" name="audio_path" id="audio_path">
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label for="call_schedule" class="block text-sm font-medium text-slate-700 mb-1">Call schedule</label>
                <input type="datetime-local" name="call_schedule" id="call_schedule" required
                    min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}"
                    value="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">Schedule calls</button>
                <a href="{{ route('beep-calls.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700">Cancel</a>
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
