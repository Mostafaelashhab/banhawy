<x-admin-layout title="بلاغ #{{ $report->id }}">

    <div style="margin-bottom: 14px;">
        <a href="{{ route('admin.reports.index') }}" class="a-btn a-btn-line a-btn-sm">← رجوع</a>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px;">

        <div class="a-card">
            <div class="a-card-title" style="margin-bottom: 14px;">تفاصيل البلاغ</div>

            <table style="width: 100%; font-size: 13px;">
                <tr><td style="padding: 8px 0; color: var(--ink-3); width: 130px;">السبب</td>
                    <td style="font-weight: 800;">{{ \App\Models\BusinessReport::REASONS[$report->reason] ?? $report->reason }}</td></tr>
                <tr><td style="padding: 8px 0; color: var(--ink-3);">تليفون</td><td style="direction: ltr; text-align: right; font-family: ui-monospace, monospace;">{{ $report->reporter_phone ?? '—' }}</td></tr>
                <tr><td style="padding: 8px 0; color: var(--ink-3);">إيميل</td><td style="direction: ltr; text-align: right;">{{ $report->reporter_email ?? '—' }}</td></tr>
                <tr><td style="padding: 8px 0; color: var(--ink-3);">المستخدم</td>
                    <td>{{ $report->user?->name ?? 'زائر مجهول' }}</td>
                </tr>
                <tr><td style="padding: 8px 0; color: var(--ink-3);">التاريخ</td><td>{{ $report->created_at?->format('Y-m-d H:i') }}</td></tr>
                <tr><td style="padding: 8px 0; color: var(--ink-3);">الحالة</td>
                    <td>
                        @switch($report->status)
                            @case('pending')   <span class="a-pill a-pill-amber">بانتظار</span> @break
                            @case('reviewed')  <span class="a-pill a-pill-blue">تمت مراجعتها</span> @break
                            @case('actioned')  <span class="a-pill a-pill-green">تم إجراء</span> @break
                            @case('dismissed') <span class="a-pill a-pill-gray">مرفوضة</span> @break
                        @endswitch
                    </td>
                </tr>
            </table>

            @if($report->details)
                <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--line);">
                    <div style="font-size: 11.5px; color: var(--ink-3); font-weight: 800; margin-bottom: 6px;">تفاصيل المُبلِغ</div>
                    <p style="font-size: 13px; line-height: 1.7; margin: 0; background: #FAFBFC; padding: 12px; border-radius: 8px;">{{ $report->details }}</p>
                </div>
            @endif

            <form method="post" action="{{ route('admin.reports.update', $report) }}" style="margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--line);">
                @csrf @method('PATCH')

                <div class="a-form-row">
                    <label>تحديث الحالة</label>
                    <select name="status">
                        <option value="pending"   @selected($report->status === 'pending')>بانتظار</option>
                        <option value="reviewed"  @selected($report->status === 'reviewed')>تمت مراجعته</option>
                        <option value="actioned"  @selected($report->status === 'actioned')>تم اتخاذ إجراء</option>
                        <option value="dismissed" @selected($report->status === 'dismissed')>مرفوض (غير صحيح)</option>
                    </select>
                </div>
                <div class="a-form-row">
                    <label>ملاحظة داخلية</label>
                    <textarea name="admin_note" rows="2" placeholder="ملاحظة...">{{ $report->admin_note }}</textarea>
                </div>
                <button type="submit" class="a-btn a-btn-primary">حفظ الحالة</button>
            </form>
        </div>

        <div class="a-card">
            <div class="a-card-title" style="margin-bottom: 14px;">النشاط المُبلَّغ عنه</div>
            @if($report->business)
                <div style="font-weight: 900; font-size: 15px;">{{ $report->business->name }}</div>
                <div style="font-size: 12px; color: var(--ink-3); margin-top: 4px;">{{ $report->business->category }}</div>
                <div style="font-size: 12px; margin-top: 8px;">{{ $report->business->address }}</div>

                <div style="margin-top: 14px; display: flex; gap: 6px; flex-wrap: wrap;">
                    <a href="{{ route('admin.businesses.edit', $report->business) }}" class="a-btn a-btn-line a-btn-sm">تحرير</a>
                    <a href="{{ route('business.show', $report->business) }}" target="_blank" class="a-btn a-btn-line a-btn-sm">عرض</a>
                </div>

                <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--line); font-size: 12px; color: var(--ink-3);">
                    <div>عدد البلاغات الكلي: <strong>{{ \App\Models\BusinessReport::where('business_id', $report->business->id)->count() }}</strong></div>
                </div>
            @else
                <p>النشاط محذوف.</p>
            @endif
        </div>
    </div>

</x-admin-layout>
