<section id="channels" class="py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div x-reveal class="mb-12 max-w-2xl">
            <h2 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Every channel you need</h2>
            <p class="mt-4 text-ink/70">Reach attendees on the channel that actually gets through.</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['title' => 'Email', 'body' => 'Rich invitations, reminders, and follow-ups with open tracking.', 'color' => 'bg-coral/10 text-coral', 'premium' => false, 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['title' => 'SMS', 'body' => 'Direct to any mobile phone — no app or data required.', 'color' => 'bg-sage/15 text-sage', 'premium' => false, 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                ['title' => 'Branded beep call', 'body' => 'Pre-recorded voice reminders that ring through when recipients are online.', 'color' => 'bg-signal/15 text-signal', 'premium' => true, 'icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                ['title' => 'Social media', 'body' => 'Draft posts for Facebook, LinkedIn, and more — publish when connected.', 'color' => 'bg-dusk/10 text-dusk', 'premium' => false, 'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z'],
            ] as $index => $channel)
            <article
                x-reveal.delay.{{ $index * 80 }}
                class="relative rounded-[22px] border {{ $channel['premium'] ? 'border-signal/40 ring-2 ring-signal/20' : 'border-ink/8' }} bg-white p-6 transition duration-200 hover:-translate-y-1 hover:shadow-lg"
            >
                @if($channel['premium'])
                    <span class="absolute -top-3 right-4 rounded-full bg-signal px-2.5 py-0.5 font-mono text-[10px] font-medium uppercase tracking-wide text-dusk">Premium</span>
                @endif
                <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $channel['color'] }}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $channel['icon'] }}"/></svg>
                </span>
                <h3 class="mt-4 font-display text-lg font-semibold">{{ $channel['title'] }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-ink/65">{{ $channel['body'] }}</p>
            </article>
            @endforeach
        </div>
    </div>
</section>
