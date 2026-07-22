<div class="admin-card p-6">
    <form method="POST" action="{{ $route }}">
        @csrf
        @method($method)

        <div class="mb-6">
            <label for="name" class="admin-label mb-1">Template name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $template->name) }}" required
                class="admin-input">
        </div>

        <div class="mb-6">
            <label for="channel_id" class="admin-label mb-1">Channel</label>
            <select name="channel_id" id="channel_id" required class="admin-input">
                @foreach($channels as $channel)
                <option value="{{ $channel->id }}" data-supports-subject="{{ $channel->supports_subject ? '1' : '0' }}" {{ ($template->channel_id ?? old('channel_id')) == $channel->id ? 'selected' : '' }}>
                    {{ $channel->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div id="subject-field" class="mb-6">
            <label for="subject" class="admin-label mb-1">Subject</label>
            <input type="text" name="subject" id="subject" value="{{ old('subject', $template->subject) }}"
                class="admin-input">
        </div>

        <div class="mb-6">
            <label for="content" class="admin-label mb-1">Content</label>
            <textarea name="content" id="content" rows="10" required
                class="admin-input font-mono text-sm">{{ old('content', $template->content) }}</textarea>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach(\App\Models\MessageTemplate::personalizationTags() as $tag => $label)
                <button type="button" onclick="document.getElementById('content').value += '{{ $tag }}'" class="px-2 py-1 text-xs rounded bg-dawn-2 text-ink/75 hover:bg-dawn-2/80">
                    {{ $tag }}
                </button>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="admin-btn-primary">Save</button>
            <a href="{{ route('templates.index') }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const channelSelect = document.getElementById('channel_id');
    const subjectField = document.getElementById('subject-field');

    function updateFields() {
        const opt = channelSelect.options[channelSelect.selectedIndex];
        const supportsSubject = opt && opt.dataset.supportsSubject === '1';
        subjectField.classList.toggle('hidden', !supportsSubject);
    }

    channelSelect.addEventListener('change', updateFields);
    updateFields();
});
</script>
@endpush
