@extends('layouts.dashboard')

@section('title', 'Messages - ' . $event->name . ' - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <a href="{{ route('events.show', $event) }}" class="text-slate-600 hover:text-slate-900">← {{ $event->name }}</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-2">Messages</h1>
        <p class="mt-1 text-slate-600">Send communications via Email, SMS, Beep Call, or Social Media</p>
    </div>
    <a href="{{ route('events.messages.create', $event) }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
        Create message
    </a>
</div>

<div class="mb-6">
    <a href="{{ route('queue.monitor') }}" class="text-sm text-slate-600 hover:text-slate-900">Queue status monitor →</a>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Channel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Subject / Preview</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Scheduled</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($messages as $message)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-800">
                        {{ $message->channel->name }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <p class="font-medium text-slate-900">{{ $message->subject ?: '—' }}</p>
                    <p class="text-sm text-slate-500 truncate max-w-xs">{{ Str::limit(strip_tags($message->content), 60) }}</p>
                </td>
                <td class="px-6 py-4 text-sm text-slate-600">{{ $message->scheduled_at?->format('M j, Y H:i') ?? '—' }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                        {{ $message->status === 'draft' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $message->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $message->status === 'queued' ? 'bg-slate-100 text-slate-800' : '' }}
                        {{ $message->status === 'sent' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $message->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}
                    ">{{ ucfirst($message->status) }}</span>
                </td>
                <td class="px-6 py-4 text-right text-sm space-x-2">
                    @if(in_array($message->status, ['draft', 'scheduled', 'queued']) && $message->status !== 'sent')
                    <form method="POST" action="{{ route('events.messages.send-now', [$event, $message]) }}" class="inline">
                        @csrf
                        <button type="submit" class="text-indigo-600 hover:text-indigo-900">Send now</button>
                    </form>
                    @endif
                    <a href="{{ route('events.messages.edit', [$event, $message]) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    <form method="POST" action="{{ route('events.messages.destroy', [$event, $message]) }}" class="inline" onsubmit="return confirm('Delete this message?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                    No messages yet. <a href="{{ route('events.messages.create', $event) }}" class="text-indigo-600 hover:text-indigo-900">Create your first message</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($messages->hasPages())
    <div class="px-6 py-4 border-t border-slate-200">{{ $messages->links() }}</div>
    @endif
</div>

<a href="{{ route('templates.index') }}" class="inline-block mt-6 text-sm text-slate-600 hover:text-slate-900">Manage templates →</a>
@endsection
