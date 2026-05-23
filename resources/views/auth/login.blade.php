@extends('layouts.mobile')

@section('title', 'تسجيل الدخول · بنهاوي')

@section('content')
<div class="app-head">
    <a href="{{ route('home') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">تسجيل الدخول</div>
</div>

<form method="post" action="{{ route('login.attempt') }}" style="padding: 24px 22px; flex: 1; display: flex; flex-direction: column;">
    @csrf
    <div style="width: 64px; height: 64px; border-radius: 20px; background: var(--teal); display: grid; place-items: center; margin: 8px 0 16px;">
        <svg viewBox="0 0 24 24" width="32" height="32" fill="white">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>
        </svg>
    </div>
    <h2 style="font-size: 22px; font-weight: 900; color: var(--navy);">أهلًا بعودتك</h2>
    <p style="font-size: 12px; color: var(--ink-3); font-weight: 600; margin-top: 6px;">سجّل دخولك لإدارة نشاطك</p>

    <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 12px;">
        @if($errors->any())
            <div class="flash err">{{ $errors->first() }}</div>
        @endif
        <div>
            <label class="field-label">رقم الموبايل</label>
            <div class="field">
                <span style="color: var(--ink-4);"><x-icon name="phone" :size="16"/></span>
                <input type="tel" name="phone" value="{{ old('phone') }}" required dir="ltr" style="text-align: right;">
            </div>
        </div>
        <div>
            <label class="field-label">كلمة المرور</label>
            <div class="field">
                <span style="color: var(--ink-4);"><x-icon name="lock" :size="16"/></span>
                <input type="password" name="password" required>
            </div>
        </div>
    </div>

    <div style="flex: 1;"></div>

    <button type="submit" class="btn btn-navy btn-full" style="padding: 13px; font-size: 14px;">دخول</button>
    <p class="tiny" style="text-align: center; color: var(--ink-3); margin-top: 12px;">
        مستخدم جديد؟
        <a href="{{ route('signup') }}" style="color: var(--teal); font-weight: 800;">إنشاء حساب</a>
    </p>
    <p class="tiny" style="text-align: center; color: var(--ink-3); margin-top: 8px;">
        صاحب نشاط؟
        <a href="{{ route('register.step1') }}" style="color: var(--navy); font-weight: 800;">اعمل صفحة لمحلك</a>
    </p>
    <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--line); text-align: center;">
        <a href="{{ route('track') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: var(--teal-600);">
            <x-icon name="clock" :size="13" stroke="#0B7F75"/>
            تتبّع طلب بدون تسجيل دخول
        </a>
    </div>
</form>
@endsection
