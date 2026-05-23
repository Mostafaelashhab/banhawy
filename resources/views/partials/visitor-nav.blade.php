@php $route = request()->route()?->getName() ?? ''; @endphp
<nav class="bnav">
    <a href="{{ route('home') }}" class="bnav-item @if($route === 'home') active @endif">
        <x-icon name="home"/><span>الرئيسية</span>
    </a>
    <a href="{{ route('map') }}" class="bnav-item @if($route === 'map') active @endif">
        <x-icon name="pin"/><span>الخريطة</span>
    </a>
    <a href="{{ route('search') }}" class="bnav-item @if($route === 'search') active @endif">
        <x-icon name="search"/><span>بحث</span>
    </a>
    @auth
        <a href="{{ route('favorites.index') }}" class="bnav-item @if(str_starts_with($route,'favorites')) active @endif">
            <x-icon name="heart"/><span>المفضلة</span>
        </a>
        @if(auth()->user()->isOwner())
            <a href="{{ route('merchant.dashboard') }}" class="bnav-item @if(str_starts_with($route,'merchant.')) active @endif">
                <x-icon name="user"/><span>حسابي</span>
            </a>
        @else
            <a href="{{ route('account') }}" class="bnav-item @if($route === 'account') active @endif">
                <x-icon name="user"/><span>حسابي</span>
            </a>
        @endif
    @else
        <a href="{{ route('login') }}" class="bnav-item">
            <x-icon name="heart"/><span>المفضلة</span>
        </a>
        <a href="{{ route('login') }}" class="bnav-item">
            <x-icon name="user"/><span>دخول</span>
        </a>
    @endauth
</nav>
