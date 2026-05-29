<script>
(function () {
    var input   = document.getElementById('cat-search');
    var form    = document.getElementById('cat-form');
    var items   = Array.from(document.querySelectorAll('.cat-item'));
    var empty   = document.getElementById('cat-empty');
    var count   = document.getElementById('cat-count');
    var chips   = Array.from(document.querySelectorAll('.chip[data-cat]'));
    if (!form || !items.length) return;

    form.addEventListener('submit', function (e) { e.preventDefault(); });

    function normalise(s) {
        return (s || '').toString().trim().toLowerCase()
            .replace(/[ً-ْٰ]/g, '')
            .replace(/[إأآا]/g, 'ا')
            .replace(/ى/g, 'ي')
            .replace(/ة/g, 'ه');
    }

    var activeCat = 'all';
    var query = '';

    function apply() {
        var q = normalise(query);
        var shown = 0;
        items.forEach(function (el) {
            var hay = normalise(el.dataset.haystack);
            var cat = normalise(el.dataset.cat);
            var byCat  = activeCat === 'all' || cat.indexOf(normalise(activeCat)) !== -1;
            var byText = q === '' || hay.indexOf(q) !== -1;
            var match = byCat && byText;
            el.hidden = !match;
            if (match) shown++;
        });
        if (count) count.textContent = (q || activeCat !== 'all') ? (shown + ' نتيجة') : '';
        if (empty) empty.hidden = !(shown === 0 && (q || activeCat !== 'all'));
    }

    if (input) {
        input.addEventListener('input', function () { query = input.value; apply(); });
    }
    chips.forEach(function (c) {
        c.addEventListener('click', function () {
            chips.forEach(function (x) { x.classList.remove('active'); });
            c.classList.add('active');
            activeCat = c.dataset.cat;
            apply();
        });
    });
})();
</script>
