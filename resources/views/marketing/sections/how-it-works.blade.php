<section id="how-it-works" class="bg-dawn-2 py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div x-reveal class="mb-12 max-w-2xl">
            <h2 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">How it works</h2>
            <p class="mt-4 text-ink/70">Seven steps from idea to follow-up — built for organizers who move fast.</p>
        </div>

        <div class="flex gap-4 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-thin">
            @foreach([
                ['step' => '01', 'title' => 'Create', 'body' => 'Set up your event with date, venue, and attendee list.'],
                ['step' => '02', 'title' => 'Channels', 'body' => 'Choose email, SMS, beep call, or social for each segment.'],
                ['step' => '03', 'title' => 'Customize', 'body' => 'Write messages with merge fields and channel-specific previews.'],
                ['step' => '04', 'title' => 'Schedule', 'body' => 'Send now or queue for later — even when you\'re offline.'],
                ['step' => '05', 'title' => 'Confirm', 'body' => 'Review recipient count, channels, and reminder timing.'],
                ['step' => '06', 'title' => 'Monitor', 'body' => 'Track RSVPs and delivery rates in real time.'],
                ['step' => '07', 'title' => 'Follow up', 'body' => 'Send thank-yous, feedback forms, and certificates.'],
            ] as $index => $card)
            <article
                x-reveal.delay.{{ $index * 80 }}
                class="w-64 shrink-0 snap-start rounded-[22px] border border-ink/8 bg-dawn p-6 transition duration-200 hover:-translate-y-1 hover:shadow-lg"
            >
                <span class="font-mono text-xs text-signal">{{ $card['step'] }}</span>
                <h3 class="mt-3 font-display text-xl font-semibold">{{ $card['title'] }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-ink/65">{{ $card['body'] }}</p>
            </article>
            @endforeach
        </div>
    </div>
</section>
