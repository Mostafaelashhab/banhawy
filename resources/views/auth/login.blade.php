@extends('layouts.mobile')

@section('title', 'تسجيل الدخول · بنهاوي')
@section('shell-class', 'no-bnav')

@section('content')
<div class="app-head">
    <a href="{{ route('home') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">تسجيل الدخول</div>
</div>

<form method="post" action="{{ route('login.attempt') }}" style="padding: 24px 22px; flex: 1; display: flex; flex-direction: column;" autocomplete="on">
    @csrf

    <img src="/icons/banhawy-mark.svg" alt="بنهاوي" width="64" height="64"
         style="border-radius: 18px; margin: 8px 0 18px; box-shadow: 0 8px 22px -8px rgba(13,148,136,.45);">

    <h2 style="font-size: 22px; font-weight: 900; color: var(--navy);">أهلًا بعودتك</h2>
    <p style="font-size: 12.5px; color: var(--ink-3); font-weight: 600; margin-top: 6px;">سجّل دخولك لإدارة نشاطك على بنهاوي</p>

    <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 14px;">
        @if($errors->any())
            <div class="flash err">{{ $errors->first() }}</div>
        @endif

        <div>
            <label class="field-label" for="login-phone">رقم الموبايل</label>
            <div class="field">
                <span style="color: var(--ink-4);"><x-icon name="phone" :size="16"/></span>
                <input id="login-phone"
                       type="tel"
                       name="phone"
                       value="{{ old('phone') }}"
                       required
                       autocomplete="tel"
                       inputmode="tel"
                       placeholder="01xxxxxxxxx"
                       dir="ltr"
                       style="text-align: right;">
            </div>
        </div>

        <div>
            <label class="field-label" for="login-password">كلمة المرور</label>
            <div class="field">
                <span style="color: var(--ink-4);"><x-icon name="lock" :size="16"/></span>
                <input id="login-password"
                       type="password"
                       name="password"
                       required
                       autocomplete="current-password"
                       placeholder="••••••••">
            </div>
        </div>
    </div>

    <div style="flex: 1; min-height: 24px;"></div>

    <button type="submit" class="btn btn-navy btn-full" style="padding: 14px; font-size: 14.5px;">دخول</button>

    <p class="tiny" style="text-align: center; color: var(--ink-3); margin-top: 14px;">
        مستخدم جديد؟
        <a href="{{ route('signup') }}" style="color: var(--teal); font-weight: 800;">إنشاء حساب</a>
    </p>
    <p class="tiny" style="text-align: center; color: var(--ink-3); margin-top: 8px;">
        عندك خدمة أو شركة شحن؟
        <a href="{{ route('register.step1') }}" style="color: var(--navy); font-weight: 800;">سجّل خدمتك</a>
    </p>
</form>
@endsection
