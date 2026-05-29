<x-admin-layout title="تنبيهات الطريق">

    <div class="a-filters">
        @foreach(['active' => 'نشطة', 'expired' => 'منتهية', 'rejected' => 'مرفوضة'] as $key => $label)
            <a href="{{ route('admin.alerts.index', ['status' => $key]) }}" class="a-chip @if($status === $key) is-active @endif">{{ $label }}</a>
        @endforeach
        <span style="margin: 0 8px; color: var(--ink-4);">·</span>
        @foreach(\App\Models\RoadAlert::TYPES as $slug => $cfg)
            <a href="{{ route('admin.alerts.index', ['status' => $status, 'type' => $slug]) }}" class="a-chip @if($type === $slug) is-active @endif">{{ $cfg['icon'] }} {{ $cfg['chip_label'] }}</a>
        @endforeach
    </div>

    <div class="a-table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>النوع</th>
                    <th>الوصف</th>
                    <th>الموقع</th>
                    <th>الحالة</th>
                    <th>تأكيد/نفي</th>
                    <th>أنشئ</th>
                    <th>ينتهي</th>
                    <th style="text-align: left;">إجراء</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $a)
                    @php $cfg = \App\Models\RoadAlert::TYPES[$a->type] ?? null; @endphp
                    <tr>
                        <td>
                            <span style="display: inline-flex; align-items: center; gap: 6px;">
                                <span style="width: 22px; height: 22px; border-radius: 50%; background: {{ $cfg['color'] ?? '#94A1AE' }}; color: white; display: grid; place-items: center; font-size: 11px;">{{ $cfg['icon'] ?? '📍' }}</span>
                                <span style="font-weight: 800;">{{ $cfg['label_ar'] ?? $a->type }}</span>
                            </span>
                        </td>
                        <td style="max-width: 320px;">
                            <div style="font-size: 12.5px; line-height: 1.6; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                {{ $a->description ?? '—' }}
                            </div>
                        </td>
                        <td style="direction: ltr; text-align: right; font-family: ui-monospace, monospace; font-size: 11px;">
                            <a href="https://www.google.com/maps/?q={{ $a->lat }},{{ $a->lng }}" target="_blank">{{ number_format($a->lat, 4) }},{{ number_format($a->lng, 4) }}</a>
                        </td>
                        <td>
                            @switch($a->status)
                                @case('active')   <span class="a-pill a-pill-green">نشطة</span> @break
                                @case('expired')  <span class="a-pill a-pill-gray">منتهية</span> @break
                                @case('rejected') <span class="a-pill a-pill-red">مرفوضة</span> @break
                            @endswitch
                        </td>
                        <td>✓ {{ $a->confirmations_count }} · ✕ {{ $a->rejections_count }}</td>
                        <td>{{ $a->created_at?->diffForHumans() }}</td>
                        <td>{{ $a->expires_at?->diffForHumans() }}</td>
                        <td style="text-align: left;">
                            <form method="post" action="{{ route('admin.alerts.update', $a) }}" style="display: inline;">
                                @csrf @method('PATCH')
                                <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 7px; border: 1px solid var(--line); font-family: inherit; font-size: 11.5px; font-weight: 800;">
                                    <option value="active"   @selected($a->status === 'active')>نشطة</option>
                                    <option value="expired"  @selected($a->status === 'expired')>منتهية</option>
                                    <option value="rejected" @selected($a->status === 'rejected')>مرفوضة</option>
                                </select>
                            </form>
                            <form method="post" action="{{ route('admin.alerts.destroy', $a) }}" onsubmit="return confirm('حذف نهائي؟')" style="display: inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="a-btn a-btn-line a-btn-sm" style="color: #B91C1C;">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="a-empty">مفيش تنبيهات بهذه الفلاتر</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $alerts->links() }}

</x-admin-layout>
