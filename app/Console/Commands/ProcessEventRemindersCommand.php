<?php

namespace App\Console\Commands;

use App\Jobs\SendMessageJob;
use App\Models\Channel;
use App\Models\EmailSenderIdentity;
use App\Models\Event;
use App\Models\Message;
use App\Models\User;
use Illuminate\Console\Command;

class ProcessEventRemindersCommand extends Command
{
    protected $signature = 'reminders:process';

    protected $description = 'Send 24hr and 1hr event reminders';

    public function handle(): int
    {
        $now = now();
        $windowMinutes = 5;

        $events24hr = Event::query()
            ->where('status', Event::STATUS_SCHEDULED)
            ->whereHas('reminderSettings', fn ($q) => $q->whereNotNull('reminder_24hr_template_id')->whereNull('sent_24hr_at'))
            ->with('reminderSettings')
            ->get()
            ->filter(function (Event $event) use ($now, $windowMinutes) {
                $eventTime = $event->date_time;
                if (! $eventTime) {
                    return false;
                }
                $target = $eventTime->copy()->subHours(24);

                return $target->between($now->copy()->subMinutes($windowMinutes), $now);
            });

        $events1hr = Event::query()
            ->where('status', Event::STATUS_SCHEDULED)
            ->whereHas('reminderSettings', fn ($q) => $q->whereNotNull('reminder_1hr_template_id')->whereNull('sent_1hr_at'))
            ->with('reminderSettings')
            ->get()
            ->filter(function (Event $event) use ($now, $windowMinutes) {
                $eventTime = $event->date_time;
                if (! $eventTime) {
                    return false;
                }
                $target = $eventTime->copy()->subHour();

                return $target->between($now->copy()->subMinutes($windowMinutes), $now);
            });

        foreach ($events24hr as $event) {
            $this->sendReminder($event, '24hr');
        }

        foreach ($events1hr as $event) {
            $this->sendReminder($event, '1hr');
        }

        return self::SUCCESS;
    }

    protected function sendReminder(Event $event, string $type): void
    {
        $settings = $event->reminderSettings;
        if (! $settings) {
            return;
        }
        $templateId = $type === '24hr' ? $settings->reminder_24hr_template_id : $settings->reminder_1hr_template_id;
        $template = \App\Models\MessageTemplate::find($templateId);

        if (! $template) {
            return;
        }

        $template->loadMissing('channel');
        $senderIdentityId = null;
        $senderUserId = null;

        if ($template->channel?->slug === Channel::SLUG_EMAIL) {
            $orgId = $event->organization_id;
            $senderIdentityId = EmailSenderIdentity::query()
                ->where('organization_id', $orgId)
                ->orderByDesc('updated_at')
                ->value('id');

            if (! $senderIdentityId && $event->created_by) {
                $creator = User::find($event->created_by);
                if ($creator?->email) {
                    $senderIdentityId = EmailSenderIdentity::firstOrCreate(
                        [
                            'organization_id' => $orgId,
                            'from_email' => $creator->email,
                        ],
                        [
                            'from_name' => $creator->name,
                            'label' => 'Reminder (event creator)',
                        ],
                    )->id;
                }
            }

            $senderUserId = $event->created_by;
        }

        $message = $event->messages()->create([
            'channel_id' => $template->channel_id,
            'sender_user_id' => $senderUserId,
            'sender_identity_id' => $senderIdentityId,
            'sender_custom_name' => null,
            'sender_custom_email' => null,
            'subject' => $template->subject,
            'content' => $template->content,
            'scheduled_at' => now(),
            'status' => Message::STATUS_SCHEDULED,
        ]);

        SendMessageJob::dispatch($message);

        $settings->update([
            $type === '24hr' ? 'sent_24hr_at' : 'sent_1hr_at' => now(),
        ]);

        $this->info("Sent {$type} reminder for event {$event->name}");
    }
}
