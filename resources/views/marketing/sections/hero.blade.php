<section class="relative overflow-hidden bg-dusk text-dawn">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_rgba(244,167,60,0.08)_0%,_transparent_60%)]"></div>

    <div class="relative mx-auto grid max-w-6xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:items-center lg:py-24 lg:px-8">
        <div x-reveal>
            <p class="inline-flex items-center gap-2 rounded-full border border-signal/30 bg-dusk-2 px-3 py-1.5 font-mono text-xs uppercase tracking-wider text-signal">
                <span class="h-2 w-2 rounded-full bg-signal pulse-dot"></span>
                Built for low-connectivity regions
            </p>

            <h1 class="mt-6 font-display text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-[3.25rem]">
                Reach everyone. Even <em class="italic text-signal">offline</em>.
            </h1>

            <p class="mt-5 max-w-lg text-lg leading-relaxed text-dawn/80">
                Email, SMS, branded beep calls, calendar sync, and social — one platform for NGOs, churches, corporates, and community organizers who can't rely on stable internet.
            </p>

            @guest
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-coral px-6 py-3 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-coral/90 hover:shadow-lg hover:shadow-coral/20">
                    Start free
                </a>
                <a href="#how-it-works" class="inline-flex items-center justify-center rounded-full border border-dawn/25 px-6 py-3 text-sm font-semibold text-dawn transition duration-200 hover:-translate-y-0.5 hover:border-dawn/50 hover:bg-dusk-2">
                    See how it works
                </a>
            </div>
            @else
            <div class="mt-8">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-full bg-coral px-6 py-3 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-coral/90">
                    Go to dashboard
                </a>
            </div>
            @endguest

            <ul class="mt-10 space-y-2 font-mono text-xs text-dawn/55">
                <li>• Draft messages offline — they send when you're back online</li>
                <li>• SMS & beep calls reach people without smartphones or data</li>
                <li>• RSVP tracking across every channel in one place</li>
            </ul>
        </div>

        <div x-reveal.delay.80 class="lg:pl-4">
            <x-signal-path />
        </div>
    </div>
</section>
