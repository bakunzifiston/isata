<?php

namespace App\Jobs;

use App\Models\Attendee;
use App\Models\CommunicationLog;
use App\Models\Message;
use App\Services\ConnectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Message $message
    ) {}

    public function handle(): void
    {
        $message = $this->message->fresh();

        if (! $message || $message->status === Message::STATUS_SENT) {
            return;
        }

        if (! app(ConnectionService::class)->isOnline()) {
            $message->update(['status' => Message::STATUS_QUEUED]);
            Log::info("Message {$message->id}: Offline, queued for sync.");

            return;
        }

        $event = $message->event;
        $attendees = $event->attendees()->whereNotNull('email')->get();

        if ($attendees->isEmpty()) {
            Log::info("Message {$message->id}: No attendees with email to send to.");
            $message->update(['status' => Message::STATUS_SENT]);
            return;
        }

        $channel = $message->channel;
        $sent = 0;
        $failed = 0;

        foreach ($attendees as $attendee) {
            try {
                $this->sendToAttendee($message, $attendee, $channel);
                CommunicationLog::create([
                    'organization_id' => $event->organization_id,
                    'event_id' => $event->id,
                    'message_id' => $message->id,
                    'attendee_id' => $attendee->id,
                    'channel_id' => $channel->id,
                    'sent_at' => now(),
                    'delivered_at' => now(),
                    'status' => CommunicationLog::STATUS_DELIVERED,
                ]);
                $sent++;
            } catch (\Throwable $e) {
                Log::warning("Message {$message->id} to {$attendee->email}: {$e->getMessage()}");
                CommunicationLog::create([
                    'organization_id' => $event->organization_id,
                    'event_id' => $event->id,
                    'message_id' => $message->id,
                    'attendee_id' => $attendee->id,
                    'channel_id' => $channel->id,
                    'sent_at' => now(),
                    'status' => CommunicationLog::STATUS_FAILED,
                ]);
                $failed++;
            }
        }

        $message->update([
            'status' => $failed > 0 && $sent === 0 ? Message::STATUS_FAILED : Message::STATUS_SENT,
        ]);

        Log::info("Message {$message->id}: Sent to {$sent}, failed {$failed}.");
    }

    protected function sendToAttendee(Message $message, Attendee $attendee, $channel): void
    {
        $content = $message->renderContentForAttendee($attendee);
        $subject = $message->subject ? $this->personalize($message->subject, $message->event, $attendee) : null;

        match ($channel->slug) {
            'email' => $this->sendEmail($attendee->email, $subject ?? 'Event reminder', $content),
            'sms' => $this->sendSms($attendee->phone ?? $attendee->email, $content),
            'beep_call' => $this->sendBeepCall($attendee->phone ?? $attendee->email, $message),
            'social_media' => $this->sendSocialMedia($attendee, $content),
            default => Log::info("Channel {$channel->slug} not implemented, simulating send to {$attendee->email}"),
        };
    }

    protected function sendEmail(string $to, string $subject, string $content): void
    {
        // Placeholder: use Laravel Mail when configured
        Log::info("Email to {$to}: {$subject}");
    }

    protected function sendSms(string $to, string $content): void
    {
        Log::info("SMS to {$to}: " . substr($content, 0, 50) . '...');
    }

    protected function sendBeepCall(string $to, Message $message): void
    {
        Log::info("Beep call to {$to}, audio: " . ($message->audio_file ?? 'none'));
    }

    protected function sendSocialMedia(Attendee $attendee, string $content): void
    {
        Log::info("Social media post for {$attendee->email}: " . substr($content, 0, 50) . '...');
    }

    protected function personalize(string $text, $event, Attendee $attendee): string
    {
        return str_replace(
            ['{name}', '{event_name}', '{event_time}', '{venue}', '{meeting_link}'],
            [
                $attendee->name,
                $event->name ?? '',
                $event->date?->format('M j, Y') . ($event->time_formatted ? ' at ' . $event->time_formatted : ''),
                $event->venue ?? '',
                $event->meeting_link ?? '',
            ],
            $text
        );
    }

    public function failed(\Throwable $exception): void
    {
        $this->message->update(['status' => Message::STATUS_FAILED]);
        Log::error("SendMessageJob failed for message {$this->message->id}: {$exception->getMessage()}");
    }
}
