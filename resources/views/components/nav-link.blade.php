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
         The <form> is display:contents so it disappears from layout — its
         child <button> becomes the real item and gets the item's own class
         directly (same as the <a> below), so the whole row is a real,
         padded, hoverable box and every pixel of it is actually clickable —
         not just the icon/text glyphs.
         Because the form has no box, margin-based spacing (space-y-*) on
         its parent won't apply to it — parents stacking these items should
         use `gap` (flex/grid) instead. --}}
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