@extends('layouts.mobile')

@section('title', 'رمز QR · ' . $business->name)
@section('page-title', 'رمز QR للمتجر')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('merchant.dashboard') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">رمز المتجر · QR</div>
</div>

<div class="scroll" style="padding: 8px 14px 14px;">

    <div style="background: white; border-radius: 22px; padding: 18px; box-shadow: var(--shadow); border: 1px solid var(--line);">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
            <div class="ph ph-{{ $business->type->slug }}" style="width: 34px; height: 34px; border-radius: 10px; font-size: 10px;">
                {{ mb_substr($business->name, 0, 2) }}
            </div>
            <div>
                <div style="font-weight: 900; font-size: 13px;">{{ $business->name }}</div>
                <div class="tiny" style="color: var(--ink-3);">امسح الرمز لفتح المتجر</div>
            </div>
        </div>

        <div style="background: white; border: 1px solid var(--line); border-radius: 16px; padding: 16px; display: grid; place-items: center;">
            @include('partials.qr-svg', [
                'size' => 240,
                'center' => mb_substr($business->name, 0, 2),
                'url' => $url,
            ])
        </div>

        <div style="text-align: center; margin-top: 14px;">
            <div class="tiny" style="color: var(--ink-3);">رابط متجرك</div>
            <div style="font-weight: 800; font-size: 12px; direction: ltr; color: var(--navy); margin-top: 3px; word-break: break-all;">{{ $url }}</div>
        </div>
    </div>

    <p class="tiny" style="text-align: center; color: var(--ink-3); margin: 14px 16px 16px; line-height: 1.6;">
        اطبع الرمز وحطه في المحل أو على المنيو — العميل يمسحه ويفتح متجرك فورًا.
    </p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
        <button onclick="downloadQr('png')" class="btn btn-navy" style="padding: 11px; font-size: 12px;">
            <x-icon name="download" :size="14" stroke="white" w="2.2"/> PNG
        </button>
        <button onclick="downloadQr('pdf')" class="btn btn-line" style="padding: 11px; font-size: 12px;">
            <x-icon name="download" :size="14"/> PDF
        </button>
        <button onclick="navigator.clipboard.writeText('{{ $url }}'); this.textContent='تم النسخ ✓'" class="btn btn-line" style="padding: 11px; font-size: 12px;">
            <x-icon name="copy" :size="14"/> نسخ الرابط
        </button>
        <a href="https://wa.me/?text={{ rawurlencode('شوف متجرنا على بنهاوي: ' . $url) }}" class="btn btn-wa" style="padding: 11px; font-size: 12px;">
            <x-icon name="whatsapp" :size="14" stroke="white"/> مشاركة
        </a>
    </div>
</div>

@include('partials.merchant-nav')

