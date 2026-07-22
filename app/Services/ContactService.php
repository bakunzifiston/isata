<?php

namespace App\Services;

use App\Models\Attendee;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationUsage;
use Illuminate\Validation\ValidationException;

class ContactService
{
    public function __construct(
        protected UsageLimitService $usageLimitService
    ) {}

    /**
     * @param  array{name: string, email: string, phone?: ?string, company?: ?string}  $attributes
     */
    public function findOrCreate(Organization $organization, array $attributes): Contact
    {
        $email = strtolower(trim($attributes['email']));

        return Contact::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'email' => $email,
            ],
            [
                'name' => trim($attributes['name']),
                'phone' => filled($attributes['phone'] ?? null) ? trim((string) $attributes['phone']) : null,
                'company' => filled($attributes['company'] ?? null) ? trim((string) $attributes['company']) : null,
            ]
        );
    }

    /**
     * @param  array{name: string, email: string, phone?: ?string, company?: ?string, rsvp_status?: string}  $attributes
     */
    public function assignToEvent(Event $event, array $attributes): Attendee
    {
        $organization = $event->organization;
        $email = strtolower(trim($attributes['email']));

        $existingContact = Contact::query()
            ->where('organization_id', $organization->id)
            ->where('email', $email)
            ->first();

        if ($existingContact && $event->attendees()->where('contact_id', $existingContact->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This contact is already on the event guest list.'],
            ]);
        }

        if (! $existingContact) {
            $this->usageLimitService->assertCanAddContacts($organization, 1);
        }

        $contact = $this->findOrCreate($organization, $attributes);

        if ($contact->wasRecentlyCreated) {
            $this->incrementContactsUsage($organization->id);
        } elseif (! $contact->wasRecentlyCreated) {
            $contact->fill([
                'name' => trim($attributes['name']),
                'phone' => filled($attributes['phone'] ?? null) ? trim((string) $attributes['phone']) : $contact->phone,
                'company' => filled($attributes['company'] ?? null) ? trim((string) $attributes['company']) : $contact->company,
            ])->save();
        }

        return $event->attendees()->create([
            'contact_id' => $contact->id,
            'rsvp_status' => $attributes['rsvp_status'] ?? Attendee::RSVP_PENDING,
        ]);
    }

    /**
     * @param  list<array{name: string, email: string, phone?: ?string, company?: ?string}>  $rows
     * @return array{imported: int, skipped: int}
     */
    public function bulkAssignToEvent(Event $event, array $rows): array
    {
        $organization = $event->organization;
        $existingEmails = Contact::query()
            ->where('organization_id', $organization->id)
            ->pluck('id', 'email')
            ->all();

        $assignedContactIds = $event->attendees()->pluck('contact_id')->all();
        $newContactCount = 0;
        $importable = [];

        foreach ($rows as $row) {
            $email = strtolower(trim($row['email']));
            $contactId = $existingEmails[$email] ?? null;

            if ($contactId && in_array($contactId, $assignedContactIds, true)) {
                continue;
            }

            if (! $contactId) {
                $newContactCount++;
            }

            $importable[] = $row;
        }

        $this->usageLimitService->assertCanAddContacts($organization, $newContactCount, 'bulk_data');

        $imported = 0;

        foreach ($importable as $row) {
            $contact = $this->findOrCreate($organization, $row);

            if ($event->attendees()->where('contact_id', $contact->id)->exists()) {
                continue;
            }

            if ($contact->wasRecentlyCreated) {
                $this->incrementContactsUsage($organization->id);
            }

            $event->attendees()->create([
                'contact_id' => $contact->id,
                'rsvp_status' => Attendee::RSVP_PENDING,
            ]);
            $imported++;
        }

        return ['imported' => $imported, 'skipped' => count($rows) - $imported];
    }

    public function createForOrganization(Organization $organization, array $attributes): Contact
    {
        $this->usageLimitService->assertCanAddContacts($organization, 1);

        $contact = Contact::create([
            'organization_id' => $organization->id,
            'name' => trim($attributes['name']),
            'email' => strtolower(trim($attributes['email'])),
            'phone' => filled($attributes['phone'] ?? null) ? trim((string) $attributes['phone']) : null,
            'company' => filled($attributes['company'] ?? null) ? trim((string) $attributes['company']) : null,
        ]);

        $this->incrementContactsUsage($organization->id);

        return $contact;
    }

    public function updateContact(Contact $contact, array $attributes): Contact
    {
        $contact->update([
            'name' => trim($attributes['name']),
            'email' => strtolower(trim($attributes['email'])),
            'phone' => filled($attributes['phone'] ?? null) ? trim((string) $attributes['phone']) : null,
            'company' => filled($attributes['company'] ?? null) ? trim((string) $attributes['company']) : null,
        ]);

        return $contact->fresh();
    }

    private function incrementContactsUsage(int $organizationId, int $count = 1): void
    {
        $period = now()->format('Y-m');
        $usage = OrganizationUsage::getOrCreateForPeriod($organizationId, $period);
        $usage->increment('contacts_count', $count);
    }
}
