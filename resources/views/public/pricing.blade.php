@extends('layouts.mobile')

@section('title', 'الأسعار · بنهاوي')
@section('page-title', 'الأسعار')
@section('shell-class', 'no-bnav')

@section('content')
<div class="app-head">
    <a href="{{ route('home') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">الأسعار والاشتراكات</div>
</div>

<div style="padding: 14px 14px 28px;">

    {{-- Header --}}
    <div style="text-align: center; padding: 16px 8px 18px;">
        <div style="display: inline-block; padding: 5px 12px; border-radius: 8px; background: rgba(13,148,136,.10); color: var(--teal); font-size: 11px; font-weight: 800; letter-spacing: .3px; margin-bottom: 12px;">
            للخدمات وشركات الشحن في بنها
        </div>
        <h1 style="font-size: 24px; font-weight: 900; margin: 0 0 8px; letter-spacing: -.4px;">خطة واحدة بسيطة بتجيبلك زباين</h1>
        <p style="color: var(--ink-3); font-size: 13px; line-height: 1.75; margin: 0;">
            مفيش عمولة على الطلبات · ادفع شهري بس · ألغِ في أي وقت
        </p>
    </div>

    {{-- Top trust strip: no commission / cancel anytime / instant setup --}}
    <div class="trust-strip">
        <div class="trust-cell">
            <span class="trust-ico" style="color: #047857; background: rgba(16,185,129,.14);">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <span>بدون عمولة على الطلبات</span>
        </div>
        <div class="trust-cell">
            <span class="trust-ico" style="color: #0369A1; background: rgba(14,165,233,.14);">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <span>ألغِ في أي وقت</span>
        </div>
        <div class="trust-cell">
            <span class="trust-ico" style="color: #B45309; background: rgba(245,158,11,.14);">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <span>إعداد في 3 دقائق</span>
        </div>
    </div>

    {{-- Billing cycle toggle --}}
    <div class="billing-toggle-wrap">
        <div class="billing-toggle" role="tablist" aria-label="دورة الفوترة">
            <button type="button" class="billing-tab is-active" data-cycle="monthly">شهري</button>
            <button type="button" class="billing-tab" data-cycle="yearly">
                سنوي
                <span class="billing-save">وفّر 20%</span>
            </button>
        </div>
    </div>

    {{-- Plans --}}
    <div style="display: flex; flex-direction: column; gap: 12px;">
        @foreach($plans as $plan)
            @php
                $monthly = (int) $plan->price_monthly;
                $yearly  = (int) round($monthly * 12 * 0.80);   // 20% off if billed yearly
                $effectiveMonthlyYearly = $monthly > 0 ? (int) round($yearly / 12) : 0;
            @endphp
            <div class="plan-card @if($plan->is_featured) is-featured @endif">
                @if($plan->is_featured)
                    <span class="plan-badge">الأكثر طلباً</span>
                @endif

                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 10px;">
                    <div>
                        <div class="plan-name">{{ $plan->name }}</div>
                        <div class="plan-tagline">{{ $plan->tagline_ar }}</div>
                    </div>
                    <div style="text-align: left;">
                        @if($monthly === 0)
                            <div class="plan-price">مجاني</div>
                            <div class="plan-period">للأبد</div>
                        @else
                            <div class="plan-price-block" data-monthly="{{ $monthly }}" data-yearly="{{ $effectiveMonthlyYearly }}" data-yearly-total="{{ $yearly }}">
                                <div class="plan-price">
                                    <span class="plan-amount">{{ number_format($monthly) }}</span>
                                    <span style="font-size: 12px; font-weight: 800; color: var(--ink-3);">ج</span>
                                </div>
                                <div class="plan-period">/ شهرياً</div>
                                <div class="plan-yearly-note" hidden>
                                    {{ number_format($yearly) }} ج / سنوياً
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <ul class="plan-features">
                    @foreach(($plan->features ?? []) as $feat)
                        <li>
                            <span class="plan-check">
                                <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            {{ $feat }}
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('register.step1') }}" class="plan-cta @if($plan->is_featured) plan-cta-primary @else plan-cta-line @endif">
                    @if($monthly === 0)
                        ابدأ مجاناً الآن
                    @else
                        اشترك مع {{ $plan->name }}
                    @endif
                </a>

                @if($monthly > 0)
                    <div class="plan-foot">
                        أول أسبوع تجربة مجانية · بدون التزام
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ROI calculator — services-focused --}}
    <div class="roi-card" style="margin-top: 24px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <span style="width: 38px; height: 38px; border-radius: 11px; background: rgba(13,148,136,.14); color: var(--teal); display: grid; place-items: center;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>
            </span>
            <div>
                <div style="font-weight: 900; font-size: 14px;">حسبتها واطلعت رخيصة</div>
                <div style="font-size: 11.5px; color: var(--ink-3); font-weight: 700; margin-top: 2px;">99 ج/شهر = 3.3 ج/يوم</div>
            </div>
        </div>
        <p style="font-size: 12.5px; line-height: 1.8; color: var(--ink-2); margin: 0;">
            عميل واحد جديد كل شهر بيغطّي اشتراكك أضعاف. أغلب صنايعية بنهاوي بيوصلهم
            <strong style="color: var(--teal);">3-10 زبون جديد شهرياً</strong> من المنصة.
        </p>
    </div>

    {{-- Value props --}}
    <div style="margin-top: 22px;">
        <div style="font-weight: 900; font-size: 16px; margin-bottom: 12px;">ليه بنهاوي خصوصاً؟</div>

        <div style="display: grid; gap: 10px;">
            <div class="value-row">
                <span class="value-ico"><x-icon name="eye" :size="18" stroke="#0D9488"/></span>
                <div>
                    <div style="font-weight: 800; font-size: 13.5px;">سكان بنها بيدوّروا هنا</div>
                    <div style="color: var(--ink-3); font-size: 12px; margin-top: 2px; line-height: 1.7;">منصة محلية مفصّلة لبنها — جمهورك مش مزوّق بين 10 محافظات.</div>
                </div>
            </div>

            <div class="value-row">
                <span class="value-ico"><x-icon name="whatsapp" :size="18" stroke="#1FB855"/></span>
                <div>
                    <div style="font-weight: 800; font-size: 13.5px;">زبون → واتساب بضغطة</div>
                    <div style="color: var(--ink-3); font-size: 12px; margin-top: 2px; line-height: 1.7;">العميل يكلّمك مباشرة — مفيش وسيط ولا تطبيق بيدخل بينك وبينه.</div>
                </div>
            </div>

            <div class="value-row">
                <span class="value-ico"><x-icon name="lock" :size="18" stroke="#0369A1"/></span>
                <div>
                    <div style="font-weight: 800; font-size: 13.5px;">صفر عمولة على شغلك</div>
                    <div style="color: var(--ink-3); font-size: 12px; margin-top: 2px; line-height: 1.7;">اشتراك ثابت معروف. اللي تشتغله بيدخل جيبك كله — ما بناخدش جنيه من الطلبات.</div>
                </div>
            </div>

            <div class="value-row">
                <span class="value-ico"><x-icon name="star" :size="18" stroke="#F59E0B"/></span>
                <div>
                    <div style="font-weight: 800; font-size: 13.5px;">سمعتك بتكبر مع كل تقييم</div>
                    <div style="color: var(--ink-3); font-size: 12px; margin-top: 2px; line-height: 1.7;">عملاءك الراضيين يقيّموك علني، فيوصلوا عملاء جداد بدون إعلانات.</div>
                </div>
            </div>

            <div class="value-row">
                <span class="value-ico"><x-icon name="bell" :size="18" stroke="#B45309"/></span>
                <div>
                    <div style="font-weight: 800; font-size: 13.5px;">إشعارات فورية</div>
                    <div style="color: var(--ink-3); font-size: 12px; margin-top: 2px; line-height: 1.7;">أي طلب جديد بيوصلك تنبيه فوراً على موبايلك — متفوّتش حد.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- FAQ --}}
    <div style="margin-top: 24px;">
        <div style="font-weight: 900; font-size: 16px; margin-bottom: 12px;">أسئلة متكررة</div>

        <details class="faq-item">
            <summary>أقدر أبدأ مجاناً؟</summary>
            <p>أيوه. الخطة المجانية بتعطيك صفحة خدمة كاملة وظهور في البحث — كافية للبدايات. ولو حبيت ترقّى لـ Pro عندك أسبوع تجربة مجانية.</p>
        </details>
        <details class="faq-item">
            <summary>هل في عمولة على الطلبات اللي تجيلي؟</summary>
            <p>لا أبداً. اشتراك شهري/سنوي ثابت بس. الطلبات بتيجيلك مباشرة على واتساب — احنا مش وسيط مالي.</p>
        </details>
        <details class="faq-item">
            <summary>إزاي بأدفع؟ وفي إيصال؟</summary>
            <p>هنتواصل معاك على واتساب لإتمام الاشتراك (كاش أو تحويل بنكي/فودافون كاش). بنبعتلك إيصال رسمي بالتفاصيل.</p>
        </details>
        <details class="faq-item">
            <summary>أقدر أوقف الاشتراك في أي وقت؟</summary>
            <p>طبعاً. ابعتلنا رسالة واحدة على واتساب وهنوقّف الاشتراك. مفيش رسوم إلغاء، ومفيش عقود ملزمة.</p>
        </details>
        <details class="faq-item">
            <summary>الخطة السنوية بتوفّر قد إيه؟</summary>
            <p>الفاتورة السنوية بتخصم 20% من السعر الشهري — يعني Pro سنوي = 950 ج بدل 1188 ج (وفّر 238 ج).</p>
        </details>
        <details class="faq-item">
            <summary>إيه الفرق بين Pro و Business؟</summary>
            <p>Pro للصنايعية الفرديين والورش الصغيرة. Business لشركات الشحن والفرق اللي عاوزة تثبيت في الأعلى، تحليلات تفصيلية، وحسابات لفريق العمل.</p>
        </details>
    </div>

