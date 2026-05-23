@extends('layouts.mobile')

@section('title', 'إنشاء حساب · بنهاوي')
@section('page-title', 'إنشاء حساب')

@section('content')
<div class="app-head">
    <a href="{{ route('home') }}" class="back" aria-label="رجوع"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">إنشاء حساب</div>
</div>

<form method="post" action="{{ route('signup.attempt') }}" style="padding: 24px 22px; flex: 1; display: flex; flex-direction: column;">
    @csrf
    <div style="width: 64px; height: 64px; border-radius: 20px; background: var(--teal); display: grid; place-items: center; margin: 8px 0 16px;">
        <svg viewBox="0 0 24 24" width="32" height="32" fill="white">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/>
        </svg>
    </div>
    <h2 style="font-size: 22px; font-weight: 900; color: var(--navy);">يلا نبدأ</h2>
    <p style="font-size: 12px; color: var(--ink-3); font-weight: 600; margin-top: 6px; line-height: 1.6;">
        اعمل حساب عشان تحفظ أنشطتك المفضلة وتتابع طلباتك وحجوزاتك في بنها.
    </p>

    <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 12px;">
        @if($errors->any())
            <div class="flash err">{{ $errors->first() }}</div>
        @endif
        <div>
            <label class="field-label">الاسم</label>
            <div class="field">
                <span style="color: var(--ink-4);"><x-icon name="user" :size="16"/></span>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>
        </div>
        <div>
            <label class="field-label">رقم الموبايل</label>
            <div class="field">
                <span style="color: var(--ink-4);"><x-icon name="phone" :size="16"/></span>
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+20 1xx xxx xxxx" required dir="ltr" style="text-align: right;">
            </div>
        </div>
        <div>
            <label class="field-label">كلمة المرور</label>
            <div class="field">
                <span style="color: var(--ink-4);"><x-icon name="lock" :size="16"/></span>
                <input type="password" name="password" required minlength="6">
            </div>
        </div>
        <div>
            <label class="field-label">تأكيد كلمة المرور</label>
            <div class="field">
                <span style="color: var(--ink-4);"><x-icon name="lock" :size="16"/></span>
                <input type="password" name="password_confirmation" required>
            </div>
        </div>
    </div>

    <div style="flex: 1;"></div>

    <button type="submit" class="btn btn-navy btn-full" style="padding: 13px; font-size: 14px;">
        إنشاء حساب
    </button>
    <p class="tiny" style="text-align: center; color: var(--ink-3); margin-top: 12px;">
        عندك حساب؟ <a href="{{ route('login') }}" style="color: var(--teal); font-weight: 800;">تسجيل الدخول</a>
    </p>
    <p class="tiny" style="text-align: center; color: var(--ink-3); margin-top: 8px;">
        صاحب نشاط؟ <a href="{{ route('register.step1') }}" style="color: var(--navy); font-weight: 800;">اعمل صفحة لمحلك</a>
    </p>
</form>
@endsection
