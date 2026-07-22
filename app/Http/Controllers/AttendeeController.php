<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkImportAttendeeRequest;
use App\Http\Requests\ImportAttendeeCsvRequest;
use App\Http\Requests\StoreAttendeeRequest;
use App\Http\Requests\UpdateAttendeeRequest;
use App\Models\Attendee;
use App\Models\Contact;
use App\Models\Event;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendeeController extends Controller
{
    public function __construct(
        protected ContactService $contactService
    ) {}

    public function index(Event $event): View
    {
        $this->authorize('viewAny', [Attendee::class, $event]);

        $attendees = $event->attendees()
            ->with('contact')
            ->join('contacts', 'attendees.contact_id', '=', 'contacts.id')
            ->orderBy('contacts.name')
            ->select('attendees.*')
            ->paginate(20);

        return view('attendees.index', [
            'event' => $event,
            'attendees' => $attendees,
        ]);
    }

    public function store(StoreAttendeeRequest $request, Event $event): RedirectResponse
    {
        $this->contactService->assignToEvent($event, $request->contactAttributes());

        return redirect()->route('events.attendees.index', $event)
            ->with('status', 'Attendee added successfully.');
    }

    public function update(UpdateAttendeeRequest $request, Event $event, Attendee $attendee): RedirectResponse
    {
        if ($attendee->event_id !== $event->id) {
            abort(404);
        }

        $attributes = $request->contactAttributes();
        $this->contactService->updateContact($attendee->contact, [
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'phone' => $attributes['phone'],
            'company' => $attributes['company'],
        ]);

        $attendee->update(['rsvp_status' => $attributes['rsvp_status']]);

        return redirect()->route('events.attendees.index', $event)
            ->with('status', 'Attendee updated successfully.');
    }

    public function destroy(Event $event, Attendee $attendee): RedirectResponse
    {
        $this->authorize('delete', $attendee);

        if ($attendee->event_id !== $event->id) {
            abort(404);
        }

        $attendee->delete();

        return redirect()->route('events.attendees.index', $event)
            ->with('status', 'Attendee removed.');
    }

    public function importCsv(ImportAttendeeCsvRequest $request, Event $event): RedirectResponse
    {
        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        if (empty($rows)) {
            throw ValidationException::withMessages(['csv_file' => 'The CSV file is empty.']);
        }

        $headerRow = array_map('trim', $rows[0]);
        $nameCol = $this->findColumn($headerRow, ['name', 'full name', 'fullname']);
        $emailCol = $this->findColumn($headerRow, ['email', 'e-mail']);
        $phoneCol = $this->findColumn($headerRow, ['phone', 'mobile', 'tel']);
        $orgCol = $this->findColumn($headerRow, ['organization', 'org', 'company']);

        if ($nameCol === null || $emailCol === null) {
            throw ValidationException::withMessages([
                'csv_file' => 'CSV must have "name" and "email" columns (case-insensitive).',
            ]);
        }

        $pendingRows = [];
        $seenEmails = [];

        foreach (array_slice($rows, 1) as $i => $row) {
            $data = array_combine($headerRow, array_pad($row, count($headerRow), ''));
            $name = trim($data[$nameCol] ?? '');
            $email = trim($data[$emailCol] ?? '');

            if (empty($name) || empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $emailKey = strtolower($email);
            if (in_array($emailKey, $seenEmails, true)) {
                continue;
            }

            $seenEmails[] = $emailKey;
            $pendingRows[] = [
                'name' => $name,
                'email' => $email,
                'phone' => $phoneCol ? trim($data[$phoneCol] ?? '') : null,
                'company' => $orgCol ? trim($data[$orgCol] ?? '') : null,
            ];
        }

        $result = $this->contactService->bulkAssignToEvent($event, $pendingRows);

        $message = $result['imported'].' attendee(s) imported.';
        if ($result['skipped'] > 0) {
            $message .= ' '.$result['skipped'].' row(s) skipped.';
        }

        return redirect()->route('events.attendees.index', $event)
            ->with('status', $message);
    }

    public function bulkImport(BulkImportAttendeeRequest $request, Event $event): RedirectResponse
    {
        $validated = $request->validated();
        $lines = array_filter(array_map('trim', explode("\n", $validated['bulk_data'])));
        $pendingRows = [];
        $seenEmails = [];

        foreach ($lines as $line) {
            $parts = array_map('trim', str_getcsv($line));
            if (count($parts) < 2) {
                continue;
            }

            $name = $parts[0];
            $email = $parts[1];
            if (empty($name) || empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $emailKey = strtolower($email);
            if (in_array($emailKey, $seenEmails, true)) {
                continue;
            }

            $seenEmails[] = $emailKey;
            $pendingRows[] = [
                'name' => $name,
                'email' => $email,
                'phone' => $parts[2] ?? null,
                'company' => $parts[3] ?? null,
            ];
        }

        $result = $this->contactService->bulkAssignToEvent($event, $pendingRows);

        return redirect()->route('events.attendees.index', $event)
            ->with('status', $result['imported'].' attendee(s) imported.');
    }

    private function findColumn(array $headerRow, array $names): ?string
    {
        foreach ($headerRow as $h) {
            $hLower = strtolower(trim($h));
            foreach ($names as $name) {
                if ($hLower === strtolower($name)) {
                    return $h;
                }
            }
        }

        return null;
    }
}

