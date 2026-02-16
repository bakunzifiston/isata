<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function show(Feedback $feedback): View|Response
    {
        $organization = auth()->user()->organization;

        if (! $organization || $feedback->event->organization_id !== $organization->id) {
            abort(404);
        }

        return view('certificates.show', [
            'feedback' => $feedback,
            'event' => $feedback->event,
            'attendee' => $feedback->attendee,
        ]);
    }

    public function publicShow(Feedback $feedback, string $token): View
    {
        if (! hash_equals($token, md5($feedback->id . config('app.key')))) {
            abort(403);
        }

        return view('certificates.show', [
            'feedback' => $feedback,
            'event' => $feedback->event,
            'attendee' => $feedback->attendee,
        ]);
    }
}
