@php $route = request()->route()?->getName() ?? ''; @endphp
<nav class="bnav">
    <a href="{{ route('merchant.dashboard') }}" class="bnav-item @if($route === 'merchant.dashboard') active @endif">
        <x-icon name="home"/><span>الرئيسية</span>
    </a>
    <a href="{{ route('merchant.orders.index') }}" class="bnav-item @if(str_starts_with($route,'merchant.orders')) active @endif">
        <x-icon name="cart"/><span>الطلبات</span>
    </a>
    <a href="{{ route('merchant.bookings.index') }}" class="bnav-item @if(str_starts_with($route,'merchant.bookings')) active @endif">
        <x-icon name="calendar"/><span>الحجوزات</span>
    </a>
    <a href="{{ route('merchant.analytics') }}" class="bnav-item @if($route === 'merchant.analytics') active @endif">
        <x-icon name="chart"/><span>التحليلات</span>
    </a>
    <a href="{{ route('merchant.settings') }}" class="bnav-item @if($route === 'merchant.settings') active @endif">
        <x-icon name="gear"/><span>الإعدادات</span>
    </a>
</nav>
