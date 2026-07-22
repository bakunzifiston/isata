<div {{ $attributes->merge(['class' => 'relative w-full max-w-md mx-auto']) }} aria-hidden="true">
    <svg viewBox="0 0 400 220" class="w-full h-auto" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M30 110 C90 110, 110 60, 160 60" stroke="rgba(244,167,60,0.5)" stroke-width="2" stroke-dasharray="4 7" class="signal-path-animate" />
        <path d="M160 60 C210 60, 230 160, 280 160" stroke="rgba(148,163,184,0.4)" stroke-width="2" stroke-dasharray="4 7" />
        <path d="M280 160 C310 160, 330 110, 370 110" stroke="rgba(244,167,60,0.7)" stroke-width="2" stroke-dasharray="4 7" class="signal-path-animate" style="animation-delay: 0.8s" />

        <circle cx="30" cy="110" r="8" fill="#252C52" stroke="#F4A73C" stroke-width="2" />
        <text x="18" y="140" fill="rgba(246,239,228,0.6)" font-family="IBM Plex Mono, monospace" font-size="10">Sent</text>

        <circle cx="160" cy="60" r="8" fill="#252C52" stroke="rgba(148,163,184,0.5)" stroke-width="2" />
        <text x="130" y="40" fill="rgba(148,163,184,0.7)" font-family="IBM Plex Mono, monospace" font-size="10">No signal</text>

        <circle cx="280" cy="160" r="8" fill="#252C52" stroke="#F4A73C" stroke-width="2" opacity="0.7" />
        <text x="255" y="190" fill="rgba(246,239,228,0.6)" font-family="IBM Plex Mono, monospace" font-size="10">Queued</text>

        <g transform="translate(350, 90)">
            <circle cx="20" cy="20" r="28" fill="rgba(244,167,60,0.15)" />
            <circle cx="20" cy="20" r="18" fill="#252C52" stroke="#F4A73C" stroke-width="2" />
            <rect x="14" y="10" width="12" height="20" rx="3" fill="none" stroke="#F4A73C" stroke-width="1.5" />
            <circle cx="20" cy="26" r="1.5" fill="#F4A73C" />
        </g>
        <text x="345" y="150" fill="#F4A73C" font-family="IBM Plex Mono, monospace" font-size="10">Delivered</text>
    </svg>
</div>
