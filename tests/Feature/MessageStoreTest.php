<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Event;
use App\Models\Message;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessageStoreTest extends TestCase
{
    use RefreshDatabase;

    private function actingOrgUser(): array
    {
        $org = Organization::create(['name' => 'Test Org', 'slug' => 'test-org-'.Str::random(6)]);
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-'.Str::random(6).'@example.com',
            'password' => bcrypt('password'),
            'organization_id' => $org->id,
            'role' => User::ROLE_ADMIN,
        ]);
        $event = Event::create([
            'organization_id' => $org->id,
            'name' => 'Town hall',
            'date' => now()->addWeek()->toDateString(),
            'status' => 'scheduled',
        ]);

        return [$user, $event];
    }

    private function smsChannel(): Channel
    {
        return Channel::firstOrCreate(
            ['slug' => Channel::SLUG_SMS],
            ['name' => 'SMS', 'supports_subject' => false, 'supports_audio' => false, 'supports_attachment' => true]
        );
    }

    /** @return array{v: int, blocks: array<int, mixed>} */
    private function sampleStructuredDocument(): array
    {
        return [
            'v' => 1,
            'blocks' => [[
                'id' => (string) Str::uuid(),
                'type' => 'section',
                'variant' => 'muted',
                'title' => 'Main message',
                'blocks' => [
                    ['id' => (string) Str::uuid(), 'type' => 'heading', 'level' => 2, 'text' => "You're invited"],
                    ['id' => (string) Str::uuid(), 'type' => 'paragraph', 'text' => 'Hi {name}, see you at {event_name}.'],
                ],
            ]],
        ];
    }

    public function test_plain_text_message_requires_body(): void
    {
        [$user, $event] = $this->actingOrgUser();
        $sms = $this->smsChannel();

        $this->actingAs($user)
            ->post(route('events.messages.store', $event), [
                'channel_id' => $sms->id,
                'content_type' => Message::CONTENT_TYPE_TEXT,
                'content' => '',
                'status' => Message::STATUS_DRAFT,
            ])
            ->assertSessionHasErrors([
                'content' => 'Add a message body in Plain text mode, or switch to Structured layout.',
            ]);
    }

    public function test_structured_message_can_be_saved_without_plain_text_body(): void
    {
        [$user, $event] = $this->actingOrgUser();
        $sms = $this->smsChannel();
        $document = $this->sampleStructuredDocument();

        $this->actingAs($user)
            ->post(route('events.messages.store', $event), [
                'channel_id' => $sms->id,
                'content_type' => Message::CONTENT_TYPE_STRUCTURED,
                'content_document' => json_encode($document),
                'status' => Message::STATUS_DRAFT,
            ])
            ->assertRedirect(route('events.messages.index', $event));

        $message = Message::query()->where('event_id', $event->id)->first();
        $this->assertNotNull($message);
        $this->assertSame(Message::CONTENT_TYPE_STRUCTURED, $message->content_type);
        $this->assertIsArray($message->content_document);
        $this->assertStringContainsString('invited', strtolower($message->content ?? ''));
    }

    public function test_beep_call_message_does_not_require_text_body(): void
    {
        [$user, $event] = $this->actingOrgUser();
        $beep = Channel::firstOrCreate(
            ['slug' => Channel::SLUG_BEEP_CALL],
            ['name' => 'Beep Call', 'supports_subject' => false, 'supports_audio' => true, 'supports_attachment' => false]
        );

        $audio = \Illuminate\Http\UploadedFile::fake()->create('reminder.mp3', 100, 'audio/mpeg');

        $this->actingAs($user)
            ->post(route('events.messages.store', $event), [
                'channel_id' => $beep->id,
                'content_type' => Message::CONTENT_TYPE_TEXT,
                'content' => '',
                'audio_file' => $audio,
                'status' => Message::STATUS_DRAFT,
            ])
            ->assertRedirect(route('events.messages.index', $event));

        $message = Message::query()->where('event_id', $event->id)->first();
        $this->assertNotNull($message);
        $this->assertSame(Message::CONTENT_TYPE_TEXT, $message->content_type);
        $this->assertSame('Voice reminder', $message->content);
        $this->assertNotNull($message->audio_file);
    }
}
