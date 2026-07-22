<footer class="border-t border-white/10 bg-dusk text-dawn">
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-1">
                <x-marketing-logo variant="dark" />
                <p class="mt-4 text-sm leading-relaxed text-dawn/60">Inclusive event communication for organizers who can't wait for perfect signal.</p>
            </div>

            <div>
                <h3 class="font-mono text-xs uppercase tracking-widest text-dawn/50">Product</h3>
                <ul class="mt-4 space-y-2 text-sm text-dawn/75">
                    <li><a href="#channels" class="transition hover:text-dawn">Channels</a></li>
                    <li><a href="#offline" class="transition hover:text-dawn">Offline-ready</a></li>
                    <li><a href="#pricing" class="transition hover:text-dawn">Pricing</a></li>
                    <li><a href="#how-it-works" class="transition hover:text-dawn">How it works</a></li>
                </ul>
            </div>

            <div>
                <h3 class="font-mono text-xs uppercase tracking-widest text-dawn/50">Use cases</h3>
                <ul class="mt-4 space-y-2 text-sm text-dawn/75">
                    <li>NGOs & nonprofits</li>
                    <li>Churches & faith groups</li>
                    <li>Corporate events</li>
                    <li>Training institutions</li>
                </ul>
            </div>

            <div>
                <h3 class="font-mono text-xs uppercase tracking-widest text-dawn/50">Company</h3>
                <ul class="mt-4 space-y-2 text-sm text-dawn/75">
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="transition hover:text-dawn">Dashboard</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="transition hover:text-dawn">Log in</a></li>
                        <li><a href="{{ route('register') }}" class="transition hover:text-dawn">Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>

        <x-pulse-divider variant="dusk" class="my-10" />

        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
            <p class="font-mono text-xs text-dawn/45">© {{ date('Y') }} {{ config('app.name') }}</p>
            <p class="font-mono text-xs text-dawn/45">Reach everyone. Even offline.</p>
        </div>
    </div>
</footer>
