<?php

namespace Tests\Feature;

use App\Jobs\SendMessageJob;
use App\Models\Attendee;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Message;
use App\Models\Organization;
use App\Models\OrganizationUsage;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageLimitEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function seedFreemiumOrg(): array
    {
        $plan = SubscriptionPlan::updateOrCreate(
            ['slug' => SubscriptionPlan::SLUG_FREEMIUM],
            [
                'name' => 'Freemium',
                'price' => 0,
                'interval' => 'monthly',
                'limits' => ['events_per_month' => 1, 'contacts' => 2, 'beep_calls' => false],
            ]
        );

        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org', 'subscription_plan_id' => $plan->id]);
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $org->id,
            'role' => User::ROLE_ADMIN,
        ]);

        return compact('plan', 'org', 'user');
    }

    protected function createAttendee(Event $event, string $email = 'guest@example.com'): Attendee
    {
        $contact = Contact::create([
            'organization_id' => $event->organization_id,
            'name' => 'Guest',
            'email' => $email,
        ]);

        return Attendee::create([
            'event_id' => $event->id,
            'contact_id' => $contact->id,
            'rsvp_status' => Attendee::RSVP_PENDING,
        ]);
    }

    public function test_blocks_scheduling_event_when_monthly_limit_reached(): void
    {
        ['org' => $org, 'user' => $user] = $this->seedFreemiumOrg();

        OrganizationUsage::getOrCreateForPeriod($org->id, now()->format('Y-m'))
            ->update(['events_count' => 1]);

        $response = $this->actingAs($user)->post(route('events.store'), [
            'name' => 'Second Event',
            'date' => now()->addWeek()->toDateString(),
            'event_format' => 'physical',
            'venue' => 'Hall',
            'status' => 'scheduled',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseCount('events', 0);
    }

    public function test_blocks_adding_attendee_when_contact_limit_reached(): void
    {
        ['org' => $org, 'user' => $user] = $this->seedFreemiumOrg();
        $event = Event::create([
            'organization_id' => $org->id,
            'name' => 'Event',
            'date' => now()->addWeek()->toDateString(),
            'status' => 'scheduled',
        ]);

        OrganizationUsage::getOrCreateForPeriod($org->id, now()->format('Y-m'))
            ->update(['contacts_count' => 2]);

        $response = $this->actingAs($user)->post(route('events.attendees.store', $event), [
            'name' => 'Extra Guest',
            'email' => 'extra@example.com',
            'rsvp_status' => 'pending',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('attendees', 0);
    }

    public function test_rejects_duplicate_attendee_email_on_same_event(): void
    {
        ['org' => $org, 'user' => $user] = $this->seedFreemiumOrg();
        $event = Event::create([
            'organization_id' => $org->id,
            'name' => 'Event',
            'date' => now()->addWeek()->toDateString(),
            'status' => 'scheduled',
        ]);

        $this->createAttendee($event);

        $response = $this->actingAs($user)->post(route('events.attendees.store', $event), [
            'name' => 'Another',
            'email' => 'guest@example.com',
            'rsvp_status' => 'pending',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('attendees', 1);
    }
}