</div>

<style>
/* Trust strip */
.trust-strip {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    margin-bottom: 14px;
    background: white;
    border: 1px solid var(--line);
    border-radius: 13px;
    padding: 10px 6px;
}
.trust-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 800;
    color: var(--ink-1);
    text-align: center;
}
.trust-ico {
    width: 18px; height: 18px;
    border-radius: 50%;
    display: grid; place-items: center;
    flex-shrink: 0;
}
@media (max-width: 380px) {
    .trust-strip { grid-template-columns: 1fr; }
    .trust-cell { justify-content: flex-start; padding: 2px 8px; }
}

/* Billing cycle toggle */
.billing-toggle-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}
.billing-toggle {
    display: inline-flex;
    padding: 4px;
    background: #FAFBFC;
    border: 1px solid var(--line);
    border-radius: 12px;
    gap: 2px;
}
.billing-tab {
    background: transparent;
    border: none;
    padding: 8px 16px;
    border-radius: 9px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 800;
    color: var(--ink-3);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background .15s ease, color .15s ease;
}
.billing-tab:hover { color: var(--ink-1); }
.billing-tab.is-active {
    background: white;
    color: var(--ink-1);
    box-shadow: 0 2px 8px -2px rgba(0,27,42,.10);
}
.billing-save {
    background: rgba(16,185,129,.14);
    color: #047857;
    font-size: 10px;
    font-weight: 800;
    padding: 2px 6px;
    border-radius: 6px;
    letter-spacing: .3px;
}

