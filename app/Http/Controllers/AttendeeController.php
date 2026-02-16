<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\OrganizationUsage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendeeController extends Controller
{
    public function index(Event $event): View
    {
        $this->authorizeEvent($event);

        $attendees = $event->attendees()->orderBy('name')->paginate(20);

        return view('attendees.index', [
            'event' => $event,
            'attendees' => $attendees,
        ]);
    }

    public function store(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'organization' => ['nullable', 'string', 'max:255'],
            'rsvp_status' => ['required', 'in:pending,confirmed,declined,attended'],
        ]);

        $event->attendees()->create($validated);
        $this->incrementContactsUsage($event->organization_id);

        return redirect()->route('events.attendees.index', $event)
            ->with('status', 'Attendee added successfully.');
    }

    public function update(Request $request, Event $event, Attendee $attendee): RedirectResponse
    {
        $this->authorizeEvent($event);

        if ($attendee->event_id !== $event->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'organization' => ['nullable', 'string', 'max:255'],
            'rsvp_status' => ['required', 'in:pending,confirmed,declined,attended'],
        ]);

        $attendee->update($validated);

        return redirect()->route('events.attendees.index', $event)
            ->with('status', 'Attendee updated successfully.');
    }

    public function destroy(Event $event, Attendee $attendee): RedirectResponse
    {
        $this->authorizeEvent($event);

        if ($attendee->event_id !== $event->id) {
            abort(404);
        }

        $attendee->delete();

        return redirect()->route('events.attendees.index', $event)
            ->with('status', 'Attendee removed.');
    }

    public function importCsv(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        if (empty($rows)) {
            throw ValidationException::withMessages(['csv_file' => 'The CSV file is empty.']);
        }

        $headerRow = array_map('trim', $rows[0]);
        $header = array_combine($headerRow, $headerRow);
        $nameCol = $this->findColumn($headerRow, ['name', 'full name', 'fullname']);
        $emailCol = $this->findColumn($headerRow, ['email', 'e-mail']);
        $phoneCol = $this->findColumn($headerRow, ['phone', 'mobile', 'tel']);
        $orgCol = $this->findColumn($headerRow, ['organization', 'org', 'company']);

        if ($nameCol === null || $emailCol === null) {
            throw ValidationException::withMessages([
                'csv_file' => 'CSV must have "name" and "email" columns (case-insensitive).',
            ]);
        }

        $imported = 0;
        $errors = [];

        DB::transaction(function () use ($rows, $headerRow, $nameCol, $emailCol, $phoneCol, $orgCol, $event, &$imported, &$errors) {
            foreach (array_slice($rows, 1) as $i => $row) {
                $data = array_combine($headerRow, array_pad($row, count($headerRow), ''));
                $name = trim($data[$nameCol] ?? '');
                $email = trim($data[$emailCol] ?? '');

                if (empty($name) || empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row " . ($i + 2) . ": Invalid name or email";
                    continue;
                }

                $event->attendees()->create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phoneCol ? trim($data[$phoneCol] ?? '') : null,
                    'organization' => $orgCol ? trim($data[$orgCol] ?? '') : null,
                    'rsvp_status' => Attendee::RSVP_PENDING,
                ]);
                $imported++;
            }
        });

        $this->incrementContactsUsage($event->organization_id, $imported);

        $message = $imported . ' attendee(s) imported.';
        if (! empty($errors)) {
            $message .= ' ' . count($errors) . ' row(s) skipped.';
        }

        return redirect()->route('events.attendees.index', $event)
            ->with('status', $message);
    }

    public function bulkImport(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'bulk_data' => ['required', 'string', 'max:10000'],
        ]);

        $lines = array_filter(array_map('trim', explode("\n", $validated['bulk_data'])));
        $imported = 0;

        foreach ($lines as $line) {
            $parts = array_map('trim', str_getcsv($line));
            if (count($parts) < 2) {
                continue;
            }
            $name = $parts[0];
            $email = $parts[1];
            $phone = $parts[2] ?? null;
            $organization = $parts[3] ?? null;

            if (empty($name) || empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $event->attendees()->create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone ?: null,
                'organization' => $organization ?: null,
                'rsvp_status' => Attendee::RSVP_PENDING,
            ]);
            $imported++;
        }

        $this->incrementContactsUsage($event->organization_id, $imported);

        return redirect()->route('events.attendees.index', $event)
            ->with('status', $imported . ' attendee(s) imported.');
    }

    private function authorizeEvent(Event $event): void
    {
        $organization = auth()->user()->organization;
        if (! $organization || $event->organization_id !== $organization->id) {
            abort(404);
        }
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

    private function incrementContactsUsage(int $organizationId, int $count = 1): void
    {
        $period = now()->format('Y-m');
        $usage = OrganizationUsage::getOrCreateForPeriod($organizationId, $period);
        $usage->increment('contacts_count', $count);
    }
}
