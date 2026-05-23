@php $route = request()->route()?->getName() ?? ''; @endphp
<nav class="sidebar-nav" aria-label="نظام التنقّل">
    @auth
        @php $section = 'تحكم'; @endphp
        <div class="sidebar-section">{{ $section }}</div>
        <a href="{{ route('merchant.dashboard') }}" class="sidebar-item @if($route === 'merchant.dashboard') is-active @endif">
            <x-icon name="home" :size="18"/><span>الرئيسية</span>
        </a>
        <a href="{{ route('merchant.orders.index') }}" class="sidebar-item @if(str_starts_with($route, 'merchant.orders')) is-active @endif">
            <x-icon name="cart" :size="18"/><span>الطلبات</span>
        </a>
        <a href="{{ route('merchant.bookings.index') }}" class="sidebar-item @if(str_starts_with($route, 'merchant.bookings')) is-active @endif">
            <x-icon name="calendar" :size="18"/><span>الحجوزات</span>
        </a>
        <a href="{{ route('merchant.analytics') }}" class="sidebar-item @if($route === 'merchant.analytics') is-active @endif">
            <x-icon name="chart" :size="18"/><span>التحليلات</span>
        </a>
        <a href="{{ route('merchant.qr') }}" class="sidebar-item @if($route === 'merchant.qr') is-active @endif">
            <x-icon name="share" :size="18"/><span>QR للمتجر</span>
        </a>
        <a href="{{ route('merchant.settings') }}" class="sidebar-item @if($route === 'merchant.settings') is-active @endif">
            <x-icon name="gear" :size="18"/><span>الإعدادات</span>
        </a>

        <div class="sidebar-section">عام</div>
        <a href="{{ route('home') }}" class="sidebar-item @if($route === 'home') is-active @endif">
            <x-icon name="home" :size="18"/><span>اكتشف</span>
        </a>
        <a href="{{ route('map') }}" class="sidebar-item @if($route === 'map') is-active @endif">
            <x-icon name="pin" :size="18"/><span>الخريطة</span>
        </a>
        <a href="{{ route('search') }}" class="sidebar-item @if($route === 'search') is-active @endif">
            <x-icon name="search" :size="18"/><span>بحث</span>
        </a>
        <a href="{{ route('favorites.index') }}" class="sidebar-item @if(str_starts_with($route, 'favorites')) is-active @endif">
            <x-icon name="heart" :size="18"/><span>المفضلة</span>
        </a>
        <a href="{{ route('track') }}" class="sidebar-item @if($route === 'track') is-active @endif">
            <x-icon name="search" :size="18"/><span>تتبّع طلب</span>
        </a>
    @else
        <div class="sidebar-section">استكشف</div>
        <a href="{{ route('home') }}" class="sidebar-item @if($route === 'home') is-active @endif">
            <x-icon name="home" :size="18"/><span>الرئيسية</span>
        </a>
        <a href="{{ route('map') }}" class="sidebar-item @if($route === 'map') is-active @endif">
            <x-icon name="pin" :size="18"/><span>الخريطة</span>
        </a>
        <a href="{{ route('search') }}" class="sidebar-item @if($route === 'search') is-active @endif">
            <x-icon name="search" :size="18"/><span>بحث</span>
        </a>
        <a href="{{ route('track') }}" class="sidebar-item @if($route === 'track') is-active @endif">
            <x-icon name="clock" :size="18"/><span>تتبّع طلب</span>
        </a>

        <div class="sidebar-section">حسابي</div>
        <a href="{{ route('login') }}" class="sidebar-item @if($route === 'login') is-active @endif">
            <x-icon name="user" :size="18"/><span>تسجيل الدخول</span>
        </a>
        <a href="{{ route('signup') }}" class="sidebar-item @if($route === 'signup') is-active @endif">
            <x-icon name="plus" :size="18"/><span>إنشاء حساب</span>
        </a>
        <a href="{{ route('register.step1') }}" class="sidebar-item @if(str_starts_with($route, 'register')) is-active @endif">
            <x-icon name="briefcase" :size="18"/><span>أضف نشاطك</span>
        </a>
    @endauth
</nav>
