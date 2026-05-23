@extends('layouts.mobile')

@section('title', 'إنشاء حساب · بنهاوي')

@section('content')
<div class="app-head">
    <a href="{{ route('home') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">إنشاء حساب نشاط</div>
</div>

@include('partials.wizard-progress', ['step' => 1])

<form method="post" action="{{ route('register.step1.store') }}" style="padding: 0 18px; flex: 1; display: flex; flex-direction: column;">
    @csrf
    <h2 style="font-size: 20px; font-weight: 900; color: var(--navy); letter-spacing: -.3px;">ابدأ مع بنهاوي</h2>
    <p style="font-size: 12px; color: var(--ink-3); font-weight: 600; margin-top: 6px; line-height: 1.6;">
        أنشئ حساب نشاطك في دقيقة — اعرض منتجاتك، استقبل طلبات واتساب، وابقَ ظاهرًا على خريطة بنها.
    </p>

    <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
        <div>
            <label class="field-label">اسم صاحب النشاط</label>
            <div class="field">
                <span style="color: var(--ink-4);"><x-icon name="user" :size="16"/></span>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>
            @error('name') <div class="tiny" style="color: #B91C1C; margin-top: 5px;">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="field-label">رقم الموبايل</label>
            <div class="field focused">
                <span style="color: var(--teal);"><x-icon name="phone" :size="16" stroke="#0D9488"/></span>
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+20 1xx xxx xxxx" required dir="ltr" style="text-align: right;">
            </div>
            <div class="tiny" style="color: var(--ink-3); margin-top: 5px;">هنرسلك كود تأكيد على واتساب</div>
            @error('phone') <div class="tiny" style="color: #B91C1C; margin-top: 5px;">{{ $message }}</div> @enderror
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
            @error('password') <div class="tiny" style="color: #B91C1C; margin-top: 5px;">{{ $message }}</div> @enderror
        </div>
    </div>

    <div style="margin-top: 14px; display: flex; align-items: flex-start; gap: 8px;">
        <div style="width: 18px; height: 18px; border-radius: 5px; background: var(--teal); display: grid; place-items: center; flex-shrink: 0; margin-top: 1px;">
            <x-icon name="check" :size="11" stroke="white" w="3"/>
        </div>
        <span class="tiny" style="color: var(--ink-3); line-height: 1.6;">أوافق على الشروط وسياسة الخصوصية</span>
    </div>

    <div style="flex: 1;"></div>

    <div style="padding: 16px 0;">
        <button type="submit" class="btn btn-navy btn-full" style="padding: 13px; font-size: 14px;">
            إنشاء حساب <x-icon name="arrow-l" :size="14" stroke="white" w="2.4"/>
        </button>
        <p class="tiny" style="text-align: center; color: var(--ink-3); margin-top: 10px;">
            عندك حساب؟ <a href="{{ route('login') }}" style="color: var(--teal); font-weight: 800;">تسجيل الدخول</a>
        </p>
    </div>
</form>
@endsection
