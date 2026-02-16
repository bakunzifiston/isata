<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\MessageTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageTemplateController extends Controller
{
    public function index(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $templates = $organization->messageTemplates()->with('channel')->orderBy('name')->paginate(15);

        return view('templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create(): View
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $channels = Channel::orderBy('name')->get();

        return view('templates.create', [
            'template' => new MessageTemplate(),
            'channels' => $channels,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization) {
            abort(403);
        }

        $channel = Channel::findOrFail($request->channel_id);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'channel_id' => ['required', 'exists:channels,id'],
            'content' => ['required', 'string', 'max:10000'],
        ];

        if ($channel->supports_subject) {
            $rules['subject'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $organization->messageTemplates()->create([
            'channel_id' => $validated['channel_id'],
            'name' => $validated['name'],
            'subject' => $validated['subject'] ?? null,
            'content' => $validated['content'],
        ]);

        return redirect()->route('templates.index')
            ->with('status', 'Template created.');
    }

    public function edit(MessageTemplate $template): View
    {
        $organization = auth()->user()->organization;

        if (! $organization || $template->organization_id !== $organization->id) {
            abort(404);
        }

        $channels = Channel::orderBy('name')->get();

        return view('templates.edit', [
            'template' => $template,
            'channels' => $channels,
        ]);
    }

    public function update(Request $request, MessageTemplate $template): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $template->organization_id !== $organization->id) {
            abort(403);
        }

        $channel = Channel::findOrFail($request->channel_id);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'channel_id' => ['required', 'exists:channels,id'],
            'content' => ['required', 'string', 'max:10000'],
        ];

        if ($channel->supports_subject) {
            $rules['subject'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        $template->update([
            'channel_id' => $validated['channel_id'],
            'name' => $validated['name'],
            'subject' => $validated['subject'] ?? null,
            'content' => $validated['content'],
        ]);

        return redirect()->route('templates.index')
            ->with('status', 'Template updated.');
    }

    public function destroy(MessageTemplate $template): RedirectResponse
    {
        $organization = auth()->user()->organization;

        if (! $organization || $template->organization_id !== $organization->id) {
            abort(403);
        }

        $template->delete();

        return redirect()->route('templates.index')
            ->with('status', 'Template deleted.');
    }
}
