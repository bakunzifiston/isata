<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageTemplateRequest;
use App\Http\Requests\UpdateMessageTemplateRequest;
use App\Models\Channel;
use App\Models\MessageTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MessageTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MessageTemplate::class);

        $organization = auth()->user()->organization;

        if (! Schema::hasTable('message_templates')) {
            $templates = new LengthAwarePaginator(
                collect(),
                0,
                15,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return view('templates.index', ['templates' => $templates]);
        }

        $templates = $organization->messageTemplates()->with('channel')->orderBy('name')->paginate(15);

        return view('templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MessageTemplate::class);

        $channels = Channel::orderBy('name')->get();

        return view('templates.create', [
            'template' => new MessageTemplate,
            'channels' => $channels,
        ]);
    }

    public function store(StoreMessageTemplateRequest $request): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $validated = $request->validated();

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
        $this->authorize('update', $template);

        $channels = Channel::orderBy('name')->get();

        return view('templates.edit', [
            'template' => $template,
            'channels' => $channels,
        ]);
    }

    public function update(UpdateMessageTemplateRequest $request, MessageTemplate $template): RedirectResponse
    {
        $validated = $request->validated();

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
        $this->authorize('delete', $template);

        $template->delete();

        return redirect()->route('templates.index')
            ->with('status', 'Template deleted.');
    }
}
