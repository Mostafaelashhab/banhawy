<x-admin-layout title="المفقودات">

    <div class="a-filters">
        @foreach(['open' => 'مفتوحة', 'resolved' => 'تم حلها', 'expired' => 'منتهية', 'all' => 'الكل'] as $key => $label)
            <a href="{{ route('admin.lost.index', ['status' => $key]) }}" class="a-chip @if($status === $key) is-active @endif">{{ $label }}</a>
        @endforeach
        <span style="margin: 0 8px; color: var(--ink-4);">·</span>
        @foreach(['lost' => 'ضايع', 'found' => 'موجود'] as $k => $v)
            <a href="{{ route('admin.lost.index', ['status' => $status, 'kind' => $k]) }}" class="a-chip @if($kind === $k) is-active @endif">{{ $v }}</a>
        @endforeach
    </div>

    <form method="get" class="a-card" style="margin-bottom: 16px;">
        <input type="hidden" name="status" value="{{ $status }}">
        @if($kind)<input type="hidden" name="kind" value="{{ $kind }}">@endif
        <div style="display: grid; grid-template-columns: 1fr 200px auto; gap: 10px; align-items: end;">
            <div class="a-form-row" style="margin: 0;">
                <label>بحث</label>
                <input type="search" name="q" value="{{ $q }}" placeholder="عنوان، تفاصيل...">
            </div>
            <div class="a-form-row" style="margin: 0;">
                <label>التصنيف</label>
                <select name="category">
                    <option value="">الكل</option>
                    @foreach(\App\Models\LostItem::CATEGORIES as $k => $v)
                        <option value="{{ $k }}" @selected($category === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <button class="a-btn a-btn-primary" style="padding: 10px 18px;">تصفية</button>
        </div>
    </form>

    <div class="a-table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>البلاغ</th>
                    <th>النوع</th>
                    <th>التصنيف</th>
                    <th>المنشِر</th>
                    <th>تليفون</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th style="text-align: left;">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                @if($item->image)
                                    <img src="{{ $item->image }}" style="width: 44px; height: 44px; object-fit: cover; border-radius: 8px; flex-shrink: 0;" referrerpolicy="no-referrer">
                                @endif
                                <a href="{{ route('lost.show', $item) }}" target="_blank" style="color: var(--ink-1); font-weight: 800; text-decoration: none;">
                                    {{ Str::limit($item->title, 40) }}
                                </a>
                            </div>
                        </td>
                        <td>
                            @if($item->kind === 'lost')
                                <span class="a-pill a-pill-red">ضايع</span>
                            @else
                                <span class="a-pill a-pill-green">موجود</span>
                            @endif
                        </td>
                        <td>{{ \App\Models\LostItem::CATEGORIES[$item->category] ?? $item->category }}</td>
                        <td>{{ $item->user?->name ?? $item->contact_name }}</td>
                        <td style="direction: ltr; text-align: right; font-family: ui-monospace, monospace; font-size: 11.5px;">{{ $item->contact_phone }}</td>
                        <td>
                            @switch($item->status)
                                @case('open')     <span class="a-pill a-pill-teal">مفتوح</span> @break
                                @case('resolved') <span class="a-pill a-pill-green">انتهى</span> @break
                                @case('expired')  <span class="a-pill a-pill-gray">منتهي</span> @break
                            @endswitch
                        </td>
                        <td>{{ $item->created_at?->diffForHumans() }}</td>
                        <td style="text-align: left;">
                            <form method="post" action="{{ route('admin.lost.update', $item) }}" style="display: inline;">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 7px; border: 1px solid var(--line); font-family: inherit; font-size: 11.5px; font-weight: 800;">
                                    <option value="open"     @selected($item->status === 'open')>مفتوح</option>
                                    <option value="resolved" @selected($item->status === 'resolved')>انتهى</option>
                                    <option value="expired"  @selected($item->status === 'expired')>منتهي</option>
                                </select>
                            </form>
                            <form method="post" action="{{ route('admin.lost.destroy', $item) }}" onsubmit="return confirm('حذف البلاغ نهائياً؟')" style="display: inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="a-btn a-btn-line a-btn-sm" style="color: #B91C1C;">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="a-empty">مفيش بلاغات بهذه الفلاتر</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $items->links() }}

</x-admin-layout>