<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
(function () {
    function inFinder(r, c, n) {
        return (r < 7 && c < 7) || (r < 7 && c >= n - 7) || (r >= n - 7 && c < 7);
    }

    function buildFinder(x, y, fg) {
        // 7×7 finder pattern: rounded outer ring + rounded inner dot, drawn in module units
        return ''
            + '<rect x="' + x + '" y="' + y + '" width="7" height="7" rx="1.8" ry="1.8" fill="' + fg + '"/>'
            + '<rect x="' + (x + 1) + '" y="' + (y + 1) + '" width="5" height="5" rx="1.2" ry="1.2" fill="#fff"/>'
            + '<rect x="' + (x + 2) + '" y="' + (y + 2) + '" width="3" height="3" rx="0.7" ry="0.7" fill="' + fg + '"/>';
    }

    function render(el) {
        var url = el.dataset.qrUrl;
        var size = parseInt(el.dataset.qrSize, 10) || 220;
        var center = el.dataset.qrCenter || '';
        var fg = el.dataset.qrFg || '#001B2A';
        var accent = el.dataset.qrAccent || '#0D9488';

        if (!window.qrcode || !url) return;

        // typeNumber 0 = auto-fit, ECC level H so the center logo can safely overlap
        var qr = window.qrcode(0, 'H');
        qr.addData(url);
        qr.make();
        var n = qr.getModuleCount();

        // Carve a small clear zone in the middle for the logo (~18% of width)
        var logoModules = Math.max(5, Math.round(n * 0.18));
        if (logoModules % 2 === 0) logoModules += 1; // keep it centered on a module
        var logoStart = Math.floor((n - logoModules) / 2);
        var logoEnd = logoStart + logoModules;

        var pad = 2; // quiet-zone modules
        var vb = n + pad * 2;

        var parts = [
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + vb + ' ' + vb + '" width="' + size + '" height="' + size + '" shape-rendering="geometricPrecision">',
            '<rect width="' + vb + '" height="' + vb + '" fill="#fff"/>',
            '<g transform="translate(' + pad + ',' + pad + ')">'
        ];

        // Data modules as small rounded dots
        var dot = 0.78;
        var off = (1 - dot) / 2;
        var dots = '';
        for (var r = 0; r < n; r++) {
            for (var c = 0; c < n; c++) {
                if (inFinder(r, c, n)) continue;
                if (r >= logoStart && r < logoEnd && c >= logoStart && c < logoEnd) continue;
                if (!qr.isDark(r, c)) continue;
                dots += '<rect x="' + (c + off).toFixed(3) + '" y="' + (r + off).toFixed(3) + '" width="' + dot + '" height="' + dot + '" rx="' + (dot / 2.6).toFixed(3) + '" ry="' + (dot / 2.6).toFixed(3) + '"/>';
            }
        }
        parts.push('<g fill="' + fg + '">' + dots + '</g>');

        // Three rounded finder patterns
        parts.push(buildFinder(0, 0, fg));
        parts.push(buildFinder(n - 7, 0, fg));
        parts.push(buildFinder(0, n - 7, fg));

        // Center logo
        var lx = logoStart, ly = logoStart, lw = logoModules;
        var pad2 = 0.6;
        parts.push('<rect x="' + (lx - pad2) + '" y="' + (ly - pad2) + '" width="' + (lw + pad2 * 2) + '" height="' + (lw + pad2 * 2) + '" rx="1.6" fill="#fff"/>');
        parts.push('<rect x="' + lx + '" y="' + ly + '" width="' + lw + '" height="' + lw + '" rx="1.3" fill="' + accent + '"/>');
        if (center) {
            var safe = center.replace(/[&<>"]/g, function (ch) { return ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;' })[ch]; });
            parts.push('<text x="' + (lx + lw / 2) + '" y="' + (ly + lw / 2 + lw * 0.16) + '" font-size="' + (lw * 0.55) + '" font-weight="900" fill="#fff" text-anchor="middle" font-family="Cairo, sans-serif">' + safe + '</text>');
        }

        parts.push('</g></svg>');
        el.innerHTML = parts.join('');
    }

    function renderAll() {
        document.querySelectorAll('.qr-render').forEach(render);
    }

    if (window.qrcode) {
        renderAll();
    } else {
        var t = setInterval(function () {
            if (window.qrcode) { clearInterval(t); renderAll(); }
        }, 50);
        setTimeout(function () { clearInterval(t); }, 5000);
    }

    // Expose for downloads
    window.__qrRenderAll = renderAll;
})();

function downloadQr(format) {
    var host = document.querySelector('.qr-render');
    if (!host) return;
    var svg = host.querySelector('svg');
    if (!svg) { alert('الرمز لم يُحمَّل بعد، حاول بعد ثانية'); return; }

    var serializer = new XMLSerializer();
    var svgString = serializer.serializeToString(svg);
    var svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
    var svgUrl = URL.createObjectURL(svgBlob);

    var img = new Image();
    img.onload = function () {
        var scale = 8; // high-res
        var w = svg.viewBox.baseVal.width * scale;
        var h = svg.viewBox.baseVal.height * scale;
        var canvas = document.createElement('canvas');
        canvas.width = w; canvas.height = h;
        var ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, w, h);
        ctx.drawImage(img, 0, 0, w, h);
        URL.revokeObjectURL(svgUrl);

        if (format === 'png') {
            canvas.toBlob(function (blob) {
                var a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'qr-{{ $business->slug ?? "store" }}.png';
                document.body.appendChild(a); a.click(); a.remove();
                setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
            }, 'image/png');
        } else {
            // PDF via print dialog with just the image
            var dataUrl = canvas.toDataURL('image/png');
            var win = window.open('', '_blank');
            if (!win) { alert('فضلاً اسمح بالنوافذ المنبثقة للطباعة'); return; }
            win.document.write('<!doctype html><html><head><title>QR {{ $business->name }}</title>'
                + '<style>body{margin:0;display:grid;place-items:center;min-height:100vh;font-family:Cairo,sans-serif}'
                + 'img{width:80mm;height:80mm}@media print{@page{size:A4;margin:20mm}}</style></head><body>'
                + '<div style="text-align:center"><img src="' + dataUrl + '"/>'
                + '<div style="margin-top:8mm;font-weight:800">{{ $business->name }}</div>'
                + '<div style="font-size:11px;direction:ltr;color:#555">{{ $url }}</div></div>'
                + '<script>window.onload=function(){setTimeout(function(){window.print();},250)}<\/script>'
                + '</body></html>');
            win.document.close();
        }
    };
    img.onerror = function () { alert('فشل التحميل، حاول مجدداً'); URL.revokeObjectURL(svgUrl); };
    img.src = svgUrl;
}
</script>
@endsection
