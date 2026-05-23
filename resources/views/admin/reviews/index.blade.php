<x-admin-layout title="التقييمات">

    <form method="get" class="a-card" style="margin-bottom: 16px;">
        <div style="display: grid; grid-template-columns: 1fr 120px 120px auto; gap: 10px; align-items: end;">
            <div class="a-form-row" style="margin: 0;">
                <label>بحث في النص</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="ابحث في نص التقييم...">
            </div>
            <div class="a-form-row" style="margin: 0;">
                <label>أقل تقييم</label>
                <select name="min_rate">
                    <option value="">—</option>
                    @for($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected((int)$minRate === $i)>{{ $i }} ★</option>
                    @endfor
                </select>
            </div>
            <div class="a-form-row" style="margin: 0;">
                <label>أعلى تقييم</label>
                <select name="max_rate">
                    <option value="">—</option>
                    @for($i = 1; $i <= 5; $i++)
                        <option value="{{ $i }}" @selected((int)$maxRate === $i)>{{ $i }} ★</option>
                    @endfor
                </select>
            </div>
            <button class="a-btn a-btn-primary" style="padding: 10px 18px;">تصفية</button>
        </div>
    </form>

    <div class="a-table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>التقييم</th>
                    <th>النص</th>
                    <th>النشاط</th>
                    <th>التليفون</th>
                    <th>التاريخ</th>
                    <th style="text-align: left;">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $r)
                    <tr>
                        <td>
                            <span style="font-weight: 900;">{{ $r->rating }}</span>
                            <span style="color: #F59E0B;">★</span>
                        </td>
                        <td style="max-width: 360px;">
                            <div style="font-size: 12.5px; line-height: 1.6; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">{{ $r->body ?? '—' }}</div>
                        </td>
                        <td>
                            @if($r->business)
                                <a href="{{ route('admin.businesses.edit', $r->business) }}" style="color: var(--ink-1); font-weight: 800; text-decoration: none;">{{ $r->business->name }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td style="direction: ltr; text-align: right; font-family: ui-monospace, monospace; font-size: 11.5px;">
                            {{ $r->reviewer_phone ? '****'.substr($r->reviewer_phone, -4) : '—' }}
                        </td>
                        <td>{{ $r->created_at?->format('Y-m-d') }}</td>
                        <td style="text-align: left;">
                            <form method="post" action="{{ route('admin.reviews.destroy', $r) }}" onsubmit="return confirm('حذف هذا التقييم نهائياً؟')" style="display: inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="a-btn a-btn-line a-btn-sm" style="color: #B91C1C;">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="a-empty">مفيش تقييمات بهذه الفلاتر</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $reviews->links() }}

</x-admin-layout>
