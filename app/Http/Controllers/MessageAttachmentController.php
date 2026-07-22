<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageAttachmentController extends Controller
{
    /**
     * Serve message attachments from the private disk to authenticated org users.
     * Attachments may contain PII (CSV, documents) and must not be on the public disk.
     */
    public function download(Request $request, Message $message): StreamedResponse
    {
        $organization = $request->user()?->organization;

        if (! $organization || $message->event->organization_id !== $organization->id) {
            abort(404);
        }

        if (! $message->attachment_file) {
            abort(404);
        }

        $disk = $this->resolveAttachmentDisk($message->attachment_file);

        if (! Storage::disk($disk)->exists($message->attachment_file)) {
            abort(404);
        }

        return Storage::disk($disk)->download(
            $message->attachment_file,
            basename($message->attachment_file)
        );
    }

    /**
     * Legacy attachments may still exist on the public disk from before Phase 0.
     */
    protected function resolveAttachmentDisk(string $path): string
    {
        if (Storage::disk('local')->exists($path)) {
            return 'local';
        }

        return 'public';
    }
}
