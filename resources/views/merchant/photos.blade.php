@extends('layouts.mobile')

@section('title', 'صور المتجر · ' . $business->name)
@section('page-title', 'صور المتجر')
@section('screen-class', 'bg-gray')

@section('content')
<div class="app-head">
    <a href="{{ route('merchant.dashboard') }}" class="back"><x-icon name="chev-r" :size="18"/></a>
    <div class="title">صور المتجر</div>
</div>

@if(session('flash'))
    <div class="flash">{{ session('flash') }}</div>
@endif
@if(session('flash_error'))
    <div class="flash" style="background: #FEE2E2; color: #991B1B;">{{ session('flash_error') }}</div>
@endif

<div class="scroll" style="padding: 8px 14px 28px;">

    {{-- ── INTRO ─────────────────────────────────────────────── --}}
    <div class="card" style="padding: 14px; margin-bottom: 12px; background: linear-gradient(135deg, rgba(13,148,136,.06), rgba(0,27,42,.02));">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="width: 42px; height: 42px; border-radius: 13px; background: white; border: 1px solid rgba(13,148,136,.18); display: grid; place-items: center; flex-shrink: 0;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="#0D9488" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                </svg>
            </span>
            <div>
                <div class="label-strong">ارفع صور المتجر والمنيو</div>
                <div class="label-meta" style="margin-top: 2px; line-height: 1.6;">حتى 10 صور في المرة · صور وضوحها أعلى تجيب زيارات أكثر.</div>
            </div>
        </div>
    </div>

    {{-- ── UPLOAD FORM ───────────────────────────────────────── --}}
    <form method="post" action="{{ route('merchant.photos.store') }}" enctype="multipart/form-data" id="photos-form" class="card" style="padding: 14px; margin-bottom: 12px;">
        @csrf

        <label for="photos-input" class="photo-drop" id="photo-drop">
            <input type="file" id="photos-input" name="photos[]" accept="image/*" multiple hidden>
            <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="#0D9488" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <div class="photo-drop-title">اضغط لاختيار صور</div>
            <div class="photo-drop-sub">JPG · PNG · WEBP · حتى 5MB لكل صورة</div>
        </label>

        <div id="photos-preview" class="photos-preview" hidden></div>

        @error('photos.*')<div class="form-error">{{ $message }}</div>@enderror
        @error('photos')<div class="form-error">{{ $message }}</div>@enderror

        <button type="submit" class="btn btn-teal" id="photos-submit" style="width: 100%; padding: 12px; margin-top: 12px; font-size: 13px;" disabled>
            رفع الصور
        </button>
    </form>

    {{-- ── EXISTING PHOTOS ───────────────────────────────────── --}}
    @php $images = is_array($business->images) ? $business->images : []; @endphp

    @if(count($images) > 0)
        <div class="card" style="padding: 14px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <div class="label-strong">الصور المرفوعة ({{ count($images) }})</div>
                <span class="label-meta">حد أقصى 30</span>
            </div>

            <div class="photos-grid">
                @foreach($images as $url)
                    <div class="photo-cell">
                        <img src="{{ $url }}" alt="" referrerpolicy="no-referrer"
                             onerror="this.closest('.photo-cell').classList.add('is-broken')">
                        <div class="photo-cell-broken">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                            </svg>
                            <span>صورة مكسورة</span>
                        </div>

                        <div class="photo-cell-overlay">
                            @if($business->cover !== $url)
                                <form method="post" action="{{ route('merchant.photos.cover') }}" style="display: contents;">
                                    @csrf
                                    <input type="hidden" name="url" value="{{ $url }}">
                                    <button type="submit" class="photo-action" title="تعيين كغلاف">غلاف</button>
                                </form>
                            @else
                                <span class="photo-action is-active">الغلاف</span>
                            @endif

                            @if($business->logo !== $url)
                                <form method="post" action="{{ route('merchant.photos.logo') }}" style="display: contents;">
                                    @csrf
                                    <input type="hidden" name="url" value="{{ $url }}">
                                    <button type="submit" class="photo-action" title="تعيين كشعار">شعار</button>
                                </form>
                            @else
                                <span class="photo-action is-active">الشعار</span>
                            @endif

                            <form method="post" action="{{ route('merchant.photos.destroy') }}" style="display: contents;"
                                  onsubmit="return confirm('تأكيد حذف هذه الصورة؟')">
                                @csrf @method('DELETE')
                                <input type="hidden" name="url" value="{{ $url }}">
                                <button type="submit" class="photo-action is-danger" title="حذف">✕</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="card" style="padding: 28px 16px; text-align: center;">
            <div style="font-size: 28px; opacity: .4; margin-bottom: 8px;">📷</div>
            <div class="label-strong">لا توجد صور بعد</div>
            <div class="label-meta" style="margin-top: 4px;">ارفع أول صورة من فوق.</div>
        </div>
    @endif

