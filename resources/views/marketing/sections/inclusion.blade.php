<section class="py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div x-reveal>
                <h2 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Inclusive by design</h2>
                <p class="mt-4 text-ink/70">Not everyone has a smartphone, data plan, or reliable signal. Isata meets people where they are.</p>

                <ul class="mt-10 space-y-8">
                    @foreach([
                        ['title' => 'Multi-channel reach', 'body' => 'Email for detail, SMS for urgency, beep calls for those who miss both — pick the right channel per audience.', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        ['title' => 'Accessible by design', 'body' => 'Plain language, clear confirmations, and reminders that work on basic phones — not just apps.', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
                        ['title' => 'Equity built in', 'body' => 'Segment contacts by need — VIPs get a beep call, general attendees get SMS — without leaving anyone out.', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ] as $item)
                    <li class="flex gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-dawn-2 text-signal">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                        </span>
                        <div>
                            <h3 class="font-semibold text-ink">{{ $item['title'] }}</h3>
                            <p class="mt-1 text-sm leading-relaxed text-ink/65">{{ $item['body'] }}</p>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div x-reveal.delay.80 class="rounded-[22px] bg-dusk p-8 text-dawn shadow-xl">
                <p class="font-mono text-xs uppercase tracking-widest text-signal">Signal strength by channel</p>
                <div class="mt-8 flex h-48 items-end justify-between gap-3">
                    @foreach([
                        ['label' => 'Push app', 'height' => 'h-[58px]', 'highlight' => false],
                        ['label' => 'In-app', 'height' => 'h-[67px]', 'highlight' => false],
                        ['label' => 'Email', 'height' => 'h-[106px]', 'highlight' => false],
                        ['label' => 'SMS', 'height' => 'h-[154px]', 'highlight' => true],
                        ['label' => 'Beep call', 'height' => 'h-[182px]', 'highlight' => true],
                    ] as $bar)
                    <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                        <div class="w-full {{ $bar['height'] }} rounded-t-lg {{ $bar['highlight'] ? 'bg-signal' : 'bg-dawn/35' }}"></div>
                        <span class="text-center font-mono text-[10px] leading-tight text-dawn/60">{{ $bar['label'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
