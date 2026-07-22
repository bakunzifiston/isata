<section class="bg-dusk py-20 text-dawn sm:py-24">
    <div x-reveal class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">
            Reach everyone. Even <em class="italic text-signal">offline</em>.
        </h2>
        <p class="mt-4 text-lg text-dawn/75">Start with a free account. No credit card required.</p>

        @guest
        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-full bg-coral px-8 py-3.5 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-coral/90 hover:shadow-lg hover:shadow-coral/20">
                Start free
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-full border border-dawn/25 px-8 py-3.5 text-sm font-semibold text-dawn transition duration-200 hover:-translate-y-0.5 hover:border-dawn/50 hover:bg-dusk-2">
                Log in
            </a>
        </div>
        @else
        <div class="mt-8">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-full bg-coral px-8 py-3.5 text-sm font-semibold text-white transition duration-200 hover:-translate-y-0.5 hover:bg-coral/90">
                Go to dashboard
            </a>
        </div>
        @endguest
    </div>
</section>
