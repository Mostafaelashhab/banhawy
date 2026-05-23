<x-admin-layout title="المتاجر">

    <form method="get" class="a-card" style="margin-bottom: 16px;">
        <div style="display: grid; grid-template-columns: 1fr 200px 200px auto; gap: 10px; align-items: end;">
            <div class="a-form-row" style="margin: 0;">
                <label>بحث</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="اسم، تليفون، slug...">
            </div>
            <div class="a-form-row" style="margin: 0;">
                <label>النوع</label>
                <select name="type">
                    <option value="">الكل</option>
                    @foreach($types as $t)
                        <option value="{{ $t->slug }}" @selected($type === $t->slug)>{{ $t->name_ar }}</option>
                    @endforeach
                </select>
            </div>
            <div class="a-form-row" style="margin: 0;">
                <label>الحالة</label>
                <select name="status">
                    <option value="">الكل</option>
                    <option value="active"    @selected($status === 'active')>نشط</option>
                    <option value="inactive"  @selected($status === 'inactive')>معطّل</option>
                    <option value="unclaimed" @selected($status === 'unclaimed')>بدون مالك</option>
                    <option value="verified"  @selected($status === 'verified')>موثّق</option>
                    <option value="featured"  @selected($status === 'featured')>مميّز</option>
                </select>
            </div>
            <button class="a-btn a-btn-primary" style="padding: 10px 18px;">بحث</button>
        </div>
    </form>

    <div class="a-table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>النوع</th>
                    <th>المالك</th>
                    <th>الحالة</th>
                    <th>التقييم</th>
                    <th>زيارات</th>
                    <th>أضيف</th>
                    <th style="text-align: left;">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($businesses as $b)
                    <tr>
                        <td>
                            <a href="{{ route('admin.businesses.edit', $b) }}" style="color: var(--ink-1); font-weight: 800; text-decoration: none;">{{ $b->name }}</a>
                            <div style="font-size: 11px; color: var(--ink-4); font-weight: 700; direction: ltr; text-align: right;">{{ $b->slug }}</div>
                        </td>
                        <td>{{ $b->type?->name_ar ?? '—' }}</td>
                        <td>
                            @if($b->owner)
                                <span style="font-weight: 800;">{{ $b->owner->name }}</span>
                            @else
                                <span class="a-pill a-pill-amber">بدون مالك</span>
                            @endif
                        </td>
                        <td>
                            @if($b->is_active)
                                <span class="a-pill a-pill-green">نشط</span>
                            @else
                                <span class="a-pill a-pill-gray">معطّل</span>
                            @endif
                            @if($b->is_verified)<span class="a-pill a-pill-teal">موثّق</span>@endif
                            @if($b->is_featured)<span class="a-pill a-pill-blue">مميّز</span>@endif
                        </td>
                        <td>★ {{ number_format($b->rating, 1) }} <span style="color: var(--ink-4);">({{ $b->reviews_count }})</span></td>
                        <td>{{ number_format($b->views_count) }}</td>
                        <td>{{ $b->created_at?->diffForHumans() }}</td>
                        <td style="text-align: left;">
                            <a href="{{ route('admin.businesses.edit', $b) }}" class="a-btn a-btn-line a-btn-sm">تحرير</a>
                            <a href="{{ route('business.show', $b) }}" target="_blank" class="a-btn a-btn-line a-btn-sm">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="a-empty">مفيش متاجر بهذه الفلاتر</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $businesses->links() }}

</x-admin-layout>