.plan-card {
    background: white;
    border: 1.5px solid var(--line);
    border-radius: 16px;
    padding: 18px;
    position: relative;
    transition: border-color .15s ease, box-shadow .15s ease;
}
.plan-card.is-featured {
    border-color: var(--teal);
    box-shadow: 0 12px 30px -12px rgba(13,148,136,.30);
}
.plan-badge {
    position: absolute;
    top: -10px;
    right: 18px;
    background: var(--teal);
    color: white;
    font-size: 10px;
    font-weight: 900;
    padding: 4px 10px;
    border-radius: 7px;
    letter-spacing: .3px;
}
.plan-name { font-weight: 900; font-size: 18px; letter-spacing: -.2px; }
.plan-tagline { font-size: 11.5px; color: var(--ink-3); font-weight: 700; margin-top: 3px; }
.plan-price { font-weight: 900; font-size: 24px; color: var(--navy); line-height: 1; }
.plan-period { font-size: 11px; color: var(--ink-4); font-weight: 700; margin-top: 4px; }
.plan-features {
    list-style: none; padding: 0; margin: 12px 0;
    display: flex; flex-direction: column; gap: 8px;
}
.plan-features li {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 600; color: var(--ink-2); line-height: 1.5;
}
.plan-check {
    width: 18px; height: 18px;
    border-radius: 50%;
    background: rgba(13,148,136,.14);
    color: var(--teal);
    display: grid; place-items: center;
    flex-shrink: 0;
}
.plan-cta {
    display: block; text-align: center;
    padding: 12px; border-radius: 11px;
    font-weight: 900; font-size: 13.5px;
    text-decoration: none;
    margin-top: 4px;
}
.plan-cta-primary { background: var(--navy); color: white; }
.plan-cta-line    { background: white; color: var(--ink-1); border: 1px solid var(--line); }

