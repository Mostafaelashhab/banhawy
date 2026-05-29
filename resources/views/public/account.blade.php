@extends('layouts.mobile')

@section('title', 'حسابي · بنهاوي')
@section('page-title', 'حسابي')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('home') }}" class="back" aria-label="رجوع"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">حسابي</div>
</div>

@if(session('flash'))
    <div class="flash">{{ session('flash') }}</div>
@endif

<div class="scroll" style="padding: 14px 14px 0; display: flex; flex-direction: column; flex: 1; min-height: 0;">

    {{-- ── Profile card ───────────────────────────────────── --}}
    <div class="card" style="padding: 18px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 56px; height: 56px; border-radius: 50%; background: var(--navy); color: white; display: grid; place-items: center; font-size: 20px; font-weight: 900;">
            {{ mb_substr($user->name, 0, 1) }}
        </div>
        <div style="flex: 1; min-width: 0;">
            <div style="font-weight: 900; font-size: 16px; color: var(--navy);">{{ $user->name }}</div>
            <div class="label-meta" style="direction: ltr; text-align: right;">{{ $user->phone }}</div>
        </div>
    </div>

    {{-- ── Notifications opt-in ──────────────────────────── --}}
    <div style="margin-top: 12px;">
        @include('partials.notifications-card')
    </div>

    {{-- ── Quick stats ────────────────────────────────────── --}}
    <div style="display: grid; grid-template-columns: 1fr; gap: 10px;">
        <a href="{{ route('favorites.index') }}" class="card" style="padding: 14px; display: flex; align-items: center; gap: 12px;">
            <span style="width: 40px; height: 40px; border-radius: 12px; background: rgba(255,77,109,.12); display: grid; place-items: center; color: #FF4D6D;">
                <x-icon name="heart" :size="18" stroke="#FF4D6D"/>
            </span>
            <div style="flex: 1;">
                <div style="font-weight: 800; font-size: 13px;">المفضلة</div>
                <div class="label-meta">{{ $favoritesCount > 0 ? $favoritesCount . ' أنشطة محفوظة' : 'محفظ أنشطتك المفضلة هنا' }}</div>
            </div>
            <span style="color: var(--ink-4);"><x-icon name="chev-l" :size="14"/></span>
        </a>
        <a href="{{ route('track') }}" class="card" style="padding: 14px; display: flex; align-items: center; gap: 12px;">
            <span style="width: 40px; height: 40px; border-radius: 12px; background: var(--teal-50); display: grid; place-items: center; color: var(--teal);">
                <x-icon name="search" :size="18" stroke="#0D9488"/>
            </span>
            <div style="flex: 1;">
                <div style="font-weight: 800; font-size: 13px;">تتبّع طلب</div>
                <div class="label-meta">اكتب رقم الطلب وشوف حالته</div>
            </div>
            <span style="color: var(--ink-4);"><x-icon name="chev-l" :size="14"/></span>
        </a>
    </div>

    {{-- ── Recent favorites preview ───────────────────────── --}}
    @if($favorites->isNotEmpty())
        <div style="margin-top: 18px;">
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                <span class="label-strong">آخر ما حفظت</span>
                <a href="{{ route('favorites.index') }}" class="tiny" style="color: var(--teal);">عرض الكل</a>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach($favorites as $b)
                    <a href="{{ route('business.show', $b) }}" class="card card-pad" style="display: flex; gap: 10px; align-items: center;">
                        <div class="ph ph-{{ $b->type->slug }}" style="width: 44px; height: 44px; border-radius: 12px; font-size: 12px; flex-shrink: 0;">
                            {{ mb_substr($b->name, 0, 2) }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div class="label-strong" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $b->name }}</div>
                            <div class="label-meta">{{ $b->category }}</div>
                        </div>
                        <span class="stars"><x-icon name="star-f" :size="11"/> {{ number_format($b->rating, 1) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Merchant CTA ───────────────────────────────────── --}}
    <div class="card" style="margin-top: 18px; padding: 14px; background: var(--navy); color: white; position: relative; overflow: hidden; border: none;">
        <div style="position: absolute; top: -30px; left: -30px; width: 110px; height: 110px; border-radius: 50%; background: rgba(13,148,136,.2); filter: blur(35px); pointer-events: none;"></div>
        <div style="position: relative;">
            <div style="font-weight: 900; font-size: 14px; margin-bottom: 4px;">عندك نشاط في بنها؟</div>
            <div style="font-size: 11px; color: rgba(255,255,255,.7); font-weight: 600; line-height: 1.7;">اعمل صفحة لمحلك واستقبل طلباتك أونلاين.</div>
            <a href="{{ route('register.step1') }}" class="btn btn-teal" style="padding: 9px 14px; font-size: 12px; margin-top: 12px;">
                ابدأ مجانًا <x-icon name="arrow-l" :size="13" stroke="white" w="2.2"/>
            </a>
        </div>
    </div>

    {{-- ── Logout ─────────────────────────────────────────── --}}
    <form method="post" action="{{ route('logout') }}" style="margin-top: auto; padding-top: 18px;">
        @csrf
        <button type="submit" class="btn btn-line btn-full" style="padding: 13px; font-size: 13px; color: #B91C1C; border-color: #FECACA;">
            <x-icon name="logout" :size="14" stroke="#B91C1C"/>
            تسجيل الخروج
        </button>
    </form>
</div>

@include('partials.visitor-nav')
@endsection
