@props(['href' => '#', 'icon' => null, 'variant' => 'sidebar', 'form' => null])

@php
    $class = match ($variant) {
        'mobile' => 'mobile-nav-item',
        'menu'   => 'mobile-menu-item',
        default  => 'sidebar-link',
    };
@endphp

@if ($form)
    {{-- e.g. logout, or any link that has to submit a form.
         The item's own class (sidebar-link / mobile-nav-item / mobile-menu-item)
         goes on the <form>, and the <button> is display:contents so its
         children (icon + label) behave as if they sat directly inside that
         class — same layout as the <a> below, whether it's a horizontal
         sidebar row or a stacked mobile item. --}}
    <form method="POST" action="{{ $href }}" class="{{ $class }}">
        @csrf
        <button type="submit" class="contents">
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