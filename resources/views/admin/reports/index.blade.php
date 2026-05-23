<x-admin-layout title="البلاغات">

    <div class="a-filters">
        @foreach(['pending' => 'بانتظار', 'reviewed' => 'تمت مراجعتها', 'actioned' => 'تم اتخاذ إجراء', 'dismissed' => 'مرفوضة', 'all' => 'الكل'] as $key => $label)
            <a href="{{ route('admin.reports.index', ['status' => $key]) }}" class="a-chip @if($status === $key) is-active @endif">{{ $label }}</a>
        @endforeach
    </div>

    <div class="a-table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>السبب</th>
                    <th>النشاط</th>
                    <th>الحالة</th>
                    <th>التليفون</th>
                    <th>التاريخ</th>
                    <th style="text-align: left;">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                    <tr>
                        <td>
                            <div style="font-weight: 800;">{{ \App\Models\BusinessReport::REASONS[$r->reason] ?? $r->reason }}</div>
                            @if($r->details)
                                <div style="font-size: 11.5px; color: var(--ink-3); max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $r->details }}</div>
                            @endif
                        </td>
                        <td>
                            @if($r->business)
                                <a href="{{ route('admin.businesses.edit', $r->business) }}" style="color: var(--ink-1); font-weight: 800; text-decoration: none;">{{ $r->business->name }}</a>
                            @else
                                <span style="color: var(--ink-4);">— محذوف —</span>
                            @endif
                        </td>
                        <td>
                            @switch($r->status)
                                @case('pending')   <span class="a-pill a-pill-amber">بانتظار</span> @break
                                @case('reviewed')  <span class="a-pill a-pill-blue">تمت مراجعتها</span> @break
                                @case('actioned')  <span class="a-pill a-pill-green">تم إجراء</span> @break
                                @case('dismissed') <span class="a-pill a-pill-gray">مرفوضة</span> @break
                            @endswitch
                        </td>
                        <td style="direction: ltr; text-align: right; font-family: ui-monospace, monospace;">{{ $r->reporter_phone ?? '—' }}</td>
                        <td>{{ $r->created_at?->diffForHumans() }}</td>
                        <td style="text-align: left;">
                            <a href="{{ route('admin.reports.show', $r) }}" class="a-btn a-btn-line a-btn-sm">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="a-empty">مفيش بلاغات ✓</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $reports->links() }}

</x-admin-layout>
