@props(['name'])

@php
    $icons = [
        'home'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h14V10"/>',
        'plus'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',
        'academic-cap'   => '<path d="M22 10L12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/><path d="M22 10v6"/>',
        'users'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-6a4 4 0 11-8 0 4 4 0 018 0zm6 1a3 3 0 10-6 0"/>',
        'book'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12v5c2 2 5 3 7 3s5-1 7-3v-5"/>',
        'video'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.5-2.5v9L15 14m-9 3h7a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>',
        'clipboard-list' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4h6m-7 4h8m-9 4h10m-9 4h6"/>',
        'check-list'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-6m3 6V9m-9 8h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2z"/>',
        'check-circle'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 11l3 3L22 4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 20h14"/>',
        'calendar'       => '<rect x="3" y="4" width="18" height="17" rx="2" stroke-width="2"/><path stroke-width="2" d="M16 2v4M8 2v4M3 10h18"/>',
        'document'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 2h9l5 5v15H6a2 2 0 01-2-2V4a2 2 0 012-2z"/>',
        'bell'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.5-2V10a6.5 6.5 0 00-13 0v5L4 17h5m6 0a3 3 0 01-6 0"/>',
        'chat'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 11.5a8.4 8.4 0 01-9 8.5 9.3 9.3 0 01-4.3-1.1L3 20l1.5-4.5A8.5 8.5 0 1112 20"/>',
        'ticket'         => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16v14H4z"/><path stroke-width="2" d="M8 9h8M8 13h5"/>',
        'settings'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 3h3l.4 2.1a7.5 7.5 0 0 1 1.8.8l1.8-1.1 2.1 2.1-1.1 1.8c.3.6.6 1.2.8 1.8L21 11v3l-2.1.4a7.5 7.5 0 0 1-.8 1.8l1.1 1.8-2.1 2.1-1.8-1.1a7.5 7.5 0 0 1-1.8.8L13.5 22h-3l-.4-2.1a7.5 7.5 0 0 1-1.8-.8l-1.8 1.1-2.1-2.1 1.1-1.8a7.5 7.5 0 0 1-.8-1.8L2.5 14v-3l2.1-.4a7.5 7.5 0 0 1 .8-1.8L4.3 7l2.1-2.1 1.8 1.1a7.5 7.5 0 0 1 1.8-.8L10.5 3Z"/><circle cx="12" cy="12" r="3" stroke-width="2"/>',
        'logout'         => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H3m0 0 4-4m-4 4 4 4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5V4a1 1 0 0 1 1-1h7a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-7a1 1 0 0 1-1-1v-1"/>',
        'menu'           => '<path stroke-linecap="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'h-5 w-5', 'fill' => 'none', 'stroke' => 'currentColor', 'viewBox' => '0 0 24 24', 'aria-hidden' => 'true']) }}>
    {!! $icons[$name] ?? '' !!}
</svg>
