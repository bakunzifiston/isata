<div class="admin-card p-6">
    <form method="POST" action="{{ $route }}" enctype="multipart/form-data">
        @csrf
        @method($method)

        <div class="mb-6">
            <label class="admin-label mb-2">Platform</label>
            <div class="flex flex-wrap gap-3">
                @foreach(\App\Models\SocialAccount::platforms() as $slug => $name)
                <label class="flex items-center px-4 py-2 rounded-lg border-2 cursor-pointer transition
                    {{ ($post->social_platform ?? old('platform')) === $slug ? 'border-coral bg-coral/10' : 'border-dawn-2 hover:border-coral/30' }}">
                    <input type="radio" name="platform" value="{{ $slug }}" {{ ($post->social_platform ?? old('platform')) === $slug ? 'checked' : '' }} class="sr-only">
                    <span>{{ $name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        @if($accounts->isNotEmpty())
        <div class="mb-6">
            <label for="social_account_id" class="admin-label mb-1">Connected account</label>
            <select name="social_account_id" id="social_account_id" class="admin-input">
                <option value="">— Use default (API keys) —</option>
                @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" {{ ($post->social_account_id ?? old('social_account_id')) == $acc->id ? 'selected' : '' }}>
                    {{ $acc->name ?? ucfirst($acc->platform) }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="mb-6">
            <label for="event_id" class="admin-label mb-1">Link to event (optional)</label>
            <select name="event_id" id="event_id" class="admin-input">
                <option value="">— No event —</option>
                @foreach($events as $e)
                <option value="{{ $e->id }}" {{ ($post->event_id ?? old('event_id')) == $e->id ? 'selected' : '' }}>
                    {{ $e->name }} ({{ $e->date->format('M j, Y') }})
                </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-ink/50">Use {rsvp_link} or {event_link} in content to add RSVP link</p>
        </div>

        <div class="mb-6">
            <label for="content" class="admin-label mb-1">Content</label>
            <textarea name="content" id="content" rows="6" required
                class="admin-input">{{ old('content', $post->content) }}</textarea>
            <div class="mt-2 flex flex-wrap gap-2">
                <button type="button" onclick="document.getElementById('content').value += '{rsvp_link}'" class="px-2 py-1 text-xs rounded bg-dawn-2 text-ink/75 hover:bg-dawn-2/80">{rsvp_link}</button>
                <button type="button" onclick="document.getElementById('content').value += '{event_name}'" class="px-2 py-1 text-xs rounded bg-dawn-2 text-ink/75 hover:bg-dawn-2/80">{event_name}</button>
                <button type="button" onclick="document.getElementById('content').value += '{event_time}'" class="px-2 py-1 text-xs rounded bg-dawn-2 text-ink/75 hover:bg-dawn-2/80">{event_time}</button>
            </div>
        </div>

        <div class="mb-6">
            <label for="media" class="admin-label mb-1">Media (images)</label>
            <input type="file" name="media[]" id="media" multiple accept="image/*"
                class="block w-full text-sm text-ink/50 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-medium file:bg-coral/10 file:text-coral">
            @if($post->media_paths ?? null)
            <p class="mt-2 text-sm text-ink/50">Current: {{ count($post->media_paths) }} image(s)</p>
            @endif
        </div>

        <div class="mb-6">
            <label for="scheduled_at" class="admin-label mb-1">Schedule</label>
            <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at', $post->scheduled_at?->format('Y-m-d\TH:i')) }}"
                class="admin-input">
        </div>

        <div class="mb-6">
            <label class="admin-label mb-2">Status</label>
            <div class="flex gap-4">
                <label class="flex items-center">
                    <input type="radio" name="status" value="draft" {{ old('status', $post->status ?? 'draft') === 'draft' ? 'checked' : '' }} class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                    <span class="ml-2 text-sm">Draft</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="status" value="scheduled" {{ old('status', $post->status ?? '') === 'scheduled' ? 'checked' : '' }} class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                    <span class="ml-2 text-sm">Scheduled</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="admin-btn-primary">Save</button>
            <a href="{{ route('social.index') }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
</div>
