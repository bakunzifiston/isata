@extends('layouts.dashboard')

@section('title', 'Message Templates - ' . config('app.name'))

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Message templates</h1>
        <p class="mt-1 text-slate-600">Reusable templates with personalization tags</p>
    </div>
    <a href="{{ route('templates.create') }}" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700">
        Create template
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-slate-200">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Channel</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Subject</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @forelse($templates as $template)
            <tr class="hover:bg-slate-50">
                <td class="px-6 py-4 font-medium text-slate-900">{{ $template->name }}</td>
                <td class="px-6 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-800">
                        {{ $template->channel->name }}
                    </span>
                </td>
                <td class="px-6 py-4 text-slate-600">{{ Str::limit($template->subject, 40) ?: '—' }}</td>
                <td class="px-6 py-4 text-right text-sm">
                    <a href="{{ route('templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    <form method="POST" action="{{ route('templates.destroy', $template) }}" class="inline ml-4" onsubmit="return confirm('Delete this template?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                    No templates yet. <a href="{{ route('templates.create') }}" class="text-indigo-600 hover:text-indigo-900">Create your first template</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
