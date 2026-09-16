@props(['href' => '#', 'icon' => null, 'variant' => 'sidebar', 'form' => null])

@php
    $class = match ($variant) {
        'mobile' => 'mobile-nav-item',
        'menu'   => 'mobile-menu-item',
        default  => 'sidebar-link',
    };
@endphp

@if ($form)
        <form method="POST" action="{{ $href }}" class="contents">
        @csrf
        <button type="submit" class="{{ $class }} w-full">
            @if ($icon)
                <x-icon :name="$icon" />
            @endif
            <span>{{ $slot }}</span>
        </button>
    </form>
@else
    <a href="{{ $href }}" class="{{ $class }}">
        @if ($icon)
            <x-icon :name="$icon" />
        @endif
        <span>{{ $slot }}</span>
    </a>
@endif