</div>

@include('partials.merchant-nav')

<style>
.photo-drop {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 28px 14px;
    border: 2px dashed rgba(13,148,136,.35);
    background: rgba(13,148,136,.04);
    border-radius: 14px;
    cursor: pointer;
    transition: background .15s ease, border-color .15s ease;
    text-align: center;
}
.photo-drop:hover, .photo-drop.is-drag {
    background: rgba(13,148,136,.08);
    border-color: var(--teal);
}
.photo-drop-title { font-weight: 800; font-size: 14px; color: var(--ink-1); }
.photo-drop-sub   { font-size: 11.5px; color: var(--ink-3); font-weight: 600; }

.photos-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(72px, 1fr));
    gap: 6px;
    margin-top: 12px;
}
.photos-preview img {
    width: 100%; aspect-ratio: 1;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--line);
}

.photos-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
@media (min-width: 480px) { .photos-grid { grid-template-columns: repeat(3, 1fr); } }

.photo-cell {
    position: relative;
    aspect-ratio: 1;
    border-radius: 12px;
    overflow: hidden;
    background: #F1F4F7;
}
.photo-cell img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.photo-cell-broken {
    position: absolute; inset: 0;
    display: none;
    flex-direction: column;
    align-items: center; justify-content: center;
    gap: 4px;
    color: var(--ink-3);
    font-size: 11px;
    font-weight: 700;
    background: #F1F4F7;
}
.photo-cell.is-broken img { display: none; }
.photo-cell.is-broken .photo-cell-broken { display: flex; }

.photo-cell-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(180deg, rgba(0,27,42,0) 30%, rgba(0,27,42,.65) 100%);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    gap: 4px;
    padding: 8px;
    opacity: 0;
    transition: opacity .18s ease;
}
.photo-cell:hover .photo-cell-overlay,
.photo-cell:focus-within .photo-cell-overlay { opacity: 1; }
@media (hover: none) { .photo-cell-overlay { opacity: 1; } }

.photo-action {
    background: rgba(255,255,255,.92);
    border: none;
    padding: 5px 9px;
    border-radius: 7px;
    font-size: 10.5px;
    font-weight: 800;
    color: var(--ink-1);
    cursor: pointer;
    backdrop-filter: blur(4px);
}
.photo-action.is-active { background: var(--teal); color: white; }
.photo-action.is-danger { background: #FF4D6D; color: white; padding: 5px 7px; }

.form-error { color: #B91C1C; font-size: 12px; font-weight: 700; margin-top: 8px; }
</style>

<script>
(function () {
    var input   = document.getElementById('photos-input');
    var drop    = document.getElementById('photo-drop');
    var preview = document.getElementById('photos-preview');
    var submit  = document.getElementById('photos-submit');
    if (!input) return;

    function render() {
        preview.innerHTML = '';
        if (!input.files || input.files.length === 0) {
            preview.hidden = true;
            submit.disabled = true;
            return;
        }
        preview.hidden = false;
        submit.disabled = false;
        Array.from(input.files).slice(0, 10).forEach(function (file) {
            if (!file.type.startsWith('image/')) return;
            var img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.onload = function () { URL.revokeObjectURL(img.src); };
            preview.appendChild(img);
        });
    }

    input.addEventListener('change', render);

    // Drag & drop
    ['dragover','dragenter'].forEach(function (ev) {
        drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-drag'); });
    });
    ['dragleave','drop'].forEach(function (ev) {
        drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-drag'); });
    });
    drop.addEventListener('drop', function (e) {
        var dt = e.dataTransfer;
        if (!dt || !dt.files || !dt.files.length) return;
        input.files = dt.files;
        render();
    });
})();
</script>
@endsection
