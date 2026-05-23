@extends('layouts.mobile')

@section('title', 'مراجعة الطلب · ' . $business->name)
@section('shell-class', 'no-bnav')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('business.menu', $business) }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">مراجعة الطلب</div>
</div>

<form method="post" action="{{ route('business.order.place', $business) }}" class="scroll" style="padding: 4px 14px 110px; display: flex; flex-direction: column; gap: 10px;">
    @csrf

    {{-- WhatsApp note --}}
    <div style="background: rgba(37,211,102,.08); border: 1px solid rgba(37,211,102,.25); border-radius: 14px; padding: 10px 12px; display: flex; gap: 10px; align-items: flex-start;">
        <span style="color: var(--wa-600); flex-shrink: 0; margin-top: 1px;"><x-icon name="whatsapp" :size="18" stroke="#1FB855" w="2"/></span>
        <div style="font-size: 11px; line-height: 1.6; color: var(--gray-700); font-weight: 600;">
            هيتم إرسال تفاصيل الطلب لصاحب النشاط على واتساب — هيتواصل معاك لتأكيد الطلب والتوصيل.
        </div>
    </div>

    {{-- Items --}}
    <div class="card" style="padding: 12px;">
        <div class="label-strong" style="margin-bottom: 10px;">الأصناف</div>
        @forelse($cart as $i => $item)
            <input type="hidden" name="items[{{ $i }}][id]"  value="{{ $item['product_id'] }}">
            <input type="hidden" name="items[{{ $i }}][qty]" value="{{ $item['qty'] }}">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="ph ph-{{ $business->type->slug }}" style="width: 36px; height: 36px; border-radius: 10px; font-size: 10px;">
                        {{ mb_substr($item['name'], 0, 2) }}
                    </div>
                    <div>
                        <div style="font-size: 12px; font-weight: 700;">{{ $item['name'] }}</div>
                        <div class="label-meta">×{{ $item['qty'] }} · {{ $item['price'] }}ج</div>
                    </div>
                </div>
                <span style="font-weight: 800; font-size: 12px;">{{ $item['line_total'] }}ج</span>
            </div>
            @if(! $loop->last) <div class="sep" style="margin: 4px 0;"></div> @endif
        @empty
            <div class="muted" style="text-align: center; padding: 16px;">السلة فارغة. <a href="{{ route('business.menu', $business) }}" style="color: var(--teal); font-weight: 800;">ارجع للمنيو</a></div>
        @endforelse
    </div>

    {{-- Customer info --}}
    <div class="card" style="padding: 12px;">
        <div class="label-strong" style="margin-bottom: 8px;">بيانات التواصل</div>
        <div style="margin-bottom: 8px;">
            <label class="field-label">الاسم</label>
            <div class="field">
                <input type="text" name="customer_name" placeholder="اسمك بالكامل" required>
            </div>
            @error('customer_name') <div class="tiny" style="color: #B91C1C; margin-top: 4px;">{{ $message }}</div> @enderror
        </div>
        <div style="margin-bottom: 8px;">
            <label class="field-label">رقم الموبايل</label>
            <div class="field">
                <input type="tel" name="customer_phone" placeholder="+20 1xx xxx xxxx" required dir="ltr" style="text-align: right;">
            </div>
            @error('customer_phone') <div class="tiny" style="color: #B91C1C; margin-top: 4px;">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="field-label">ملاحظات (اختياري)</label>
            <div class="field">
                <input type="text" name="notes" placeholder="مثلًا: من غير بصل">
            </div>
        </div>
    </div>

    {{-- Total --}}
    <div class="card" style="padding: 12px;">
        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: var(--ink-3);">
            <span>المجموع الفرعي</span><span>{{ $subtotal }}ج</span>
        </div>
        @if($delivery > 0)
            <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 600; color: var(--ink-3); margin-top: 4px;">
                <span>التوصيل</span><span>{{ $delivery }}ج</span>
            </div>
        @endif
        <div class="sep" style="margin: 8px 0;"></div>
        <div style="display: flex; justify-content: space-between;">
            <span class="label-strong">الإجمالي</span>
            <span style="font-weight: 900; font-size: 16px; color: var(--navy);">{{ $total }}ج</span>
        </div>
    </div>

    <div style="position: sticky; bottom: 0; padding: 10px 0 0; background: var(--gray-100); display: flex; flex-direction: column; gap: 8px;">
        @if($business->orders_via === 'whatsapp')
            {{-- WhatsApp only --}}
            <input type="hidden" name="channel" value="whatsapp">
            <button type="submit" class="btn btn-wa btn-full" style="padding: 14px; font-size: 14px;" @if(empty($cart)) disabled @endif>
                <x-icon name="whatsapp" :size="16" stroke="white" w="2"/>
                إرسال الطلب عبر واتساب
            </button>
        @elseif($business->orders_via === 'web')
            {{-- Web only --}}
            <input type="hidden" name="channel" value="web">
            <button type="submit" class="btn btn-navy btn-full" style="padding: 14px; font-size: 14px;" @if(empty($cart)) disabled @endif>
                <x-icon name="cart" :size="16" stroke="white"/>
                تأكيد الطلب
            </button>
            <p class="tiny" style="text-align: center; color: var(--ink-3); padding: 4px 8px; line-height: 1.6;">
                الطلب هيتسجل مباشرة عند صاحب النشاط · هيتواصل معاك على الرقم اللي فوق.
            </p>
        @else
            {{-- Both — customer chooses --}}
            <button type="submit" name="channel" value="whatsapp" class="btn btn-wa btn-full" style="padding: 14px; font-size: 14px;" @if(empty($cart)) disabled @endif>
                <x-icon name="whatsapp" :size="16" stroke="white" w="2"/>
                إرسال الطلب عبر واتساب
            </button>
            <button type="submit" name="channel" value="web" class="btn btn-line btn-full" style="padding: 12px; font-size: 13px;" @if(empty($cart)) disabled @endif>
                <x-icon name="cart" :size="14"/>
                تأكيد الطلب من غير واتساب
            </button>
        @endif
    </div>
</form>
@endsection
