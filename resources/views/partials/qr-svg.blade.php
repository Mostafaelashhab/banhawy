@props(['size' => 200, 'center' => null])
<svg viewBox="0 0 100 100" width="{{ $size }}" height="{{ $size }}" style="display: block;">
    <rect width="100" height="100" fill="white"/>
    <g fill="#001B2A">
        {{-- Corner markers --}}
        <rect x="6"  y="6"  width="22" height="22" rx="2"/>
        <rect x="72" y="6"  width="22" height="22" rx="2"/>
        <rect x="6"  y="72" width="22" height="22" rx="2"/>
        <rect x="12" y="12" width="10" height="10" fill="white"/>
        <rect x="78" y="12" width="10" height="10" fill="white"/>
        <rect x="12" y="78" width="10" height="10" fill="white"/>
        <rect x="15" y="15" width="4" height="4"/>
        <rect x="81" y="15" width="4" height="4"/>
        <rect x="15" y="81" width="4" height="4"/>

        {{-- Data dots (decorative) --}}
        <rect x="36" y="8"  width="3" height="3"/><rect x="42" y="8"  width="3" height="3"/><rect x="50" y="8"  width="3" height="3"/><rect x="56" y="8"  width="3" height="3"/><rect x="62" y="8" width="3" height="3"/>
        <rect x="38" y="14" width="3" height="3"/><rect x="46" y="14" width="3" height="3"/><rect x="54" y="14" width="3" height="3"/><rect x="60" y="14" width="3" height="3"/>
        <rect x="36" y="20" width="3" height="3"/><rect x="44" y="20" width="3" height="3"/><rect x="52" y="20" width="3" height="3"/><rect x="60" y="20" width="3" height="3"/>
        <rect x="40" y="26" width="3" height="3"/><rect x="48" y="26" width="3" height="3"/><rect x="56" y="26" width="3" height="3"/>
        <rect x="8"  y="36" width="3" height="3"/><rect x="14" y="36" width="3" height="3"/><rect x="22" y="36" width="3" height="3"/><rect x="28" y="36" width="3" height="3"/>
        <rect x="8"  y="42" width="3" height="3"/><rect x="18" y="42" width="3" height="3"/><rect x="26" y="42" width="3" height="3"/>
        <rect x="12" y="48" width="3" height="3"/><rect x="20" y="48" width="3" height="3"/><rect x="28" y="48" width="3" height="3"/>
        <rect x="8"  y="54" width="3" height="3"/><rect x="16" y="54" width="3" height="3"/><rect x="24" y="54" width="3" height="3"/>
        <rect x="12" y="60" width="3" height="3"/><rect x="20" y="60" width="3" height="3"/><rect x="28" y="60" width="3" height="3"/>
        <rect x="72" y="36" width="3" height="3"/><rect x="80" y="36" width="3" height="3"/><rect x="88" y="36" width="3" height="3"/>
        <rect x="74" y="42" width="3" height="3"/><rect x="82" y="42" width="3" height="3"/><rect x="90" y="42" width="3" height="3"/>
        <rect x="72" y="48" width="3" height="3"/><rect x="78" y="48" width="3" height="3"/><rect x="86" y="48" width="3" height="3"/>
        <rect x="76" y="54" width="3" height="3"/><rect x="84" y="54" width="3" height="3"/><rect x="90" y="54" width="3" height="3"/>
        <rect x="72" y="60" width="3" height="3"/><rect x="80" y="60" width="3" height="3"/><rect x="88" y="60" width="3" height="3"/>
        <rect x="36" y="72" width="3" height="3"/><rect x="44" y="72" width="3" height="3"/><rect x="52" y="72" width="3" height="3"/><rect x="60" y="72" width="3" height="3"/>
        <rect x="38" y="78" width="3" height="3"/><rect x="48" y="78" width="3" height="3"/><rect x="56" y="78" width="3" height="3"/>
        <rect x="36" y="84" width="3" height="3"/><rect x="44" y="84" width="3" height="3"/><rect x="52" y="84" width="3" height="3"/><rect x="62" y="84" width="3" height="3"/>
        <rect x="40" y="90" width="3" height="3"/><rect x="50" y="90" width="3" height="3"/><rect x="58" y="90" width="3" height="3"/>
    </g>
    {{-- Center logo --}}
    <rect x="42" y="42" width="16" height="16" rx="3" fill="white" stroke="#001B2A" stroke-width=".5"/>
    <rect x="44" y="44" width="12" height="12" rx="2" fill="#0D9488"/>
    @if($center)
        <text x="50" y="53" font-size="5" font-weight="900" fill="white" text-anchor="middle" font-family="Cairo">{{ $center }}</text>
    @endif
</svg>
