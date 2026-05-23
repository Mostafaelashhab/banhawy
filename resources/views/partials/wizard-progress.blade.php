@props(['step' => 1])
<div style="padding: 4px 14px 16px;">
    <div style="display: flex; gap: 4px;">
        @for($i = 1; $i <= 4; $i++)
            <span style="flex: 1; height: 4px; border-radius: 2px; background: {{ $i <= $step ? 'var(--teal)' : 'var(--gray-200)' }};"></span>
        @endfor
    </div>
    <div style="display: flex; justify-content: space-between; margin-top: 6px;">
        <span class="tiny" style="color: var(--teal);">الخطوة {{ $step }} من ٤</span>
        <span class="tiny" style="color: var(--ink-3);">{{ intval(($step / 4) * 100) }}%</span>
    </div>
</div>
