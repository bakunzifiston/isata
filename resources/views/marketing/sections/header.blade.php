<header
    x-data="{ open: false }"
    class="sticky top-0 z-50 border-b border-white/10 bg-dusk/95 backdrop-blur-md"
>
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <x-marketing-logo variant="dark" />

        <nav class="hidden items-center gap-8 md:flex">
            <a href="#how-it-works" class="text-sm font-medium text-dawn/80 transition hover:text-dawn">How it works</a>
            <a href="#channels" class="text-sm font-medium text-dawn/80 transition hover:text-dawn">Channels</a>
            <a href="#offline" class="text-sm font-medium text-dawn/80 transition hover:text-dawn">Offline-ready</a>
            <a href="#pricing" class="text-sm font-medium text-dawn/80 transition hover:text-dawn">Pricing</a>
        </nav>

        <div class="hidden items-center gap-3 md:flex">
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-dawn/80 transition hover:text-dawn">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-dawn/80 transition hover:text-dawn">Log in</a>
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-full bg-coral px-4 py-2 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-coral/90">
                    Start free
                </a>
            @endauth
        </div>

        <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg p-2 text-dawn md:hidden"
            @click="open = !open"
            :aria-expanded="open"
            aria-label="Toggle menu"
        >
            <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div x-show="open" x-cloak x-transition class="border-t border-white/10 bg-dusk md:hidden">
        <nav class="flex flex-col gap-1 px-4 py-4">
            <a href="#how-it-works" @click="open = false" class="rounded-lg px-3 py-2 text-sm font-medium text-dawn/90 hover:bg-dusk-2">How it works</a>
            <a href="#channels" @click="open = false" class="rounded-lg px-3 py-2 text-sm font-medium text-dawn/90 hover:bg-dusk-2">Channels</a>
            <a href="#offline" @click="open = false" class="rounded-lg px-3 py-2 text-sm font-medium text-dawn/90 hover:bg-dusk-2">Offline-ready</a>
            <a href="#pricing" @click="open = false" class="rounded-lg px-3 py-2 text-sm font-medium text-dawn/90 hover:bg-dusk-2">Pricing</a>
            <div class="mt-2 flex flex-col gap-2 border-t border-white/10 pt-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-dawn/90 hover:bg-dusk-2">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-dawn/90 hover:bg-dusk-2">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-coral px-4 py-2.5 text-center text-sm font-semibold text-white">Start free</a>
                @endauth
            </div>
        </nav>
    </div>
</header>

<style>[x-cloak] { display: none !important; }</style>
