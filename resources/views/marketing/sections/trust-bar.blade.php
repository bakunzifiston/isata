<section class="bg-dawn-2 py-8">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <p class="mb-4 text-center font-mono text-xs uppercase tracking-widest text-ink/50">Trusted by organizers across</p>
        <div class="flex flex-wrap items-center justify-center gap-3">
            @foreach(['NGOs', 'Churches', 'Corporates', 'Training institutions', 'Community initiatives', 'Startups'] as $tag)
                <span class="rounded-full border border-ink/10 bg-dawn px-4 py-1.5 font-mono text-xs text-ink/70">{{ $tag }}</span>
            @endforeach
        </div>
    </div>
</section>
