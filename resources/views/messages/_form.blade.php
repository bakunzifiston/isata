<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ $route }}" enctype="multipart/form-data" id="message-form">
        @csrf
        @method($method)

        {{-- Channel selector --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Channel</label>
            <div class="flex flex-wrap gap-3">
                @foreach($channels as $channel)
                <label class="flex items-center px-4 py-2 rounded-lg border-2 cursor-pointer transition channel-label
                    {{ ($message->channel_id ?? old('channel_id')) == $channel->id ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 hover:border-slate-300' }}"
                    data-supports-subject="{{ $channel->supports_subject ? '1' : '0' }}"
                    data-supports-audio="{{ $channel->supports_audio ? '1' : '0' }}">
                    <input type="radio" name="channel_id" value="{{ $channel->id }}" {{ ($message->channel_id ?? old('channel_id')) == $channel->id ? 'checked' : '' }}
                        class="sr-only channel-selector">
                    <span>{{ $channel->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Template selector --}}
        @if($templates->isNotEmpty())
        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Load from template</label>
            <select id="template-select" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                <option value="">-- Select a template --</option>
                @foreach($templates as $tpl)
                <option value="{{ $tpl->id }}" data-channel="{{ $tpl->channel_id }}" data-subject="{{ $tpl->subject }}" data-content="{{ e($tpl->content) }}">
                    {{ $tpl->name }} ({{ $tpl->channel->name }})
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <div id="subject-field" class="mb-6">
            <label for="subject" class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
            <input type="text" name="subject" id="subject" value="{{ old('subject', $message->subject) }}"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="mb-6">
            <label for="content" class="block text-sm font-medium text-slate-700 mb-1">Content</label>
            <textarea name="content" id="content" rows="8" required
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-mono text-sm">{{ old('content', $message->content) }}</textarea>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach(\App\Models\MessageTemplate::personalizationTags() as $tag => $label)
                <button type="button" onclick="document.getElementById('content').value += '{{ $tag }}'" class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-700 hover:bg-slate-200">
                    {{ $tag }}
                </button>
                @endforeach
            </div>
        </div>

        <div id="audio-field" class="mb-6 hidden">
            <label for="audio_file" class="block text-sm font-medium text-slate-700 mb-1">Audio file (Beep Call)</label>
            <input type="file" name="audio_file" id="audio_file" accept=".mp3,.wav,.m4a"
                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-medium file:bg-indigo-50 file:text-indigo-700">
            @if($message->audio_file ?? false)
            <p class="mt-1 text-sm text-slate-500">Current: {{ basename($message->audio_file) }}</p>
            @endif
        </div>

        <div class="mb-6">
            <label for="scheduled_at" class="block text-sm font-medium text-slate-700 mb-1">Schedule (optional)</label>
            <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at', $message->scheduled_at?->format('Y-m-d\TH:i')) }}"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
            <div class="flex gap-4">
                <label class="flex items-center">
                    <input type="radio" name="status" value="draft" {{ old('status', $message->status ?? 'draft') === 'draft' ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                    <span class="ml-2 text-sm">Draft</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="status" value="scheduled" {{ old('status', $message->status ?? '') === 'scheduled' ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                    <span class="ml-2 text-sm">Scheduled</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">Save</button>
            <a href="{{ route('events.messages.index', $event) }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const channelRadios = document.querySelectorAll('input[name="channel_id"]');
    const subjectField = document.getElementById('subject-field');
    const audioField = document.getElementById('audio-field');

    function updateFields() {
        const checked = document.querySelector('input[name="channel_id"]:checked');
        document.querySelectorAll('.channel-label').forEach(l => {
            l.classList.remove('border-indigo-500', 'bg-indigo-50');
            l.classList.add('border-slate-200');
        });
        if (checked) {
            const label = checked.closest('.channel-label');
            if (label) {
                label.classList.remove('border-slate-200');
                label.classList.add('border-indigo-500', 'bg-indigo-50');
                subjectField.classList.toggle('hidden', label.dataset.supportsSubject !== '1');
                audioField.classList.toggle('hidden', label.dataset.supportsAudio !== '1');
            }
        }
    }

    channelRadios.forEach(r => r.addEventListener('change', updateFields));
    updateFields();

    document.getElementById('template-select')?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) return;
        document.querySelector(`input[name="channel_id"][value="${opt.dataset.channel}"]`).checked = true;
        updateFields();
        document.getElementById('subject').value = opt.dataset.subject || '';
        document.getElementById('content').value = opt.dataset.content || '';
    });
});
</script>
@endpush
