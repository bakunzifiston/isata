<section id="offline" class="py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div x-reveal class="mb-10 max-w-2xl">
            <h2 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Offline-ready by default</h2>
            <p class="mt-4 text-ink/70">When the connection drops, your work doesn't disappear. Here's what keeps working.</p>
        </div>

        <div x-reveal.delay.80 class="overflow-hidden rounded-[22px] bg-dusk-2 shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-white/10 font-mono text-xs uppercase tracking-wider text-dawn/50">
                            <th class="px-6 py-4 font-medium">Channel</th>
                            <th class="px-6 py-4 font-medium">Organizer offline</th>
                            <th class="px-6 py-4 font-medium">Recipient offline</th>
                            <th class="px-6 py-4 font-medium">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/8 text-dawn/80">
                        @foreach([
                            ['channel' => 'Email', 'organizer' => 'Draft & queue', 'recipient' => 'Needs internet', 'notes' => 'Queued until online'],
                            ['channel' => 'SMS', 'organizer' => 'Draft & schedule', 'recipient' => 'Works via mobile network', 'notes' => 'Recipient doesn\'t need internet'],
                            ['channel' => 'Branded beep call', 'organizer' => 'Schedule & queue', 'recipient' => 'Works via mobile network', 'notes' => 'Pre-recorded, triggers when online'],
                            ['channel' => 'Calendar sync', 'organizer' => 'Drafts cached', 'recipient' => 'Confirmation stored', 'notes' => 'Syncs when connected'],
                            ['channel' => 'Social media', 'organizer' => 'Draft & queue', 'recipient' => 'Needs internet', 'notes' => 'Posts send automatically once online'],
                            ['channel' => 'Dashboard analytics', 'organizer' => 'Partial offline stats', 'recipient' => '—', 'notes' => 'Full data updates when online'],
                        ] as $row)
                        <tr class="transition hover:bg-white/3">
                            <td class="px-6 py-4 font-medium text-dawn">{{ $row['channel'] }}</td>
                            <td class="px-6 py-4">{{ $row['organizer'] }}</td>
                            <td class="px-6 py-4">{{ $row['recipient'] }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-dawn/60">{{ $row['notes'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
