@props(['size' => 220, 'center' => null, 'url' => null, 'fg' => '#001B2A', 'accent' => '#0D9488'])
<div
    class="qr-render"
    data-qr-url="{{ $url ?? url()->current() }}"
    data-qr-size="{{ $size }}"
    data-qr-center="{{ $center }}"
    data-qr-fg="{{ $fg }}"
    data-qr-accent="{{ $accent }}"
    style="width: {{ $size }}px; height: {{ $size }}px;"
></div>
