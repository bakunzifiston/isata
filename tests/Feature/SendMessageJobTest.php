<?php

namespace Tests\Feature;

use App\Jobs\SendMessageJob;
use App\Models\Attendee;
use App\Models\Channel;
use App\Models\CommunicationLog;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Message;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendMessageJobTest extends TestCase
{
    use RefreshDatabase;

    protected function createAttendee(Event $event, array $overrides = []): Attendee
    {
        $contact = Contact::create([
            'organization_id' => $event->organization_id,
            'name' => $overrides['name'] ?? 'Guest',
            'email' => $overrides['email'] ?? 'guest@example.com',
            'phone' => $overrides['phone'] ?? null,
        ]);

        return Attendee::create([
            'event_id' => $event->id,
            'contact_id' => $contact->id,
            'rsvp_status' => 'pending',
        ]);
    }

    public function test_sms_skips_attendee_without_phone_and_logs_reason(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org']);
        $event = Event::create([
            'organization_id' => $org->id,
            'name' => 'Event',
            'date' => now()->addWeek()->toDateString(),
            'status' => 'scheduled',
        ]);

        $sms = Channel::firstOrCreate(
            ['slug' => Channel::SLUG_SMS],
            ['name' => 'SMS', 'supports_subject' => false, 'supports_audio' => false, 'supports_attachment' => true]
        );

        $attendee = $this->createAttendee($event, [
            'name' => 'No Phone',
            'email' => 'nophone@example.com',
        ]);

        $message = Message::create([
            'event_id' => $event->id,
            'channel_id' => $sms->id,
            'content' => 'Hello {name}',
            'content_type' => Message::CONTENT_TYPE_TEXT,
            'status' => Message::STATUS_SCHEDULED,
        ]);

        (new SendMessageJob($message))->handle();

        $log = CommunicationLog::first();
        $this->assertNotNull($log);
        $this->assertSame(CommunicationLog::STATUS_FAILED, $log->status);
        $this->assertSame('missing_contact_method', $log->metadata['reason'] ?? null);
        $this->assertSame($attendee->id, $log->attendee_id);
    }

    public function test_sms_sends_to_attendee_with_phone(): void
    {
        $org = Organization::create(['name' => 'Org', 'slug' => 'org-2']);
        $event = Event::create([
            'organization_id' => $org->id,
            'name' => 'Event',
            'date' => now()->addWeek()->toDateString(),
            'status' => 'scheduled',
        ]);

        $sms = Channel::firstOrCreate(
            ['slug' => Channel::SLUG_SMS],
            ['name' => 'SMS', 'supports_subject' => false, 'supports_audio' => false, 'supports_attachment' => true]
        );

        $this->createAttendee($event, [
            'name' => 'With Phone',
            'email' => 'phone@example.com',
            'phone' => '+15551234567',
        ]);

        $message = Message::create([
            'event_id' => $event->id,
            'channel_id' => $sms->id,
            'content' => 'Hello {name}',
            'content_type' => Message::CONTENT_TYPE_TEXT,
            'status' => Message::STATUS_SCHEDULED,
        ]);

        (new SendMessageJob($message))->handle();

        $this->assertSame(CommunicationLog::STATUS_DELIVERED, CommunicationLog::first()->status);
        $message->refresh();
        $this->assertSame(Message::STATUS_SENT, $message->status);
    }
}
