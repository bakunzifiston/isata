<div class="admin-card p-6">
    @php
    $hasSavedIdentities = ($senderIdentities ?? collect())->isNotEmpty();
    $__emailSenderType = old(
        'email_sender_type',
        filled($message->sender_custom_email ?? null)
            ? 'custom'
            : ($hasSavedIdentities ? 'saved' : 'custom'),
    );
    $__defaultSenderIdentityId = $message->sender_identity_id ?? (($senderIdentities ?? collect())->first()?->id);
    $__contentType = old(
        'content_type',
        $message->content_type ?? ($message->exists ? \App\Models\Message::CONTENT_TYPE_TEXT : \App\Models\Message::CONTENT_TYPE_STRUCTURED),
    );
    $__decodedStructuredOld = json_decode((string) old('content_document', ''), true);
    if (is_array($__decodedStructuredOld)) {
        $structuredForMount = \App\Support\StructuredMessageDocument::normalize($__decodedStructuredOld);
    } elseif ($message->exists && ($message->content_type ?? null) === \App\Models\Message::CONTENT_TYPE_STRUCTURED && is_array($message->content_document ?? null)) {
        $structuredForMount = \App\Support\StructuredMessageDocument::normalize($message->content_document);
    } else {
        $structuredForMount = [
            'v' => 1,
            'blocks' => [[
                'id' => (string) Illuminate\Support\Str::uuid(),
                'type' => 'section',
                'variant' => 'muted',
                'title' => 'Main message',
                'blocks' => [
                    ['id' => (string) Illuminate\Support\Str::uuid(), 'type' => 'heading', 'level' => 2, 'text' => "You're invited"],
                    ['id' => (string) Illuminate\Support\Str::uuid(), 'type' => 'spacer', 'size' => 'md'],
                    ['id' => (string) Illuminate\Support\Str::uuid(), 'type' => 'paragraph', 'text' => "Hi {name},\n\nWe're looking forward to {event_name}. Add details below."],
                ],
            ]],
        ];
    }
    $__contentDocumentValue = old('content_document');
    if (! filled($__contentDocumentValue) && $__contentType === \App\Models\Message::CONTENT_TYPE_STRUCTURED) {
        $__contentDocumentValue = json_encode($structuredForMount, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
@endphp
<form method="POST" action="{{ $route }}" enctype="multipart/form-data" id="message-form"
    data-has-content-image="{{ filled($message->content_image ?? null) ? '1' : '0' }}"
    data-has-audio="{{ filled($message->audio_file ?? null) ? '1' : '0' }}">
        @csrf
        @method($method)

        {{-- Channel selector --}}
        <div class="mb-6">
            <label class="admin-label mb-2">Channel</label>
            <div class="flex flex-wrap gap-3">
                @foreach($channels as $channel)
                <label class="flex items-center px-4 py-2 rounded-lg border-2 cursor-pointer transition channel-label
                    {{ ($message->channel_id ?? old('channel_id')) == $channel->id ? 'border-coral bg-coral/10' : 'border-dawn-2 hover:border-coral/30' }}"
                    data-supports-subject="{{ $channel->supports_subject ? '1' : '0' }}"
                    data-supports-audio="{{ $channel->supports_audio ? '1' : '0' }}"
                    data-supports-attachment="{{ ($channel->supports_attachment ?? false) ? '1' : '0' }}"
                    data-channel-slug="{{ $channel->slug }}">
                    <input type="radio" name="channel_id" value="{{ $channel->id }}" {{ ($message->channel_id ?? old('channel_id')) == $channel->id ? 'checked' : '' }}
                        class="sr-only channel-selector">
                    <span>{{ $channel->name }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <div id="sender-field" class="mb-6 hidden space-y-4">
            @if(($senderIdentities ?? collect())->isEmpty())
            <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-sm px-4 py-3">
                Add one or more email sender profiles for your organization, then choose one here (or send with a custom address for this message only).
                <a href="{{ route('email-senders.create') }}" target="_blank" rel="noopener" class="font-medium underline">Add sender address</a>
            </div>
            @endif

            <span class="admin-label">Send email from *</span>
            <div class="flex flex-wrap gap-4">
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="email_sender_type" value="saved" {{ $__emailSenderType === 'saved' ? 'checked' : '' }}
                        class="rounded border-dawn-2 text-coral focus:ring-coral/20 email-sender-type">
                    <span class="ml-2 text-sm text-ink/75">Saved sender</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="email_sender_type" value="custom" {{ $__emailSenderType === 'custom' ? 'checked' : '' }}
                        class="rounded border-dawn-2 text-coral focus:ring-coral/20 email-sender-type">
                    <span class="ml-2 text-sm text-ink/75">Custom (this message only)</span>
                </label>
            </div>
            @error('email_sender_type')
            <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div id="sender-saved-panel" class="{{ $__emailSenderType === 'custom' ? 'hidden' : '' }} space-y-1">
                <label for="sender_identity_id" class="admin-label">Saved From address</label>
                <select name="sender_identity_id" id="sender_identity_id"
                    class="admin-input">
                    @foreach($senderIdentities ?? [] as $senderIdentityRow)
                    <option value="{{ $senderIdentityRow->id }}" {{ (string) old('sender_identity_id', $__defaultSenderIdentityId) === (string) $senderIdentityRow->id ? 'selected' : '' }}>
                        {{ $senderIdentityRow->displayLabel() }}
                    </option>
                    @endforeach
                </select>
                <p class="text-xs text-ink/50">Manage profiles under <a href="{{ route('email-senders.index') }}" class="admin-link">Email senders</a>.</p>
                @error('sender_identity_id')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="sender-custom-panel" class="{{ $__emailSenderType === 'saved' ? 'hidden' : '' }} space-y-3">
                <div>
                    <label for="sender_custom_name" class="admin-label mb-1">Display name *</label>
                    <input type="text" name="sender_custom_name" id="sender_custom_name"
                        value="{{ old('sender_custom_name', $message->sender_custom_name) }}"
                        class="admin-input"
                        autocomplete="organization">
                    @error('sender_custom_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="sender_custom_email" class="admin-label mb-1">From email *</label>
                    <input type="email" name="sender_custom_email" id="sender_custom_email"
                        value="{{ old('sender_custom_email', $message->sender_custom_email) }}"
                        class="admin-input"
                        autocomplete="email">
                    @error('sender_custom_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <p class="text-xs text-ink/50">Not saved to your address book. Switch to “Saved sender” to reuse addresses later.</p>
            </div>
        </div>

        {{-- Template selector --}}
        @if($templates->isNotEmpty())
        <div class="mb-6">
            <label class="admin-label mb-2">Load from template</label>
            <select id="template-select" class="admin-input">
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
            <label for="subject" class="admin-label mb-1">Subject</label>
            <input type="text" name="subject" id="subject" value="{{ old('subject', $message->subject) }}"
                class="admin-input">
        </div>

        <div id="content-format-section" class="mb-6 space-y-3">
            <span class="admin-label">Content format *</span>
            <div class="flex flex-wrap gap-3">
                <label class="flex items-center cursor-pointer rounded-lg border-2 px-3 py-2 transition content-type-label {{ $__contentType === \App\Models\Message::CONTENT_TYPE_STRUCTURED ? 'border-coral bg-coral/10' : 'border-dawn-2' }}">
                    <input type="radio" name="content_type" value="{{ \App\Models\Message::CONTENT_TYPE_STRUCTURED }}" {{ $__contentType === \App\Models\Message::CONTENT_TYPE_STRUCTURED ? 'checked' : '' }}
                        class="rounded border-dawn-2 text-coral focus:ring-coral/20 content-type-radio">
                    <span class="ml-2 text-sm font-medium text-ink">Structured</span>
                </label>
                <label class="flex items-center cursor-pointer rounded-lg border-2 px-3 py-2 transition content-type-label {{ $__contentType === \App\Models\Message::CONTENT_TYPE_TEXT ? 'border-coral bg-coral/10' : 'border-dawn-2' }}">
                    <input type="radio" name="content_type" value="{{ \App\Models\Message::CONTENT_TYPE_TEXT }}" {{ $__contentType === \App\Models\Message::CONTENT_TYPE_TEXT ? 'checked' : '' }}
                        class="rounded border-dawn-2 text-coral focus:ring-coral/20 content-type-radio">
                    <span class="ml-2 text-sm text-ink/75">Plain text</span>
                </label>
                <label class="flex items-center cursor-pointer rounded-lg border-2 px-3 py-2 transition content-type-label {{ $__contentType === \App\Models\Message::CONTENT_TYPE_IMAGE ? 'border-coral bg-coral/10' : 'border-dawn-2' }}">
                    <input type="radio" name="content_type" value="{{ \App\Models\Message::CONTENT_TYPE_IMAGE }}" {{ $__contentType === \App\Models\Message::CONTENT_TYPE_IMAGE ? 'checked' : '' }}
                        class="rounded border-dawn-2 text-coral focus:ring-coral/20 content-type-radio">
                    <span class="ml-2 text-sm text-ink/75">Image + caption</span>
                </label>
            </div>
            @error('content_type')
            <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div id="structured-builder-shell" class="{{ $__contentType === \App\Models\Message::CONTENT_TYPE_STRUCTURED ? '' : 'hidden' }} space-y-4 rounded-xl border border-dawn-2 bg-dawn-2/30 p-5">
                @error('content_document')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <input type="hidden" name="content_document" id="content_document" value="{{ e($__contentDocumentValue ?? '') }}">
                <div id="structured-upload-staging" class="fixed top-0 left-0 opacity-0 w-px h-px overflow-hidden pointer-events-none" aria-hidden="true"></div>
                <div id="message-builder-mount" data-initial='@json($structuredForMount)'></div>
            </div>

            <div id="content-image-panel" class="{{ $__contentType !== \App\Models\Message::CONTENT_TYPE_IMAGE ? 'hidden' : '' }} space-y-2 mt-4">
                <label for="content_image" class="admin-label mb-1">Image *</label>
                <input type="file" name="content_image" id="content_image" accept=".jpeg,.jpg,.png,.gif,.webp"
                    class="block w-full text-sm text-ink/60 file:mr-4 file:rounded-lg file:border-0 file:bg-coral/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-coral">
                <p class="text-xs text-ink/50">JPEG, PNG, GIF, or WebP · max 5 MB</p>
                @error('content_image')
                <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if($message->exists && ($message->content_image ?? false))
                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <img src="{{ $message->content_image_url }}" alt="" class="max-h-32 rounded-lg border border-dawn-2 shadow-sm">
                    <label class="inline-flex items-center gap-2 text-sm text-ink/75 cursor-pointer">
                        <input type="checkbox" name="remove_content_image" id="remove_content_image" value="1" class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                        Remove current image
                    </label>
                </div>
                @endif
            </div>

            <p class="text-xs text-ink/50 mb-1">Personalization tags (insert into text or caption):</p>
            <div id="content-text-tags" class="mb-2 flex flex-wrap gap-2">
                @foreach(\App\Models\MessageTemplate::personalizationTags() as $tag => $label)
                <button type="button" class="tag-insert-btn rounded bg-dawn-2 px-2 py-1 text-xs text-ink/75 hover:bg-dawn-2/80" data-tag="{{ $tag }}" title="{{ $label }}">
                    {{ $tag }}
                </button>
                @endforeach
            </div>

            <div id="classic-content-wrap" class="{{ $__contentType === \App\Models\Message::CONTENT_TYPE_STRUCTURED ? 'hidden' : '' }}">
                <label for="content" class="admin-label mb-1 {{ $__contentType === \App\Models\Message::CONTENT_TYPE_STRUCTURED ? 'hidden' : '' }}" id="content-field-label">{{ $__contentType === \App\Models\Message::CONTENT_TYPE_IMAGE ? 'Caption (optional)' : 'Message body *' }}</label>
                <div id="plain-text-editor-anchor" class="mt-1 {{ ($__contentType ?? '') === \App\Models\Message::CONTENT_TYPE_IMAGE ? 'hidden' : '' }}"></div>
                <textarea name="content" id="content" rows="10"
                    class="admin-input mt-2 text-sm">{{ old('content', $message->content) }}</textarea>
            </div>
            @error('content')
            <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div id="audio-field" class="mb-6 hidden">
            <label for="audio_file" class="admin-label mb-1" id="audio-field-label">Audio file (Beep Call)</label>
            <input type="file" name="audio_file" id="audio_file" accept=".mp3,.wav,.m4a"
                class="block w-full text-sm text-ink/60 file:mr-4 file:rounded-lg file:border-0 file:bg-coral/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-coral">
            <p class="text-xs text-ink/50 mt-1">MP3, WAV, or M4A · max 10 MB. This is what attendees will hear on the call.</p>
            @error('audio_file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @if($message->audio_file ?? false)
            <p class="mt-1 text-sm text-ink/50">Current: {{ basename($message->audio_file) }}</p>
            @endif
        </div>

        <div id="attachment-field" class="mb-6 hidden">
            <label for="attachment_file" class="admin-label mb-1">Attachment (Email / SMS)</label>
            <input type="file" name="attachment_file" id="attachment_file" accept=".pdf,.jpeg,.jpg,.png,.gif,.webp,.txt,.csv,.doc,.docx"
                class="block w-full text-sm text-ink/60 file:mr-4 file:rounded-lg file:border-0 file:bg-coral/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-coral">
            <p class="mt-1 text-xs text-ink/50">Optional. PDF, images, Word, or text · max 10 MB. SMS carriers typically treat attachments as MMS.</p>
            @error('attachment_file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @if($message->exists && ($message->attachment_file ?? false))
            <div class="mt-2 rounded-lg border border-dawn-2 bg-dawn-2/30 px-3 py-2 flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-ink/75">Current file: <a href="{{ route('messages.attachment.download', $message) }}" class="admin-link hover:underline">{{ basename($message->attachment_file) }}</a></p>
                <label class="inline-flex items-center gap-2 text-sm text-ink/75 cursor-pointer">
                    <input type="checkbox" name="remove_attachment" value="1" class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                    Remove attachment
                </label>
            </div>
            @endif
        </div>

        <div class="mb-6">
            <label for="scheduled_at" class="admin-label mb-1">Schedule (optional)</label>
            <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at', $message->scheduled_at?->format('Y-m-d\TH:i')) }}"
                class="admin-input">
        </div>

        <div class="mb-6">
            <label class="admin-label mb-2">Status</label>
            <div class="flex gap-4">
                <label class="flex items-center">
                    <input type="radio" name="status" value="draft" {{ old('status', $message->status ?? 'draft') === 'draft' ? 'checked' : '' }} class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                    <span class="ml-2 text-sm">Draft</span>
                </label>
                <label class="flex items-center">
                    <input type="radio" name="status" value="scheduled" {{ old('status', $message->status ?? '') === 'scheduled' ? 'checked' : '' }} class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                    <span class="ml-2 text-sm">Scheduled</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="admin-btn-primary">Save</button>
            <a href="{{ route('events.messages.index', $event) }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
@vite(['resources/js/message-builder.js'])
<script>
window.__ISATA__ = window.__ISATA__ || {};
window.__ISATA__.storagePublicBase = @json(rtrim(asset('storage'), '/'));
window.__ISATA__.mergeTags = @json(
    collect(\App\Models\MessageTemplate::personalizationTags())
        ->map(fn(string $displayLabel, string $token) => ['tag' => $token, 'label' => $displayLabel])
        ->values()
);
window.__ISATA__.contentTypeText = @json(\App\Models\Message::CONTENT_TYPE_TEXT);
window.__ISATA__.channelSlugBeepCall = @json(\App\Models\Channel::SLUG_BEEP_CALL);
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const channelRadios = document.querySelectorAll('input[name="channel_id"]');
    const subjectField = document.getElementById('subject-field');
    const audioField = document.getElementById('audio-field');
    const attachmentField = document.getElementById('attachment-field');
    const senderField = document.getElementById('sender-field');
    const senderSavedPanel = document.getElementById('sender-saved-panel');
    const senderCustomPanel = document.getElementById('sender-custom-panel');
    const identitySelect = document.getElementById('sender_identity_id');
    const senderCustomNameInput = document.getElementById('sender_custom_name');
    const senderCustomEmailInput = document.getElementById('sender_custom_email');
    const contentForm = document.getElementById('message-form');
    const contentImagePanel = document.getElementById('content-image-panel');
    const contentImageInput = document.getElementById('content_image');
    const contentField = document.getElementById('content');
    const contentFieldLabel = document.getElementById('content-field-label');
    const removeContentImage = document.getElementById('remove_content_image');
    const structuredShell = document.getElementById('structured-builder-shell');
    const classicContentWrap = document.getElementById('classic-content-wrap');
    const contentDocumentInput = document.getElementById('content_document');
    const staging = document.getElementById('structured-upload-staging');
    const contentFormatSection = document.getElementById('content-format-section');
    const audioFileInput = document.getElementById('audio_file');
    const audioFieldLabel = document.getElementById('audio-field-label');
    const textContentTypeRadio = document.querySelector('input[name="content_type"][value="{{ \App\Models\Message::CONTENT_TYPE_TEXT }}"]');

    function isBeepCallChannelSelected() {
        const checked = document.querySelector('input[name="channel_id"]:checked');
        const label = checked ? checked.closest('.channel-label') : null;
        return !!(label && label.dataset.channelSlug === (window.__ISATA__?.channelSlugBeepCall || 'beep_call'));
    }

    function syncBeepCallPanels() {
        const isBeepCall = isBeepCallChannelSelected();
        const hasExistingAudio = contentForm && contentForm.getAttribute('data-has-audio') === '1';

        if (contentFormatSection) {
            contentFormatSection.classList.toggle('hidden', isBeepCall);
        }

        if (isBeepCall && textContentTypeRadio) {
            textContentTypeRadio.checked = true;
        }

        if (audioFieldLabel) {
            audioFieldLabel.textContent = hasExistingAudio && isBeepCall
                ? 'Replace audio file (optional)'
                : (isBeepCall ? 'Audio file (Beep Call) *' : 'Audio file (Beep Call)');
        }

        if (audioFileInput) {
            audioFileInput.required = isBeepCall && !hasExistingAudio;
        }

        if (isBeepCall) {
            syncContentTypePanels();
        }
    }

    function syncContentTypePanels() {
        const typeRadio = document.querySelector('input[name="content_type"]:checked');
        const val = typeRadio ? typeRadio.value : '';
        var isImage = val === '{{ \App\Models\Message::CONTENT_TYPE_IMAGE }}';
        var isStructured = val === '{{ \App\Models\Message::CONTENT_TYPE_STRUCTURED }}';
        var isBeepCall = isBeepCallChannelSelected();
        var hasExisting = contentForm && contentForm.getAttribute('data-has-content-image') === '1';

        if (structuredShell) structuredShell.classList.toggle('hidden', !isStructured);
        if (classicContentWrap) classicContentWrap.classList.toggle('hidden', isStructured);

        if (contentImagePanel) contentImagePanel.classList.toggle('hidden', !isImage);
        if (contentImageInput) contentImageInput.disabled = !isImage;

        if (contentFieldLabel) {
            if (isStructured) contentFieldLabel.textContent = 'Sync note (auto-filled from blocks)';
            else if (isBeepCall) contentFieldLabel.textContent = 'Notes (optional)';
            else contentFieldLabel.textContent = isImage ? 'Caption (optional)' : 'Message body *';
            contentFieldLabel.classList.toggle('hidden', isStructured);
        }

        if (contentField) {
            if (isStructured) {
                contentField.required = false;
                contentField.classList.add('hidden');
            } else {
                contentField.classList.remove('hidden');
                contentField.required = !isBeepCall && val === '{{ \App\Models\Message::CONTENT_TYPE_TEXT }}';
            }
        }

        if (contentDocumentInput) {
            contentDocumentInput.disabled = !isStructured;
        }

        if (staging) {
            staging.querySelectorAll('input[type="file"]').forEach(function(el) {
                el.disabled = !isStructured;
            });
        }

        var mustUpload = false;
        if (isImage) {
            var removed = removeContentImage && removeContentImage.checked;
            if (!hasExisting && !removed) mustUpload = true;
            if (hasExisting && removed) mustUpload = true;
        }
        if (contentImageInput) contentImageInput.required = !!mustUpload;

        syncContentTypeLabelRadios();
        if (typeof window.ensurePlainTextClassicEditor === 'function') {
            window.ensurePlainTextClassicEditor();
        }
    }

    function syncContentTypeLabelRadios() {
        const sel = document.querySelector('input[name="content_type"]:checked');
        document.querySelectorAll('.content-type-label').forEach(function(label) {
            const inp = label.querySelector('input[type="radio"]');
            var on = !!(sel && inp === sel);
            label.classList.toggle('border-coral', on);
            label.classList.toggle('bg-coral/10', on);
            label.classList.toggle('border-dawn-2', !on);
        });
    }

    function syncEmailSenderPanels(isEmailChannel) {
        const typeRadio = document.querySelector('input[name="email_sender_type"]:checked');
        var useCustom = typeRadio && typeRadio.value === 'custom';
        if (senderSavedPanel) senderSavedPanel.classList.toggle('hidden', useCustom || !isEmailChannel);
        if (senderCustomPanel) senderCustomPanel.classList.toggle('hidden', !useCustom || !isEmailChannel);
        document.querySelectorAll('.email-sender-type').forEach(function (radio) {
            radio.disabled = !isEmailChannel;
        });
        if (identitySelect) identitySelect.disabled = !isEmailChannel || useCustom;
        if (senderCustomNameInput) senderCustomNameInput.disabled = !isEmailChannel || !useCustom;
        if (senderCustomEmailInput) senderCustomEmailInput.disabled = !isEmailChannel || !useCustom;
    }

    function updateFields() {
        const checked = document.querySelector('input[name="channel_id"]:checked');
        document.querySelectorAll('.channel-label').forEach(l => {
            l.classList.remove('border-coral', 'bg-coral/10');
            l.classList.add('border-dawn-2');
        });
        if (checked) {
            const label = checked.closest('.channel-label');
            if (label) {
                label.classList.remove('border-dawn-2');
                label.classList.add('border-coral', 'bg-coral/10');
                subjectField.classList.toggle('hidden', label.dataset.supportsSubject !== '1');
                audioField.classList.toggle('hidden', label.dataset.supportsAudio !== '1');
                attachmentField.classList.toggle('hidden', label.dataset.supportsAttachment !== '1');
                var isEmail = label.dataset.channelSlug === 'email';
                if (senderField) senderField.classList.toggle('hidden', !isEmail);
                syncEmailSenderPanels(isEmail);
                syncBeepCallPanels();
            }
        }
    }

    channelRadios.forEach(r => r.addEventListener('change', updateFields));
    document.querySelectorAll('.content-type-radio').forEach(function (radio) {
        radio.addEventListener('change', syncContentTypePanels);
    });
    if (removeContentImage) removeContentImage.addEventListener('change', syncContentTypePanels);
    document.querySelectorAll('.tag-insert-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tag = this.getAttribute('data-tag') || '';
            var active = window.__lastStructuredField;
            var ta = (active && document.body.contains(active)) ? active : document.getElementById('content');
            if (!ta) return;
            if (typeof ta.selectionStart === 'number') {
                const start = ta.selectionStart;
                const end = ta.selectionEnd;
                const v = ta.value;
                ta.value = v.slice(0, start) + tag + v.slice(end);
                ta.selectionStart = ta.selectionEnd = start + tag.length;
            } else {
                ta.value += tag;
            }
            ta.focus();
        });
    });

    document.querySelectorAll('.email-sender-type').forEach(function (radio) {
        radio.addEventListener('change', function () {
            const checkedChannel = document.querySelector('input[name="channel_id"]:checked');
            const lab = checkedChannel ? checkedChannel.closest('.channel-label') : null;
            syncEmailSenderPanels(lab ? lab.dataset.channelSlug === 'email' : false);
        });
    });
    updateFields();
    syncContentTypePanels();
    syncBeepCallPanels();

    if (contentForm) {
        contentForm.addEventListener('submit', function () {
            const typeRadio = document.querySelector('input[name="content_type"]:checked');
            const val = typeRadio ? typeRadio.value : '';
            if (val === '{{ \App\Models\Message::CONTENT_TYPE_STRUCTURED }}' && contentDocumentInput) {
                contentDocumentInput.disabled = false;
            }
        }, true);
    }

    document.getElementById('template-select')?.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (!opt.value) return;
        document.querySelector(`input[name="channel_id"][value="${opt.dataset.channel}"]`).checked = true;
        const textType = document.querySelector('input[name="content_type"][value="{{ \App\Models\Message::CONTENT_TYPE_TEXT }}"]');
        if (textType) textType.checked = true;
        updateFields();
        syncContentTypePanels();
        document.getElementById('subject').value = opt.dataset.subject || '';
        document.getElementById('content').value = opt.dataset.content || '';
    });
});
</script>
@endpush
