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
        'search'         => '<circle cx="11" cy="11" r="7" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="m20 20-3.5-3.5"/>',
        'trash'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2m2 0-.7 12.1a2 2 0 0 1-2 1.9H8.7a2 2 0 0 1-2-1.9L6 7h12Z"/>',
        'inbox'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h4l2 3h4l2-3h4M4 12l1.6-6.4A2 2 0 0 1 7.5 4h9a2 2 0 0 1 1.9 1.6L20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6Z"/>',
        'arrow-right'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m0 0-6-6m6 6-6 6"/>',
        'user'           => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4" stroke-width="2"/>',
        'phone'          => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92Z"/>',
        'lock'           => '<rect x="3" y="11" width="18" height="11" rx="2" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'eye'            => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3" stroke-width="2"/>',
        'eye-off'        => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.88 9.88a3 3 0 1 0 4.24 4.24M6.1 6.1A13 13 0 0 0 2 12s3.5 7 10 7a9.7 9.7 0 0 0 4.9-1.32M17.94 17.94A13.1 13.1 0 0 0 22 12s-3.5-7-10-7a9.7 9.7 0 0 0-2.94.44M2 2l20 20"/>',
        'shield-check'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 22c4.16-1.45 7.66-3.95 7.66-8.95V6l-7.66-3-7.66 3v7.05C4.34 18.05 7.84 20.55 12 22Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 12 2 2 4-4"/>',
        'pencil'         => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'h-5 w-5', 'fill' => 'none', 'stroke' => 'currentColor', 'viewBox' => '0 0 24 24', 'aria-hidden' => 'true']) }}>
    {!! $icons[$name] ?? '' !!}
</svg>