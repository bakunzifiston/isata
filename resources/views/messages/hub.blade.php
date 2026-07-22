@extends('layouts.dashboard')

@section('title', 'Messages · ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="admin-page-title">Messages</h1>
        <p class="admin-page-subtitle">All messages for events in your organization</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if($quickCreateEvent)
        <a href="{{ route('events.messages.create', $quickCreateEvent) }}" class="admin-btn-primary">
            Create message
        </a>
        @endif
        <a href="{{ route('events.index') }}" class="admin-btn-secondary">
            {{ $quickCreateEvent ? 'Browse events' : 'Choose event to send' }}
        </a>
    </div>
</div>

<div class="mb-6">
    <a href="{{ route('queue.monitor') }}" class="admin-link-muted text-sm">Queue status monitor →</a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Event</th>
                <th>Channel</th>
                <th>Subject / Preview</th>
                <th>Scheduled</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($messages as $message)
            <tr>
                <td>
                    <a href="{{ route('events.show', $message->event) }}" class="admin-link">{{ $message->event->name }}</a>
                </td>
                <td>
                    <span class="inline-flex rounded-full bg-dawn-2 px-2 py-1 text-xs font-medium text-ink/70">
                        {{ $message->channel->name }}
                    </span>
                </td>
                <td>
                    <p class="font-medium text-ink">{{ $message->subject ?: '—' }}</p>
                    <p class="max-w-xs truncate text-sm text-ink/50">
                        @if(($message->content_type ?? \App\Models\Message::CONTENT_TYPE_TEXT) === \App\Models\Message::CONTENT_TYPE_IMAGE)
                            <span class="font-medium text-coral">Image</span>@if(filled($message->content)) · {{ Str::limit(strip_tags($message->content), 50) }} @endif
                        @elseif(($message->content_type ?? '') === \App\Models\Message::CONTENT_TYPE_STRUCTURED)
                            <span class="font-medium text-coral">Structured layout</span> · {{ Str::limit(strip_tags($message->structuredPreview() ?? $message->content), 60) }}
                        @else
                            {{ Str::limit(strip_tags($message->content), 60) }}
                        @endif
                    </p>
                    @if($message->channel->slug === 'email' && $message->resolved_email_sender_line)
                    <p class="mt-1 text-xs text-ink/50">From {{ $message->resolved_email_sender_line }}</p>
                    @endif
                    @if($message->attachment_file)
                    <p class="mt-1 text-xs"><a href="{{ route('messages.attachment.download', $message) }}" class="admin-link hover:underline">Download attachment</a></p>
                    @endif
                </td>
                <td>{{ $message->scheduled_at?->format('M j, Y H:i') ?? '—' }}</td>
                <td>
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium
                        {{ $message->status === 'draft' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $message->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $message->status === 'queued' ? 'bg-dawn-2 text-ink/70' : '' }}
                        {{ $message->status === 'sent' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $message->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                    ">{{ ucfirst($message->status) }}</span>
                </td>
                <td class="space-x-2 text-right">
                    @if(in_array($message->status, ['draft', 'scheduled', 'queued']) && $message->status !== 'sent')
                    <form method="POST" action="{{ route('events.messages.send-now', [$message->event, $message]) }}" class="inline">
                        @csrf
                        <button type="submit" class="admin-link">Send now</button>
                    </form>
                    @endif
                    <a href="{{ route('events.messages.edit', [$message->event, $message]) }}" class="admin-link">Edit</a>
                    <form method="POST" action="{{ route('events.messages.destroy', [$message->event, $message]) }}" class="inline" onsubmit="return confirm('Delete this message?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="py-12 text-center text-ink/50">
                    No messages yet. Open an event to create one, or
                    @if($quickCreateEvent)
                    <a href="{{ route('events.messages.create', $quickCreateEvent) }}" class="admin-link">create a message</a>.
                    @else
                    <a href="{{ route('events.create') }}" class="admin-link">create an event</a> first.
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($messages->hasPages())
    <div class="border-t border-dawn-2/80 px-6 py-4">{{ $messages->links() }}</div>
    @endif
</div>

<a href="{{ route('templates.index') }}" class="admin-link-muted mt-6 inline-block text-sm">Manage templates →</a>
@endsection
