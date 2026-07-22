<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RsvpLookupTest extends TestCase
{
    use RefreshDatabase;

    private function seedEventWithAttendee(?string $email = 'guest@example.com'): array
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org']);
        $event = Event::create([
            'organization_id' => $org->id,
            'name' => 'Test Event',
            'date' => now()->addWeek()->toDateString(),
            'status' => 'scheduled',
        ]);
        $contact = Contact::create([
            'organization_id' => $org->id,
            'name' => 'Guest',
            'email' => $email,
        ]);
        $attendee = Attendee::create([
            'event_id' => $event->id,
            'contact_id' => $contact->id,
            'rsvp_status' => 'pending',
        ]);

        return compact('org', 'event', 'attendee');
    }

    public function test_lookup_rejects_invalid_email(): void
    {
        ['event' => $event] = $this->seedEventWithAttendee();

        $response = $this->from(route('events.rsvp', $event))
            ->get(route('rsvp.lookup', [
                'event' => $event->id,
                'email' => 'not-an-email',
            ]));

        $response->assertSessionHasErrors('email');
    }

    public function test_lookup_finds_attendee_with_valid_email(): void
    {
        ['event' => $event, 'attendee' => $attendee] = $this->seedEventWithAttendee();

        $response = $this->get(route('rsvp.lookup', [
            'event' => $event->id,
            'email' => 'guest@example.com',
        ]));

        $response->assertRedirect();
        $this->assertStringContainsString(
            'rsvp/'.$event->id.'/'.$attendee->id,
            (string) $response->headers->get('Location')
        );
    }

    public function test_lookup_is_rate_limited(): void
    {
        ['event' => $event] = $this->seedEventWithAttendee();

        for ($i = 0; $i < 10; $i++) {
            $this->get(route('rsvp.lookup', [
                'event' => $event->id,
                'email' => 'person'.$i.'@example.com',
            ]));
        }

        $response = $this->get(route('rsvp.lookup', [
            'event' => $event->id,
            'email' => 'blocked@example.com',
        ]));

        $response->assertStatus(429);
    }
}
