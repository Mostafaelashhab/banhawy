<x-admin-layout title="طلب ملكية #{{ $claim->id }}">

    <div style="margin-bottom: 14px;">
        <a href="{{ route('admin.claims.index') }}" class="a-btn a-btn-line a-btn-sm">← رجوع</a>
    </div>

    @if(session('temp_password'))
        <div class="a-flash is-warn">
            تم إنشاء حساب جديد للمالك · كلمة المرور المؤقتة: <code style="background: white; padding: 2px 6px; border-radius: 6px; font-family: ui-monospace, monospace; direction: ltr; display: inline-block;">{{ session('temp_password') }}</code>
            <div style="font-size: 11.5px; margin-top: 4px;">احفظها وابعتها له (مش هتظهر تاني).</div>
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px;">

        <div class="a-card">
            <div class="a-card-title" style="margin-bottom: 14px;">تفاصيل الطلب</div>

            <table style="width: 100%; font-size: 13px;">
                <tr><td style="padding: 8px 0; color: var(--ink-3); width: 130px;">الاسم</td><td style="font-weight: 800;">{{ $claim->claimant_name }}</td></tr>
                <tr><td style="padding: 8px 0; color: var(--ink-3);">التليفون</td><td style="direction: ltr; text-align: right; font-family: ui-monospace, monospace;">{{ $claim->claimant_phone }}</td></tr>
                <tr><td style="padding: 8px 0; color: var(--ink-3);">الإيميل</td><td style="direction: ltr; text-align: right;">{{ $claim->claimant_email ?? '—' }}</td></tr>
                <tr><td style="padding: 8px 0; color: var(--ink-3);">حساب موجود</td>
                    <td>
                        @if($claim->user)
                            <a href="#" style="color: var(--teal); font-weight: 800;">{{ $claim->user->name }}</a>
                        @else
                            <span class="a-pill a-pill-gray">غير مسجّل · هيتم إنشاء حساب</span>
                        @endif
                    </td>
                </tr>
                <tr><td style="padding: 8px 0; color: var(--ink-3);">تاريخ الطلب</td><td>{{ $claim->created_at?->format('Y-m-d H:i') }}</td></tr>
                <tr><td style="padding: 8px 0; color: var(--ink-3);">الحالة</td>
                    <td>
                        @switch($claim->status)
                            @case('pending')   <span class="a-pill a-pill-amber">بانتظار المراجعة</span> @break
                            @case('approved')  <span class="a-pill a-pill-green">تمت الموافقة</span> @break
                            @case('rejected')  <span class="a-pill a-pill-red">مرفوضة</span> @break
                        @endswitch
                    </td>
                </tr>
            </table>

            @if($claim->message)
                <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--line);">
                    <div style="font-size: 11.5px; color: var(--ink-3); font-weight: 800; margin-bottom: 6px;">رسالة المتقدّم</div>
                    <p style="font-size: 13px; line-height: 1.7; margin: 0;">{{ $claim->message }}</p>
                </div>
            @endif

            @if($claim->admin_note)
                <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--line);">
                    <div style="font-size: 11.5px; color: var(--ink-3); font-weight: 800; margin-bottom: 6px;">ملاحظة الأدمن</div>
                    <p style="font-size: 13px; line-height: 1.7; margin: 0; background: #FAFBFC; padding: 10px; border-radius: 8px;">{{ $claim->admin_note }}</p>
                </div>
            @endif
        </div>

        <div class="a-card">
            <div class="a-card-title" style="margin-bottom: 14px;">النشاط المطلوب</div>
            @if($claim->business)
                <div style="font-weight: 900; font-size: 15px;">{{ $claim->business->name }}</div>
                <div style="font-size: 12px; color: var(--ink-3); margin-top: 4px;">{{ $claim->business->category }}</div>
                <div style="font-size: 12px; margin-top: 8px;">{{ $claim->business->address }}</div>
                <div style="margin-top: 12px;">
                    @if($claim->business->owner)
                        <span class="a-pill a-pill-red">له مالك بالفعل</span>
                        <div style="font-size: 12px; margin-top: 6px;">{{ $claim->business->owner->name }}</div>
                    @else
                        <span class="a-pill a-pill-amber">بدون مالك حالياً</span>
                    @endif
                </div>
                <div style="margin-top: 12px;">
                    <a href="{{ route('admin.businesses.edit', $claim->business) }}" class="a-btn a-btn-line a-btn-sm">تحرير النشاط</a>
                    <a href="{{ route('business.show', $claim->business) }}" target="_blank" class="a-btn a-btn-line a-btn-sm">عرض</a>
                </div>
            @else
                <p>النشاط محذوف.</p>
            @endif
        </div>
    </div>

    @if($claim->status === 'pending' && $claim->business && ! $claim->business->owner_id)
        <div class="a-card" style="margin-top: 16px;">
            <div class="a-card-title" style="margin-bottom: 12px;">قرار المراجعة</div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <form method="post" action="{{ route('admin.claims.approve', $claim) }}" onsubmit="return confirm('تأكيد اعتماد الطلب وإسناد ملكية النشاط؟')">
                    @csrf
                    <div class="a-form-row">
                        <label>ملاحظة (اختياري)</label>
                        <textarea name="admin_note" rows="2" placeholder="ملاحظة داخلية..."></textarea>
                    </div>
                    <button type="submit" class="a-btn a-btn-teal" style="width: 100%; padding: 11px;">✓ اعتماد وإسناد الملكية</button>
                </form>

                <form method="post" action="{{ route('admin.claims.reject', $claim) }}" onsubmit="return confirm('تأكيد رفض الطلب؟')">
                    @csrf
                    <div class="a-form-row">
                        <label>سبب الرفض (اختياري)</label>
                        <textarea name="admin_note" rows="2" placeholder="السبب يفيد المتقدّم لاحقاً..."></textarea>
                    </div>
                    <button type="submit" class="a-btn a-btn-danger" style="width: 100%; padding: 11px;">✕ رفض الطلب</button>
                </form>
            </div>
        </div>
    @endif

</x-admin-layout>
