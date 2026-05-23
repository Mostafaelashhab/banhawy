@extends('layouts.mobile')

@section('title', 'تأكيد رقم الموبايل · بنهاوي')
@section('shell-class', 'no-bnav')

@section('content')
<div class="app-head">
    <a href="{{ route('home') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">تأكيد رقم الموبايل</div>
</div>

<div style="padding: 20px 22px; flex: 1; display: flex; flex-direction: column;">

    <div style="width: 64px; height: 64px; border-radius: 18px; background: rgba(13,148,136,.10); display: grid; place-items: center; margin-bottom: 14px;">
        <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="#0D9488" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 21l1.65-3.8A9 9 0 1 1 21 12a9 9 0 0 1-13.5 7.8z"/>
            <path d="M9 12h.01M12 12h.01M15 12h.01"/>
        </svg>
    </div>

    <h2 style="font-size: 22px; font-weight: 900; color: var(--navy); margin: 0;">أكّد رقم موبايلك</h2>
    <p style="font-size: 13px; color: var(--ink-3); font-weight: 600; margin-top: 6px; line-height: 1.7;">
        بعتلك كود تحقق على واتساب على الرقم
        <strong style="color: var(--ink-1); direction: ltr; display: inline-block;">{{ $user->phone }}</strong>
        — ادخل الكود تحت.
    </p>

    @if(session('flash'))
        <div class="flash" style="margin-top: 14px;">{{ session('flash') }}</div>
    @endif
    @if(session('flash_warn'))
        <div class="flash" style="margin-top: 14px; background: #FEF3C7; color: #92400E;">{{ session('flash_warn') }}</div>
    @endif
    @if(session('flash_error'))
        <div class="flash" style="margin-top: 14px; background: #FEE2E2; color: #991B1B;">{{ session('flash_error') }}</div>
    @endif
    @if($errors->any())
        <div class="flash err" style="margin-top: 14px;">{{ $errors->first() }}</div>
    @endif

    <form method="post" action="{{ route('phone.verify') }}" style="margin-top: 20px;">
        @csrf
        <label class="field-label">كود التحقق (5 أرقام)</label>
        <div class="otp-input-wrap">
            <input type="text"
                   name="code"
                   id="otp-input"
                   inputmode="numeric"
                   pattern="[0-9]*"
                   maxlength="5"
                   required
                   autocomplete="one-time-code"
                   autofocus
                   placeholder="• • • • •">
        </div>

        <button type="submit" class="btn btn-navy btn-full" style="padding: 13px; font-size: 14px; margin-top: 18px;">تأكيد</button>
    </form>

    <form method="post" action="{{ route('phone.send') }}" style="margin-top: 14px;">
        @csrf
        <button type="submit" class="btn btn-line btn-full" style="padding: 11px; font-size: 13px;">
            إعادة إرسال الكود
        </button>
    </form>

    <p class="tiny" style="text-align: center; color: var(--ink-3); margin-top: 18px; line-height: 1.6;">
        الكود ساري لمدة 10 دقائق · لو الرقم مش صحيح
        <a href="{{ route('account') }}" style="color: var(--teal); font-weight: 800;">عدّله من حسابك</a>
    </p>
</div>

<style>
.otp-input-wrap {
    margin-top: 8px;
    background: #FAFBFC;
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 6px;
    transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
}
.otp-input-wrap:focus-within {
    background: white;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(13,148,136,.14);
}
#otp-input {
    width: 100%;
    background: transparent;
    border: none;
    outline: none;
    font-family: ui-monospace, monospace;
    font-size: 28px;
    font-weight: 900;
    text-align: center;
    letter-spacing: 12px;
    color: var(--ink-1);
    padding: 10px 6px;
    direction: ltr;
}
#otp-input::placeholder {
    color: var(--ink-4);
    letter-spacing: 8px;
}
</style>

<script>
// Auto-submit when 5 digits are entered + strip non-digits
(function () {
    var input = document.getElementById('otp-input');
    if (!input) return;
    input.addEventListener('input', function () {
        input.value = input.value.replace(/\D/g, '').slice(0, 5);
        if (input.value.length === 5) {
            input.form.submit();
        }
    });
})();
</script>
@endsection
