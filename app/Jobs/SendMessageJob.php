<?php

namespace App\Jobs;

use App\Models\Attendee;
use App\Models\Channel;
use App\Models\CommunicationLog;
use App\Models\Message;
use App\Services\ConnectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        $attendees = $event->attendees()->get();

        if ($attendees->isEmpty()) {
            Log::info("Message {$message->id}: No attendees to send to.");
            $message->update(['status' => Message::STATUS_SENT]);

            return;
        }

        $message->loadMissing(['sentBy', 'senderIdentity', 'channel']);
        $channel = $message->channel;
        $sent = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($attendees as $attendee) {
            $skipReason = $this->missingContactReason($channel, $attendee);
            if ($skipReason !== null) {
                $this->logDelivery($message, $attendee, $channel, CommunicationLog::STATUS_FAILED, [
                    'reason' => 'missing_contact_method',
                    'detail' => $skipReason,
                ]);
                $skipped++;

                continue;
            }

            try {
                $this->sendToAttendee($message, $attendee, $channel);
                $this->logDelivery($message, $attendee, $channel, CommunicationLog::STATUS_DELIVERED);
                $sent++;
            } catch (\Throwable $e) {
                Log::warning("Message {$message->id} to attendee {$attendee->id}: {$e->getMessage()}");
                $this->logDelivery($message, $attendee, $channel, CommunicationLog::STATUS_FAILED, [
                    'reason' => 'send_error',
                    'detail' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $message->update([
            'status' => $failed > 0 && $sent === 0 ? Message::STATUS_FAILED : Message::STATUS_SENT,
        ]);

        Log::info("Message {$message->id}: Sent to {$sent}, failed {$failed}, skipped {$skipped}.");
    }

    protected function missingContactReason(Channel $channel, Attendee $attendee): ?string
    {
        return match ($channel->slug) {
            Channel::SLUG_EMAIL => filled($attendee->email) ? null : 'email address required',
            Channel::SLUG_SMS, Channel::SLUG_BEEP_CALL => filled($attendee->phone) ? null : 'phone number required',
            default => filled($attendee->email) ? null : 'email address required for this channel',
        };
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    protected function logDelivery(
        Message $message,
        Attendee $attendee,
        Channel $channel,
        string $status,
        ?array $metadata = null
    ): void {
        $event = $message->event;
        $delivered = $status === CommunicationLog::STATUS_DELIVERED;

        CommunicationLog::create([
            'organization_id' => $event->organization_id,
            'event_id' => $event->id,
            'message_id' => $message->id,
            'attendee_id' => $attendee->id,
            'channel_id' => $channel->id,
            'sent_at' => now(),
            'delivered_at' => $delivered ? now() : null,
            'status' => $status,
            'metadata' => $metadata,
        ]);
    }

    protected function sendToAttendee(Message $message, Attendee $attendee, Channel $channel): void
    {
        $content = $message->renderContentForAttendee($attendee);
        $subject = $message->subject ? $this->personalize($message->subject, $message->event, $attendee) : null;

        match ($channel->slug) {
            Channel::SLUG_EMAIL => $this->sendEmail($message, (string) $attendee->email, $subject ?? 'Event reminder', $content),
            Channel::SLUG_SMS => $this->sendSms($message, (string) $attendee->phone, $content),
            Channel::SLUG_BEEP_CALL => $this->sendBeepCall((string) $attendee->phone, $message),
            Channel::SLUG_SOCIAL_MEDIA => $this->sendSocialMedia($attendee, $content),
            default => Log::info("Channel {$channel->slug} not implemented, simulating send to attendee {$attendee->id}"),
        };
    }

    protected function sendEmail(Message $message, string $to, string $subject, string $content): void
    {
        $attachmentNote = '';
        $attachmentDisk = $message->attachment_file && Storage::disk('local')->exists($message->attachment_file)
            ? 'local'
            : 'public';
        if ($message->attachment_file && Storage::disk($attachmentDisk)->exists($message->attachment_file)) {
            $attachmentNote = ' [attachment: '.$message->attachment_file.']';
        }

        $resolved = $message->resolveEmailSender();
        $fromSender = ' | from='.$resolved['name'].' <'.$resolved['email'].'>';

        Log::info("Email to {$to}: {$subject}{$attachmentNote}{$fromSender}");
    }

    protected function sendSms(Message $message, string $to, string $content): void
    {
        $attachmentNote = '';
        $attachmentDisk = $message->attachment_file && Storage::disk('local')->exists($message->attachment_file)
            ? 'local'
            : 'public';
        if ($message->attachment_file && Storage::disk($attachmentDisk)->exists($message->attachment_file)) {
            $attachmentNote = ' [MMS attachment: '.$message->attachment_file.']';
        }

        Log::info('SMS to '.$to.': '.substr($content, 0, 50).'...'.$attachmentNote);
    }

    protected function sendBeepCall(string $to, Message $message): void
    {
        Log::info("Beep call to {$to}, audio: ".($message->audio_file ?? 'none'));
    }

    protected function sendSocialMedia(Attendee $attendee, string $content): void
    {
        Log::info("Social media post for {$attendee->email}: ".substr($content, 0, 50).'...');
    }

    protected function personalize(string $text, $event, Attendee $attendee): string
    {
        return str_replace(
            ['{name}', '{event_name}', '{event_time}', '{venue}', '{meeting_link}'],
            [
                $attendee->name,
                $event->name ?? '',
                $event->date?->format('M j, Y').($event->time_formatted ? ' at '.$event->time_formatted : ''),
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
