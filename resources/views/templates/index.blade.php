@extends('layouts.dashboard')

@section('title', 'Message Templates - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="admin-page-title">Message templates</h1>
        <p class="admin-page-subtitle">Reusable templates with personalization tags</p>
    </div>
    <a href="{{ route('templates.create') }}" class="admin-btn-primary">
        Create template
    </a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Channel</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink/50">Subject</th>
                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink/50">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($templates as $template)
            <tr>
                <td class="px-6 py-4 font-medium text-ink">{{ $template->name }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-dawn-2 text-ink/70">
                        {{ $template->channel->name }}
                    </span>
                </td>
                <td class="px-6 py-4 text-ink/65">{{ Str::limit($template->subject, 40) ?: '—' }}</td>
                <td class="px-6 py-4 text-right text-sm">
                    <a href="{{ route('templates.edit', $template) }}" class="admin-link">Edit</a>
                    <form method="POST" action="{{ route('templates.destroy', $template) }}" class="inline ml-4" onsubmit="return confirm('Delete this template?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-ink/50">
                    No templates yet. <a href="{{ route('templates.create') }}" class="admin-link">Create your first template</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
