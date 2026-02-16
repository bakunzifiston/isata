<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
    <form method="POST" action="{{ $route }}" enctype="multipart/form-data">
        @csrf
        @method($method)

        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Platform</label>
            <div class="flex flex-wrap gap-3">
                @foreach(\App\Models\SocialAccount::platforms() as $slug => $name)
                <label class="flex items-center px-4 py-2 rounded-lg border-2 cursor-pointer transition
                    {{ ($post->platform ?? old('platform')) === $slug ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" name="platform" value="{{ $slug }}" {{ ($post->platform ?? old('platform')) === $slug ? 'checked' : '' }} class="sr-only">
                    <span>{{ $name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        @if($accounts->isNotEmpty())
        <div class="mb-6">
            <label for="social_account_id" class="block text-sm font-medium text-slate-700 mb-1">Connected account</label>
            <select name="social_account_id" id="social_account_id" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
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
            <label for="event_id" class="block text-sm font-medium text-slate-700 mb-1">Link to event (optional)</label>
            <select name="event_id" id="event_id" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                <option value="">— No event —</option>
                @foreach($events as $e)
                <option value="{{ $e->id }}" {{ ($post->event_id ?? old('event_id')) == $e->id ? 'selected' : '' }}>
                    {{ $e->name }} ({{ $e->date->format('M j, Y') }})
                </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">Use {rsvp_link} or {event_link} in content to add RSVP link</p>
        </div>

        <div class="mb-6">
            <label for="content" class="block text-sm font-medium text-slate-700 mb-1">Content</label>
            <textarea name="content" id="content" rows="6" required
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">{{ old('content', $post->content) }}</textarea>
            <div class="mt-2 flex flex-wrap gap-2">
                <button type="button" onclick="document.getElementById('content').value += '{rsvp_link}'" class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-700 hover:bg-slate-200">{rsvp_link}</button>
                <button type="button" onclick="document.getElementById('content').value += '{event_name}'" class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-700 hover:bg-slate-200">{event_name}</button>
                <button type="button" onclick="document.getElementById('content').value += '{event_time}'" class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-700 hover:bg-slate-200">{event_time}</button>
            </div>
        </div>

        <div class="mb-6">
            <label for="media" class="block text-sm font-medium text-slate-700 mb-1">Media (images)</label>
            <input type="file" name="media[]" id="media" multiple accept="image/*"
                class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-medium file:bg-indigo-50 file:text-indigo-700">
            @if($post->media_paths ?? null)
            <p class="mt-2 text-sm text-slate-500">Current: {{ count($post->media_paths) }} image(s)</p>
            @endif
        </div>

        <div class="mb-6">
            <label for="scheduled_at" class="block text-sm font-medium text-slate-700 mb-1">Schedule</label>
            <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at', $post->scheduled_at?->format('Y-m-d\TH:i')) }}"
                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500">
        </div>

        <div class="mb-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
            <div class="flex gap-4">
                <label class="flex items-center">
                    <input type="radio" name="status" value="draft" {{ old('status', $post->status ?? 'draft') === 'draft' ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                    <span class="ml-2 text-sm">Draft</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="status" value="scheduled" {{ old('status', $post->status ?? '') === 'scheduled' ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                    <span class="ml-2 text-sm">Scheduled</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">Save</button>
            <a href="{{ route('social.index') }}" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700">Cancel</a>
        </div>
    </form>
</div>
