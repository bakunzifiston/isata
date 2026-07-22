<section id="pricing" class="bg-dawn-2 py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div x-reveal class="mb-12 text-center">
            <h2 class="font-display text-3xl font-semibold tracking-tight sm:text-4xl">Simple, honest pricing</h2>
            <p class="mt-4 text-ink/70">Start free. Upgrade when your events grow.</p>
        </div>

        @if(($plans ?? collect())->isEmpty())
        <p class="text-center text-sm text-ink/60">Pricing plans are being configured. <a href="{{ route('register') }}" class="text-coral hover:underline">Register</a> to get started.</p>
        @else
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($plans as $index => $plan)
            <article
                x-reveal.delay.{{ $index * 80 }}
                class="relative flex flex-col rounded-[22px] border {{ $plan->isMostPopular() ? 'border-coral bg-white shadow-lg ring-2 ring-coral/20' : 'border-ink/8 bg-dawn' }} p-6 transition duration-200 hover:-translate-y-1"
            >
                @if($plan->isMostPopular())
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-coral px-3 py-0.5 font-mono text-[10px] font-medium uppercase tracking-wide text-white">Most popular</span>
                @endif

                <p class="font-mono text-xs uppercase tracking-widest text-ink/50">{{ $plan->name }}</p>
                <p class="mt-3 font-display text-4xl font-semibold">
                    ${{ number_format($plan->price, 0) }}
                    @if($plan->price > 0)
                        <span class="text-base font-normal text-ink/50">/mo</span>
                    @endif
                </p>

                <ul class="mt-6 flex-1 space-y-3">
                    @foreach($plan->marketingFeatures() as $feature)
                    <li class="flex items-start gap-2 text-sm text-ink/70">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-sage" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>

                @guest
                <a href="{{ route('register') }}" class="mt-6 inline-flex items-center justify-center rounded-full {{ $plan->isMostPopular() ? 'bg-coral text-white hover:bg-coral/90' : 'border border-ink/15 bg-white text-ink hover:bg-dawn-2' }} px-4 py-2.5 text-sm font-semibold transition duration-200">
                    {{ $plan->price == 0 ? 'Start free' : 'Get started' }}
                </a>
                @else
                <a href="{{ route('subscription.plans') }}" class="mt-6 inline-flex items-center justify-center rounded-full border border-ink/15 bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:bg-dawn-2">
                    View plans
                </a>
                @endguest
            </article>
            @endforeach
        </div>
        @endif
    </div>
</section>
