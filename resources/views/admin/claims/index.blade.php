<x-admin-layout title="طلبات الملكية">

    <div class="a-filters">
        @foreach(['pending' => 'بانتظار', 'approved' => 'موافقة', 'rejected' => 'مرفوضة', 'all' => 'الكل'] as $key => $label)
            <a href="{{ route('admin.claims.index', ['status' => $key]) }}" class="a-chip @if($status === $key) is-active @endif">{{ $label }}</a>
        @endforeach
    </div>

    <div class="a-table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>المتقدّم</th>
                    <th>التليفون</th>
                    <th>النشاط</th>
                    <th>الحالة</th>
                    <th>التاريخ</th>
                    <th style="text-align: left;">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($claims as $c)
                    <tr>
                        <td>
                            <div style="font-weight: 800;">{{ $c->claimant_name }}</div>
                            @if($c->claimant_email)
                                <div style="font-size: 11.5px; color: var(--ink-3); direction: ltr; text-align: right;">{{ $c->claimant_email }}</div>
                            @endif
                        </td>
                        <td style="direction: ltr; text-align: right; font-family: ui-monospace, monospace;">{{ $c->claimant_phone }}</td>
                        <td>
                            @if($c->business)
                                <a href="{{ route('admin.businesses.edit', $c->business) }}" style="color: var(--ink-1); font-weight: 800; text-decoration: none;">{{ $c->business->name }}</a>
                            @else
                                <span style="color: var(--ink-4);">— محذوف —</span>
                            @endif
                        </td>
                        <td>
                            @switch($c->status)
                                @case('pending')   <span class="a-pill a-pill-amber">بانتظار</span> @break
                                @case('approved')  <span class="a-pill a-pill-green">موافقة</span> @break
                                @case('rejected')  <span class="a-pill a-pill-red">مرفوضة</span> @break
                            @endswitch
                        </td>
                        <td>{{ $c->created_at?->diffForHumans() }}</td>
                        <td style="text-align: left;">
                            <a href="{{ route('admin.claims.show', $c) }}" class="a-btn a-btn-line a-btn-sm">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="a-empty">مفيش طلبات ✓</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $claims->links() }}

</x-admin-layout>
