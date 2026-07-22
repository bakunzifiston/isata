@extends('layouts.dashboard')

@section('title', 'Create Event - ' . config('app.name'))

@section('content')
<div class="mb-8">
    <h1 class="admin-page-title">Create event</h1>
    <p class="admin-page-subtitle">Four-step wizard: details, guests, first message, and review.</p>
</div>

<x-admin.card class="max-w-3xl" x-data="eventWizard()">
    <div class="mb-8 flex items-center gap-2 text-sm">
        <template x-for="(label, index) in ['Details', 'Guests', 'Message', 'Review']" :key="label">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full font-semibold"
                    :class="step === index + 1 ? 'bg-coral text-white' : (step > index + 1 ? 'bg-coral/15 text-coral' : 'bg-dawn-2 text-ink/50')"
                    x-text="index + 1"></span>
                <span class="hidden sm:inline" :class="step === index + 1 ? 'font-medium text-ink' : 'text-ink/50'" x-text="label"></span>
                <span x-show="index < 3" class="hidden text-ink/30 sm:inline">→</span>
            </div>
        </template>
    </div>

    <form method="POST" action="{{ route('events.wizard.store') }}" @submit="prepareSubmit">
        @csrf

        <div x-show="step === 1" x-cloak>
            @include('events._form-fields', ['event' => $event, 'showActions' => false])
        </div>

        <div x-show="step === 2" x-cloak class="space-y-4">
            <p class="text-sm text-ink/65">Paste guests one per line: <code class="rounded bg-dawn-2 px-1 text-xs">Name, email, phone, company</code>. Leave blank to skip.</p>
            <textarea name="guests_bulk" rows="8" x-model="guestsBulk"
                class="admin-input font-mono text-sm"
                placeholder="Jane Doe, jane@example.com&#10;John Smith, john@example.com, +15551234567, Acme Inc"></textarea>
            @error('guests_bulk')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div x-show="step === 3" x-cloak class="space-y-4">
            <label class="flex items-center gap-2 text-sm text-ink/75">
                <input type="checkbox" name="skip_message" value="1" x-model="skipMessage" class="rounded border-dawn-2 text-coral focus:ring-coral/20">
                Skip first message for now
            </label>
            <div x-show="!skipMessage" class="space-y-4">
                <div>
                    <label for="message_channel_id" class="admin-label mb-1">Channel</label>
                    <select name="message_channel_id" id="message_channel_id" x-model="messageChannelId" class="admin-input">
                        <option value="">Select channel</option>
                        @foreach($channels as $channel)
                            <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="message_content" class="admin-label mb-1">Message content</label>
                    <textarea name="message_content" id="message_content" rows="5" x-model="messageContent"
                        class="admin-input"
                        placeholder="Hello {name}, you're invited to {event_name}..."></textarea>
                </div>
                <div>
                    <label for="message_status" class="admin-label mb-1">Message status</label>
                    <select name="message_status" id="message_status" class="admin-input">
                        <option value="draft">Save as draft</option>
                        <option value="scheduled">Schedule for sending</option>
                    </select>
                </div>
            </div>
        </div>

        <div x-show="step === 4" x-cloak class="space-y-4 text-sm text-ink/75">
            <p class="font-medium text-ink">Review your event</p>
            <ul class="list-disc space-y-1 pl-5">
                <li>Event details from step 1 will be saved with your chosen status.</li>
                <li x-text="guestsBulk.trim() ? 'Guests from step 2 will be imported.' : 'No guests will be added yet.'"></li>
                <li x-text="skipMessage ? 'No message will be created.' : 'A first message will be created with your channel and content.'"></li>
            </ul>
        </div>

        <div class="mt-8 flex justify-between border-t border-dawn-2/80 pt-6">
            <button type="button" x-show="step > 1" @click="step--"
                class="admin-btn-secondary">Back</button>
            <div class="ml-auto flex gap-3">
                <button type="button" x-show="step < 4" @click="nextStep()"
                    class="admin-btn-primary">Continue</button>
                <button type="submit" x-show="step === 4"
                    class="admin-btn-primary">Create event</button>
            </div>
        </div>
    </form>
</x-admin.card>

<script>
function eventWizard() {
    return {
        step: 1,
        guestsBulk: @json(old('guests_bulk', '')),
        skipMessage: @json((bool) old('skip_message', false)),
        messageChannelId: @json(old('message_channel_id', '')),
        messageContent: @json(old('message_content', '')),
        nextStep() {
            if (this.step < 4) this.step++;
        },
        prepareSubmit() {},
    };
}
</script>
@endsection
