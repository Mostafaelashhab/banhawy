@extends('layouts.mobile')

@section('title', 'تفعيل '.$plan->name.' · بنهاوي')
@section('page-title', 'تفعيل الاشتراك')

@section('content')
<div class="app-head">
    <a href="{{ route('merchant.dashboard') }}" class="back" aria-label="رجوع"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">تفعيل {{ $plan->name }}</div>
</div>

@if(session('flash'))
    <div class="flash">{{ session('flash') }}</div>
@endif
@if(session('flash_error'))
    <div class="flash" style="background: #FEE2E2; color: #991B1B;">{{ session('flash_error') }}</div>
@endif

<div class="scroll" style="padding: 14px 14px 28px;">

    {{-- ── Plan summary ────────────────────────────────────── --}}
    <div class="card card-pad" style="background: var(--navy); color: white; border: none;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div>
                <div style="font-weight: 900; font-size: 16px;">خطة {{ $plan->name }}</div>
                <div style="font-size: 11px; color: rgba(255,255,255,.65); margin-top: 2px;">{{ $cycle === 'yearly' ? 'اشتراك سنوي' : 'اشتراك شهري' }}</div>
            </div>
            <div style="text-align: left;">
                <div style="font-weight: 900; font-size: 22px;">{{ number_format($amount) }} <span style="font-size: 11px;">ج</span></div>
                @if($cycle === 'yearly')
                    <div style="font-size: 10px; color: var(--teal); font-weight: 800;">وفّرت 20%</div>
                @endif
            </div>
        </div>
        <div style="display: flex; gap: 8px; margin-top: 10px;">
            <a href="{{ route('merchant.upgrade', ['plan' => $plan->slug, 'cycle' => 'monthly']) }}"
               class="chip @if($cycle==='monthly') is-active @endif"
               style="padding: 6px 12px; font-size: 11px; @if($cycle==='monthly') background: var(--teal); color: white; @else background: rgba(255,255,255,.08); color: white; @endif">
                شهري
            </a>
            <a href="{{ route('merchant.upgrade', ['plan' => $plan->slug, 'cycle' => 'yearly']) }}"
               class="chip @if($cycle==='yearly') is-active @endif"
               style="padding: 6px 12px; font-size: 11px; @if($cycle==='yearly') background: var(--teal); color: white; @else background: rgba(255,255,255,.08); color: white; @endif">
                سنوي · وفّر 20%
            </a>
        </div>
    </div>

    {{-- ── Pending notice ─────────────────────────────────── --}}
    @if($pending)
        <div class="card card-pad" style="margin-top: 12px; background: #FEF3C7; border: 1px solid #FDE68A;">
            <div style="font-weight: 800; font-size: 13px; color: #92400E; margin-bottom: 4px;">عندك إيصال قيد المراجعة ⏳</div>
            <div style="font-size: 12px; color: #78350F; line-height: 1.7;">
                بعتنا إيصال بـ {{ number_format($pending->amount) }} ج بطريقة {{ $pending->methodLabel() }}.
                هنراجعه ونفعّل اشتراكك خلال 24 ساعة. لو فيه تأخير اتواصل معانا.
            </div>
        </div>
    @else
        {{-- ── Payment instructions ─────────────────────────── --}}
        <div class="card card-pad" style="margin-top: 12px;">
            <div style="font-weight: 900; font-size: 14px; margin-bottom: 10px;">طرق الدفع</div>

            <div class="pay-method" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--gray-100); border-radius: 12px; margin-bottom: 10px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: white; display: grid; place-items: center; flex-shrink: 0;">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#7B61FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="6" width="18" height="13" rx="3"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 800; font-size: 13px;">إنستاباي (InstaPay)</div>
                    <div style="font-size: 14px; font-weight: 900; color: var(--navy); direction: ltr; text-align: right; margin-top: 2px;">{{ \App\Models\PaymentReceipt::PAYMENT_PHONE }}</div>
                </div>
                <button type="button" class="btn btn-line copy-btn" data-copy="{{ \App\Models\PaymentReceipt::PAYMENT_PHONE }}" style="padding: 6px 10px; font-size: 11px;">نسخ</button>
            </div>

            <div class="pay-method" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--gray-100); border-radius: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: white; display: grid; place-items: center; flex-shrink: 0;">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#E60000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9"/><path d="M12 7v10M7 12h10"/>
                    </svg>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 800; font-size: 13px;">فودافون كاش</div>
                    <div style="font-size: 14px; font-weight: 900; color: var(--navy); direction: ltr; text-align: right; margin-top: 2px;">{{ \App\Models\PaymentReceipt::PAYMENT_PHONE }}</div>
                </div>
                <button type="button" class="btn btn-line copy-btn" data-copy="{{ \App\Models\PaymentReceipt::PAYMENT_PHONE }}" style="padding: 6px 10px; font-size: 11px;">نسخ</button>
            </div>

            <div style="margin-top: 12px; padding: 10px; background: rgba(13,148,136,.06); border-right: 3px solid var(--teal); border-radius: 8px;">
                <div style="font-weight: 800; font-size: 12px; color: var(--teal); margin-bottom: 6px;">خطوات الدفع:</div>
                <ol style="margin: 0; padding-right: 18px; font-size: 12px; line-height: 1.9; color: var(--ink-2);">
                    <li>حوّل <strong>{{ number_format($amount) }} ج</strong> على رقم <strong style="direction: ltr; display: inline-block;">{{ \App\Models\PaymentReceipt::PAYMENT_PHONE }}</strong></li>
                    <li>اعمل screenshot للإيصال أو رقم العملية</li>
                    <li>ارفع الصورة في الفورم اللي تحت</li>
                    <li>هنراجعها ونفعّل اشتراكك في أقل من 24 ساعة</li>
                </ol>
            </div>
        </div>

        {{-- ── Upload receipt form ──────────────────────────── --}}
        <form method="post" action="{{ route('merchant.upgrade.store') }}" enctype="multipart/form-data" class="card card-pad" style="margin-top: 12px;">
            @csrf
            <input type="hidden" name="plan_slug"     value="{{ $plan->slug }}">
            <input type="hidden" name="billing_cycle" value="{{ $cycle }}">

            <div style="font-weight: 900; font-size: 14px; margin-bottom: 12px;">رفع إيصال الدفع</div>

            <div style="margin-bottom: 14px;">
                <label class="label-strong" style="display: block; margin-bottom: 6px;">طريقة الدفع</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <label class="pay-pick">
                        <input type="radio" name="method" value="instapay" required style="display: none;">
                        <span>إنستاباي</span>
                    </label>
                    <label class="pay-pick">
                        <input type="radio" name="method" value="vodafone_cash" required style="display: none;">
                        <span>فودافون كاش</span>
                    </label>
                </div>
                @error('method') <div style="color: #B91C1C; font-size: 11px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 14px;">
                <label class="label-strong" for="reference_number" style="display: block; margin-bottom: 6px;">رقم العملية (اختياري)</label>
                <input type="text" id="reference_number" name="reference_number" maxlength="120"
                       placeholder="مثال: TXN1234567"
                       style="width: 100%; padding: 10px 12px; border: 1px solid var(--ink-5); border-radius: 10px; font-size: 13px; direction: ltr;">
                @error('reference_number') <div style="color: #B91C1C; font-size: 11px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom: 14px;">
                <label class="label-strong" for="receipt" style="display: block; margin-bottom: 6px;">صورة الإيصال</label>
                <input type="file" id="receipt" name="receipt" accept="image/*" required
                       style="width: 100%; padding: 8px; border: 1px dashed var(--ink-5); border-radius: 10px; font-size: 12px;">
                <div class="label-meta" style="margin-top: 4px;">JPEG, PNG, WebP — أقل من 5MB</div>
                @error('receipt') <div style="color: #B91C1C; font-size: 11px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn btn-teal btn-full" style="padding: 12px; font-size: 13px;">
                إرسال الإيصال للمراجعة
            </button>
        </form>
    @endif

    {{-- ── History ──────────────────────────────────────── --}}
    @if($history->isNotEmpty())
        <div style="margin-top: 18px;">
            <div class="label-strong" style="margin-bottom: 8px;">إيصالاتك السابقة</div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach($history as $r)
                    <div class="card card-pad" style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background:
                            @if($r->isApproved()) rgba(16,185,129,.14); color: #047857;
                            @elseif($r->isRejected()) rgba(220,38,38,.14); color: #B91C1C;
                            @else rgba(245,158,11,.14); color: #92400E;
                            @endif
                            display: grid; place-items: center; flex-shrink: 0; font-weight: 800; font-size: 11px;">
                            {{ $r->statusLabel() }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 800; font-size: 12px;">{{ $r->plan->name }} · {{ $r->cycleLabel() }}</div>
                            <div class="label-meta">{{ number_format($r->amount) }} ج · {{ $r->methodLabel() }} · {{ $r->created_at->translatedFormat('d M Y') }}</div>
                            @if($r->isRejected() && $r->admin_note)
                                <div style="font-size: 11px; color: #B91C1C; margin-top: 4px;">سبب الرفض: {{ $r->admin_note }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<style>
.pay-pick {
    display: flex; align-items: center; justify-content: center;
    padding: 12px; border-radius: 10px; border: 1.5px solid var(--ink-5);
    font-size: 13px; font-weight: 800; cursor: pointer;
    transition: all .15s ease;
}
.pay-pick:has(input:checked) {
    background: var(--teal); color: white; border-color: var(--teal);
}
</style>

<script>
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const txt = btn.dataset.copy;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(txt).then(() => {
                const orig = btn.textContent;
                btn.textContent = 'تم النسخ ✓';
                setTimeout(() => { btn.textContent = orig; }, 1500);
            });
        }
    });
});
</script>

@include('partials.visitor-nav')
@endsection
