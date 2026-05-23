@extends('layouts.mobile')

@section('title', 'التحليلات · ' . $business->name)
@section('page-title', 'التحليلات')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('merchant.dashboard') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">التحليلات</div>
    <button class="ico-btn"><x-icon name="download" :size="18"/></button>
</div>

<div style="padding: 0 14px 10px; display: flex; gap: 6px;">
    @foreach([7 => '٧ أيام', 30 => '٣٠ يوم', 90 => '٩٠ يوم'] as $value => $label)
        <a href="{{ route('merchant.analytics', ['days' => $value]) }}" class="chip @if($days === $value) active @endif">{{ $label }}</a>
    @endforeach
</div>

<div class="scroll" style="padding: 4px 14px 14px;">
    {{-- Chart card --}}
    <div class="card" style="padding: 12px; margin-bottom: 10px;">
        <div>
            <div class="label-meta">الزيارات آخر {{ $days }} يوم</div>
            <div style="font-weight: 900; font-size: 22px; color: var(--navy); margin-top: 2px;">{{ number_format($totals['views']) }}</div>
        </div>

        @php
            $max = max(1, collect($series)->max('value'));
            $w = 280; $h = 100;
            $step = $w / max(1, count($series) - 1);
            $points = collect($series)->map(function ($p, $i) use ($step, $h, $max) {
                $x = $i * $step;
                $y = $h - 10 - (($p['value'] / $max) * ($h - 20));
                return [$x, $y];
            });
            $linePath = $points->map(fn($p, $i) => ($i === 0 ? 'M' : 'L') . round($p[0],1) . ',' . round($p[1],1))->implode(' ');
            $areaPath = $linePath . ' L' . $w . ',' . $h . ' L0,' . $h . ' Z';
        @endphp

        <svg viewBox="0 0 {{ $w }} {{ $h }}" style="width: 100%; height: 100px; margin-top: 12px;" preserveAspectRatio="none">
            <defs>
                <linearGradient id="gradTeal" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#0D9488" stop-opacity=".25"/>
                    <stop offset="100%" stop-color="#0D9488" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <line x1="0" y1="20" x2="{{ $w }}" y2="20" stroke="#ECEEF2" stroke-width="1" stroke-dasharray="2 4"/>
            <line x1="0" y1="50" x2="{{ $w }}" y2="50" stroke="#ECEEF2" stroke-width="1" stroke-dasharray="2 4"/>
            <line x1="0" y1="80" x2="{{ $w }}" y2="80" stroke="#ECEEF2" stroke-width="1" stroke-dasharray="2 4"/>
            <path d="{{ $areaPath }}" fill="url(#gradTeal)"/>
            <path d="{{ $linePath }}" fill="none" stroke="#0D9488" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            @if($points->isNotEmpty())
                @php $last = $points->last(); @endphp
                <circle cx="{{ round($last[0],1) }}" cy="{{ round($last[1],1) }}" r="4" fill="white" stroke="#0D9488" stroke-width="2.5"/>
            @endif
        </svg>

        <div style="display: flex; justify-content: space-between; margin-top: 4px;">
            <span class="tiny" style="color: var(--ink-4);">{{ \Carbon\Carbon::parse($series[0]['date'])->translatedFormat('j M') }}</span>
            <span class="tiny" style="color: var(--ink-4);">{{ \Carbon\Carbon::parse(end($series)['date'])->translatedFormat('j M') }}</span>
        </div>
    </div>

    {{-- Mini stats --}}
    <div class="stat-grid" style="margin-bottom: 16px;">
        <div class="card" style="padding: 10px;">
            <div class="label-meta">ضغطات واتساب</div>
            <div style="font-weight: 900; font-size: 17px; color: var(--navy);">{{ number_format($totals['clicks']) }}</div>
        </div>
        <div class="card" style="padding: 10px;">
            <div class="label-meta">الطلبات</div>
            <div style="font-weight: 900; font-size: 17px; color: var(--navy);">{{ number_format($totals['orders']) }}</div>
        </div>
        <div class="card" style="padding: 10px;">
            <div class="label-meta">الحجوزات</div>
            <div style="font-weight: 900; font-size: 17px; color: var(--navy);">{{ number_format($totals['bookings']) }}</div>
        </div>
        <div class="card" style="padding: 10px;">
            <div class="label-meta">معدل التحويل</div>
            <div style="font-weight: 900; font-size: 17px; color: var(--navy);">{{ $totals['conversion'] }}%</div>
        </div>
    </div>

    {{-- Top items --}}
    @if($topProducts->isNotEmpty())
        <div class="card" style="padding: 12px;">
            <div class="label-strong" style="margin-bottom: 8px;">الأصناف الأكثر طلبًا</div>
            @php $maxQty = $topProducts->max(); @endphp
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach($topProducts as $name => $qty)
                    @php $width = $maxQty > 0 ? ($qty / $maxQty) * 100 : 0; @endphp
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 16px; text-align: center; font-weight: 900; font-size: 12px; color: {{ $loop->first ? 'var(--teal)' : 'var(--ink-3)' }};">{{ $loop->iteration }}</span>
                        <div style="flex: 1;">
                            <div style="font-size: 12px; font-weight: 700;">{{ $name }}</div>
                            <div style="height: 4px; background: var(--gray-100); border-radius: 2px; margin-top: 4px;">
                                <div style="height: 100%; width: {{ $width }}%; background: {{ $loop->first ? 'var(--teal)' : 'var(--navy)' }}; border-radius: 2px;"></div>
                            </div>
                        </div>
                        <span class="tiny" style="font-weight: 800;">{{ $qty }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@include('partials.merchant-nav')
@endsection
