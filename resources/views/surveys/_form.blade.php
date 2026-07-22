<div class="admin-card p-6">
    <form method="POST" action="{{ $route }}" id="survey-form">
        @csrf
        @method($method)

        <div class="mb-6">
            <label for="event_id" class="admin-label mb-1">Event</label>
            <select name="event_id" id="event_id" required class="admin-input">
                <option value="">— Select event —</option>
                @foreach($events as $e)
                <option value="{{ $e->id }}" {{ ($survey->event_id ?? $preselectedEventId ?? '') == $e->id ? 'selected' : '' }}>
                    {{ $e->name }} ({{ $e->date->format('M j, Y') }})
                </option>
                @endforeach
            </select>
        </div>

        <div class="mb-6">
            <label for="name" class="admin-label mb-1">Survey name</label>
            <input type="text" name="name" id="name" value="{{ old('name', $survey->name) }}" required
                class="admin-input">
        </div>

        <div class="mb-6">
            <label for="description" class="admin-label mb-1">Description</label>
            <textarea name="description" id="description" rows="2" class="admin-input">{{ old('description', $survey->description) }}</textarea>
        </div>

        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <label class="admin-label">Questions</label>
                <button type="button" id="add-question" class="text-sm admin-link">+ Add question</button>
            </div>
            <div id="questions-container" class="space-y-4">
                @php $questions = old('questions', $survey->questions ?? [['id' => 'q1', 'type' => 'text', 'label' => '', 'options' => [], 'required' => false]]); @endphp
                @foreach($questions as $i => $q)
                <div class="question-block p-4 border border-dawn-2/80 rounded-lg bg-dawn-2/30" data-index="{{ $i }}">
                    <div class="flex gap-2 mb-2">
                        <input type="hidden" name="questions[{{ $i }}][id]" value="{{ $q['id'] ?? 'q' . ($i + 1) }}">
                        <select name="questions[{{ $i }}][type]" class="question-type px-3 py-1 rounded border border-dawn-2 text-sm">
                            @foreach(\App\Models\Survey::questionTypes() as $k => $v)
                            <option value="{{ $k }}" {{ ($q['type'] ?? 'text') == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="questions[{{ $i }}][label]" placeholder="Question" value="{{ $q['label'] ?? '' }}" required
                            class="flex-1 px-3 py-1 rounded border border-dawn-2 text-sm">
                        <label class="flex items-center gap-1 text-sm">
                            <input type="checkbox" name="questions[{{ $i }}][required]" value="1" {{ ($q['required'] ?? false) ? 'checked' : '' }}>
                            Required
                        </label>
                        <button type="button" class="remove-question text-red-600 hover:text-red-800 text-sm">Remove</button>
                    </div>
                    <div class="options-container mt-2" style="{{ in_array($q['type'] ?? '', ['select', 'multiple']) ? '' : 'display:none' }}">
                        <label class="text-xs text-ink/50">Options (one per line)</label>
                        <textarea name="questions[{{ $i }}][options]" rows="2" class="w-full mt-1 px-3 py-1 rounded border border-dawn-2 text-sm">
{{ implode("\n", $q['options'] ?? []) }}</textarea>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mb-6">
            <label for="thank_you_message" class="admin-label mb-1">Thank-you message</label>
            <textarea name="thank_you_message" id="thank_you_message" rows="3" class="admin-input">{{ old('thank_you_message', $survey->thank_you_message) }}</textarea>
            <p class="mt-1 text-xs text-ink/50">Shown after survey submission</p>
        </div>

        <div class="mb-6">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $survey->is_active ?? true) ? 'checked' : '' }} class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                <span class="text-sm text-ink/75">Active (accepting responses)</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="admin-btn-primary">Save</button>
            <a href="{{ route('surveys.index') }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let index = document.querySelectorAll('.question-block').length;

    document.getElementById('add-question')?.addEventListener('click', function() {
        const id = 'q' + Date.now();
        const block = document.createElement('div');
        block.className = 'question-block p-4 border border-dawn-2/80 rounded-lg bg-dawn-2/30';
        block.dataset.index = index;
        block.innerHTML = `
            <div class="flex gap-2 mb-2">
                <input type="hidden" name="questions[${index}][id]" value="${id}">
                <select name="questions[${index}][type]" class="question-type px-3 py-1 rounded border border-dawn-2 text-sm">
                    <option value="text">Text</option>
                    <option value="rating">Rating (1-5)</option>
                    <option value="select">Single choice</option>
                    <option value="multiple">Multiple choice</option>
                </select>
                <input type="text" name="questions[${index}][label]" placeholder="Question" required class="flex-1 px-3 py-1 rounded border border-dawn-2 text-sm">
                <label class="flex items-center gap-1 text-sm">
                    <input type="checkbox" name="questions[${index}][required]" value="1"> Required
                </label>
                <button type="button" class="remove-question text-red-600 hover:text-red-800 text-sm">Remove</button>
            </div>
            <div class="options-container mt-2" style="display:none">
                <label class="text-xs text-ink/50">Options (one per line)</label>
                <textarea name="questions[${index}][options]" rows="2" class="w-full mt-1 px-3 py-1 rounded border border-dawn-2 text-sm"></textarea>
            </div>
        `;
        document.getElementById('questions-container').appendChild(block);
        block.querySelector('.question-type').addEventListener('change', toggleOptions);
        block.querySelector('.remove-question').addEventListener('click', () => block.remove());
        index++;
    });

    function toggleOptions(e) {
        const block = e.target.closest('.question-block');
        const opts = block.querySelector('.options-container');
        opts.style.display = ['select', 'multiple'].includes(e.target.value) ? 'block' : 'none';
    }

    document.querySelectorAll('.question-type').forEach(el => el.addEventListener('change', toggleOptions));
    document.querySelectorAll('.remove-question').forEach(btn => {
        btn.addEventListener('click', function() {
            if (document.querySelectorAll('.question-block').length > 1) this.closest('.question-block').remove();
        });
    });
});
</script>
@endpush