.plan-yearly-note {
    font-size: 10.5px;
    color: var(--ink-4);
    font-weight: 700;
    margin-top: 2px;
}
.plan-foot {
    text-align: center;
    margin-top: 10px;
    font-size: 11px;
    color: var(--ink-4);
    font-weight: 700;
}

.roi-card {
    background: linear-gradient(135deg, rgba(13,148,136,.08), rgba(13,148,136,.02));
    border: 1px solid rgba(13,148,136,.20);
    border-radius: 14px;
    padding: 14px;
}

.value-row {
    display: flex; gap: 12px;
    padding: 12px;
    background: #FAFBFC;
    border-radius: 12px;
}
.value-ico {
    width: 36px; height: 36px;
    border-radius: 11px;
    background: white;
    display: grid; place-items: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px -4px rgba(0,27,42,.10);
}

.faq-item {
    background: white;
    border: 1px solid var(--line);
    border-radius: 11px;
    padding: 14px 16px;
    margin-bottom: 8px;
}
.faq-item summary {
    font-weight: 800;
    font-size: 13.5px;
    cursor: pointer;
    list-style: none;
    position: relative;
    padding-left: 24px;
}
.faq-item summary::-webkit-details-marker { display: none; }
.faq-item summary::after {
    content: "+";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    font-weight: 800;
    color: var(--teal);
    line-height: 1;
    transition: transform .2s ease;
}
.faq-item[open] summary::after { transform: translateY(-50%) rotate(45deg); }
.faq-item p {
    color: var(--ink-3);
    font-size: 12.5px;
    line-height: 1.8;
    margin: 10px 0 0;
}
</style>

<script>
(function () {
    var tabs = document.querySelectorAll('.billing-tab');
    if (!tabs.length) return;

    function setCycle(cycle) {
        tabs.forEach(function (t) {
            t.classList.toggle('is-active', t.dataset.cycle === cycle);
        });
        document.querySelectorAll('.plan-price-block').forEach(function (block) {
            var monthly = block.dataset.monthly;
            var yearly  = block.dataset.yearly;
            var amount  = block.querySelector('.plan-amount');
            var period  = block.querySelector('.plan-period');
            var note    = block.querySelector('.plan-yearly-note');

            if (cycle === 'yearly') {
                amount.textContent = new Intl.NumberFormat('ar-EG').format(yearly);
                period.textContent = '/ شهرياً · فاتورة سنوية';
                if (note) note.hidden = false;
            } else {
                amount.textContent = new Intl.NumberFormat('ar-EG').format(monthly);
                period.textContent = '/ شهرياً';
                if (note) note.hidden = true;
            }
        });
    }

    tabs.forEach(function (t) {
        t.addEventListener('click', function () {
            setCycle(t.dataset.cycle);
        });
    });
})();
</script>
@endsection
