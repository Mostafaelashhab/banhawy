@auth
<div id="notif-card" class="card" style="padding: 14px; display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
    <span style="width: 40px; height: 40px; border-radius: 12px; background: var(--teal-50); display: grid; place-items: center; color: var(--teal); flex-shrink: 0;">
        <x-icon name="bell" :size="18" stroke="#0D9488"/>
    </span>
    <div style="flex: 1; min-width: 0;">
        <div class="label-strong" id="notif-title">فعّل الإشعارات</div>
        <div class="label-meta" id="notif-sub" style="line-height: 1.5;">
            @if(auth()->user()->isOwner())
                هتوصلك تنبيهات الطلبات الجديدة فورًا.
            @else
                هتوصلك عروض ومستجدات من الأنشطة المفضلة.
            @endif
        </div>
    </div>
    <button id="notif-enable" class="btn btn-teal" style="padding: 9px 14px; font-size: 12px;">فعّل</button>
    <button id="notif-disable" class="btn btn-line" style="padding: 9px 14px; font-size: 12px; display: none;">إلغاء</button>
</div>

<script>
(function () {
    var card    = document.getElementById('notif-card');
    var enable  = document.getElementById('notif-enable');
    var disable = document.getElementById('notif-disable');
    var title   = document.getElementById('notif-title');
    var sub     = document.getElementById('notif-sub');
    if (!card || !window.banhawyPush) return;

    var supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
    if (!supported) {
        card.style.opacity = '.7';
        enable.disabled = true;
        title.textContent = 'الإشعارات غير مدعومة';
        sub.textContent = 'متصفّحك لا يدعم Web Push.';
        return;
    }

    // Permission was denied at the OS level → tell the user
    if (Notification.permission === 'denied') {
        title.textContent = 'الإشعارات محظورة';
        sub.textContent = 'افتح إعدادات الموقع في المتصفح واسمح بالإشعارات.';
        enable.disabled = true;
        return;
    }

    function setEnabledUI(on) {
        enable.style.display  = on ? 'none' : 'inline-flex';
        disable.style.display = on ? 'inline-flex' : 'none';
        title.textContent = on ? 'الإشعارات مفعّلة ✓' : 'فعّل الإشعارات';
        sub.textContent   = on
            ? 'لو حبّيت توقفها، اضغط إلغاء.'
            : (@json(auth()->user()->isOwner()) ? 'هتوصلك تنبيهات الطلبات الجديدة فورًا.' : 'هتوصلك عروض ومستجدات.');
    }

    window.banhawyPush.isSubscribed().then(setEnabledUI);

    enable.addEventListener('click', async function () {
        enable.disabled = true;
        var oldText = enable.textContent;
        enable.textContent = '...';
        try {
            await window.banhawyPush.subscribe();
            setEnabledUI(true);
            // Send a confirmation push so the user knows it works
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            fetch('/push/test', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
        } catch (e) {
            alert(e.message || 'فشل التفعيل');
        } finally {
            enable.disabled = false;
            enable.textContent = oldText;
        }
    });

    disable.addEventListener('click', async function () {
        disable.disabled = true;
        try {
            await window.banhawyPush.unsubscribe();
            setEnabledUI(false);
        } finally {
            disable.disabled = false;
        }
    });
})();
</script>
@endauth
