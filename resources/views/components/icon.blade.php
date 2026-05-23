@props(['name', 'size' => 18, 'stroke' => 'currentColor', 'w' => 2])

<svg viewBox="0 0 24 24" fill="none" stroke="{{ $stroke }}" stroke-width="{{ $w }}" stroke-linecap="round" stroke-linejoin="round" width="{{ $size }}" height="{{ $size }}" {{ $attributes }}>
    @switch($name)
        @case('search')     <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/> @break
        @case('pin')        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/> @break
        @case('home')       <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/> @break
        @case('map')        <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/> @break
        @case('heart')      <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/> @break
        @case('bell')       <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/> @break
        @case('user')       <path d="M20 21v-1.5A4.5 4.5 0 0 0 15.5 15h-7A4.5 4.5 0 0 0 4 19.5V21"/><circle cx="12" cy="8" r="4"/> @break
        @case('phone')      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/> @break
        @case('whatsapp')   <path d="M3 21l1.65-3.8A9 9 0 1 1 21 12a9 9 0 0 1-13.5 7.8z"/><path d="M8 10c.4 1.4 1.6 3 3 4s2.6 1.6 4 2c.7.2 1.2-.2 1.5-.6.2-.3.2-.7 0-1l-1-1c-.2-.2-.5-.3-.8-.2l-1 .3c-.3.1-.7 0-.9-.3-.4-.5-1-1-1.4-1.5-.2-.3-.3-.6-.2-.9l.3-1c.1-.3 0-.6-.2-.8l-1-1c-.3-.2-.7-.2-1 0-.4.3-.8.8-.6 1.5z"/> @break
        @case('arrow-l')    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/> @break
        @case('chev-r')     <polyline points="9 18 15 12 9 6"/> @break
        @case('chev-l')     <polyline points="15 18 9 12 15 6"/> @break
        @case('chev-d')     <polyline points="6 9 12 15 18 9"/> @break
        @case('plus')       <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/> @break
        @case('minus')      <line x1="5" y1="12" x2="19" y2="12"/> @break
        @case('check')      <polyline points="20 6 9 17 4 12"/> @break
        @case('lock')       <rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/> @break
        @case('star')       <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/> @break
        @case('star-f')     <polygon fill="#F5BA12" stroke="#F5BA12" points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/> @break
        @case('clock')      <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/> @break
        @case('directions') <polyline points="8 3 12 7 8 11"/><path d="M4 14V8a2 2 0 0 1 2-2h6"/><polyline points="16 21 12 17 16 13"/><path d="M20 10v6a2 2 0 0 1-2 2h-6"/> @break
        @case('menu')       <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/> @break
        @case('utensils')   <path d="M3 2v8a3 3 0 0 0 6 0V2"/><path d="M6 10v12"/><path d="M14 2c-2 0-4 2-4 5s2 5 4 5v10"/> @break
        @case('coffee')     <path d="M3 8h14v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V8z"/><path d="M17 11h3a2 2 0 0 1 0 4h-3"/><line x1="6" y1="3" x2="6" y2="5"/><line x1="10" y1="3" x2="10" y2="5"/><line x1="14" y1="3" x2="14" y2="5"/> @break
        @case('steth')      <path d="M6 2v6a4 4 0 0 0 8 0V2"/><path d="M10 14v3a4 4 0 0 0 4 4h0a4 4 0 0 0 4-4v-2"/><circle cx="18" cy="13" r="2"/> @break
        @case('shop')       <path d="M3 9l1.5-5h15L21 9"/><path d="M3 9v11h18V9"/><path d="M3 9h18"/><path d="M9 13h6"/> @break
        @case('cart')       <circle cx="9" cy="21" r="1.5"/><circle cx="18" cy="21" r="1.5"/><path d="M3 3h2l2.4 12.4a2 2 0 0 0 2 1.6h9a2 2 0 0 0 2-1.6L22 7H6"/> @break
        @case('chart')      <line x1="3" y1="20" x2="21" y2="20"/><polyline points="5 16 9 11 13 14 19 6"/> @break
        @case('gift')       <polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/> @break
        @case('calendar')   <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/> @break
        @case('tag')        <path d="M20.59 13.41 13.42 20.58a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1.5" fill="currentColor" stroke="none"/> @break
        @case('share')      <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/> @break
        @case('download')   <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/> @break
        @case('copy')       <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/> @break
        @case('eye')        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/> @break
        @case('gear')       <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/> @break
        @case('briefcase')  <rect x="2" y="7" width="20" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/> @break
        @case('scissors')   <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/> @break
        @case('graduation') <path d="M22 10 12 4 2 10l10 6 10-6z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/> @break
        @case('flag')       <path d="M4 22V4a3 3 0 0 1 3-3h11l-3 5 3 5H7"/><line x1="4" y1="22" x2="4" y2="15"/> @break
        @case('logout')     <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/> @break
    @endswitch
</svg>
