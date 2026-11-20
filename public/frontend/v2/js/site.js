/* S S ENTERPRISE — public website interactions (vanilla JS, no dependencies) */
(function () {
    'use strict';

    var SITE = window.SITE || { i18n: {} };
    var T = SITE.i18n || {};
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isBn = SITE.locale === 'bn';
    var BN = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    function $(sel, ctx) { return (ctx || document).querySelector(sel); }
    function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }
    function num(v) { var s = String(v); return isBn ? s.replace(/\d/g, function (d) { return BN[d]; }) : s; }
    function money(v) {
        var n = Math.round(Number(v || 0) * 100) / 100;
        var s = n.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        return '৳' + num(s);
    }
    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* ---------------- Header: solid on scroll + mobile nav ---------------- */
    var header = $('[data-header]');
    function onHeaderScroll() {
        if (header) header.classList.toggle('is-solid', window.scrollY > 24);
    }
    onHeaderScroll();
    window.addEventListener('scroll', onHeaderScroll, { passive: true });

    var navToggle = $('[data-nav-toggle]');
    var nav = $('#mainNav');
    if (navToggle && nav) {
        navToggle.addEventListener('click', function () {
            var open = !nav.classList.contains('open');
            nav.classList.toggle('open', open);
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (header) header.classList.add('is-solid');
            if (!open) onHeaderScroll();
        });
        $$('a', nav).forEach(function (a) {
            a.addEventListener('click', function () {
                nav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /* ---------------- Reveal on scroll ---------------- */
    var revealEls = $$('[data-reveal]');
    if ('IntersectionObserver' in window && !reduceMotion) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('is-visible');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach(function (el, i) {
            // Stagger siblings inside grids.
            if (!el.style.getPropertyValue('--d') && el.parentElement && el.parentElement.children.length > 1) {
                var idx = Array.prototype.indexOf.call(el.parentElement.children, el) % 4;
                el.style.setProperty('--d', (idx * 0.07) + 's');
            }
            io.observe(el);
        });
    } else {
        revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    }

    /* ---------------- Parallax (scroll) + depth (mouse) ---------------- */
    var parallaxEls = $$('[data-parallax]');
    var depthEls = $$('[data-depth]');
    var mouse = { x: 0, y: 0 };
    var ticking = false;

    function applyTransforms() {
        ticking = false;
        var vh = window.innerHeight;
        parallaxEls.forEach(function (el) {
            var speed = parseFloat(el.getAttribute('data-parallax')) || 0;
            var host = el.closest('section') || el.parentElement;
            var rect = host.getBoundingClientRect();
            if (rect.bottom < -200 || rect.top > vh + 200) return;
            var offset = (rect.top + rect.height / 2 - vh / 2) * -speed;
            var inHero = !!el.closest('[data-hero]');
            var limit = inHero ? 90 : 220; // keep layers near their slot
            offset = Math.max(-limit, Math.min(limit, offset));
            // On phones the hero stacks vertically; moving cards would cover the text.
            if (inHero && window.innerWidth < 1024 && el.classList.contains('float-card')) offset = 0;
            el.style.setProperty('--py', offset.toFixed(1) + 'px');
            compose(el);
        });
        depthEls.forEach(compose);
    }
    function compose(el) {
        var py = el.style.getPropertyValue('--py') || '0px';
        var depth = parseFloat(el.getAttribute('data-depth')) || 0;
        var mx = (mouse.x * depth * 400).toFixed(1);
        var my = (mouse.y * depth * 400).toFixed(1);
        el.style.transform = 'translate3d(' + mx + 'px, calc(' + py + ' + ' + my + 'px), 0)';
    }
    function requestTick() {
        if (!ticking) { ticking = true; window.requestAnimationFrame(applyTransforms); }
    }
    if (!reduceMotion && (parallaxEls.length || depthEls.length)) {
        window.addEventListener('scroll', requestTick, { passive: true });
        window.addEventListener('resize', requestTick);
        var hero = $('[data-hero]');
        if (hero && window.matchMedia('(pointer: fine)').matches) {
            hero.addEventListener('mousemove', function (e) {
                var r = hero.getBoundingClientRect();
                mouse.x = (e.clientX - r.left) / r.width - 0.5;
                mouse.y = (e.clientY - r.top) / r.height - 0.5;
                requestTick();
            });
            hero.addEventListener('mouseleave', function () { mouse.x = 0; mouse.y = 0; requestTick(); });
        }
        requestTick();
    }

    /* ---------------- Tilt on brand cards ---------------- */
    if (!reduceMotion && window.matchMedia('(pointer: fine)').matches) {
        $$('[data-tilt]').forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                var r = card.getBoundingClientRect();
                var x = (e.clientX - r.left) / r.width - 0.5;
                var y = (e.clientY - r.top) / r.height - 0.5;
                card.style.transform = 'perspective(700px) rotateY(' + (x * 8) + 'deg) rotateX(' + (-y * 8) + 'deg) translateY(-4px)';
            });
            card.addEventListener('mouseleave', function () { card.style.transform = ''; });
        });
    }

    /* ---------------- Animated counters ---------------- */
    var counters = $$('[data-count]');
    if (counters.length && 'IntersectionObserver' in window && !reduceMotion) {
        var cio = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                cio.unobserve(e.target);
                var el = e.target;
                var target = parseInt(el.getAttribute('data-count'), 10) || 0;
                var start = performance.now();
                var dur = 1400;
                (function step(now) {
                    var p = Math.min((now - start) / dur, 1);
                    var eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = num(Math.round(target * eased));
                    if (p < 1) requestAnimationFrame(step);
                })(start);
            });
        }, { threshold: 0.5 });
        counters.forEach(function (c) { c.textContent = num(0); cio.observe(c); });
    }

    /* ---------------- Gallery ---------------- */
    var mainImg = $('[data-gallery-main]');
    $$('[data-gallery-thumb]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!mainImg) return;
            mainImg.style.opacity = '0';
            setTimeout(function () {
                mainImg.src = btn.getAttribute('data-gallery-thumb');
                mainImg.style.opacity = '1';
            }, 150);
            $$('[data-gallery-thumb]').forEach(function (b) { b.classList.remove('on'); });
            btn.classList.add('on');
        });
    });

    /* ---------------- Quantity steppers (product page) ---------------- */
    function bindQty(wrap, onChange) {
        var input = $('[data-qty-input]', wrap);
        function set(v) {
            v = Math.max(1, Math.min(100000, parseInt(v, 10) || 1));
            input.value = v;
            if (onChange) onChange(v);
        }
        $('[data-qty-dec]', wrap).addEventListener('click', function () { set(+input.value - 1); });
        $('[data-qty-inc]', wrap).addEventListener('click', function () { set(+input.value + 1); });
        input.addEventListener('change', function () { set(input.value); });
    }
    $$('.pd-order [data-qty]').forEach(function (w) { bindQty(w); });

    /* ---------------- Filters sheet (catalog, mobile) ---------------- */
    var filters = $('[data-filters]');
    var filtersBackdrop = $('.filters-backdrop');
    function toggleFilters(open) {
        if (!filters) return;
        filters.classList.toggle('open', open);
        if (filtersBackdrop) filtersBackdrop.hidden = !open;
        document.body.classList.toggle('lock', open);
    }
    $$('[data-filters-open]').forEach(function (b) { b.addEventListener('click', function () { toggleFilters(true); }); });
    $$('[data-filters-close]').forEach(function (b) { b.addEventListener('click', function () { toggleFilters(false); }); });

    /* ---------------- Toast ---------------- */
    var toastEl = $('[data-toast]');
    var toastTimer;
    function toast(msg) {
        if (!toastEl) return;
        toastEl.innerHTML = '<i class="mdi mdi-check-circle"></i> ' + escapeHtml(msg);
        toastEl.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toastEl.classList.remove('show'); }, 2400);
    }

    /* ---------------- Order list (localStorage) ---------------- */
    var STORE_KEY = 'sse_order_list_v1';
    function load() {
        try {
            var data = JSON.parse(localStorage.getItem(STORE_KEY) || '[]');
            return Array.isArray(data) ? data : [];
        } catch (e) { return []; }
    }
    var memoryList = load();
    function save(list) {
        memoryList = list;
        try { localStorage.setItem(STORE_KEY, JSON.stringify(list)); } catch (e) { /* private mode: keep in memory */ }
        render();
    }
    function list() { return memoryList; }

    function addItem(item, qty) {
        var l = list().slice();
        var found = l.filter(function (x) { return x.id === item.id; })[0];
        if (found) { found.qty = Math.min(100000, found.qty + qty); }
        else { l.push({ id: item.id, name: item.name, unit: item.unit, price: item.price, qty: qty }); }
        save(l);
        toast(T.added || 'Added');
    }

    $$('[data-add-order]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var qty = 1;
            if (btn.hasAttribute('data-with-qty')) {
                var inp = $('.pd-order [data-qty-input]');
                qty = Math.max(1, parseInt(inp && inp.value, 10) || 1);
            }
            addItem({
                id: parseInt(btn.getAttribute('data-id'), 10),
                name: btn.getAttribute('data-name'),
                unit: btn.getAttribute('data-unit'),
                price: parseFloat(btn.getAttribute('data-price')) || 0
            }, qty);
            btn.classList.add('added');
            setTimeout(function () { btn.classList.remove('added'); }, 800);
        });
    });

    var drawer = $('#orderDrawer');
    var backdrop = $('.drawer-backdrop[data-order-close]');
    var itemsEl = $('[data-order-items]');
    var emptyEl = $('[data-order-empty]');
    var summaryEl = $('[data-order-summary]');
    var totalEl = $('[data-order-total]');
    var formEl = $('[data-order-form]');
    var actionsEl = $('[data-order-actions]');
    var statusEl = $('[data-order-status]');

    function openDrawer(open) {
        if (!drawer) return;
        drawer.classList.toggle('open', open);
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (backdrop) backdrop.hidden = !open;
        document.body.classList.toggle('lock', open);
        if (open) { var f = $('.drawer-head .icon-btn', drawer); if (f) f.focus(); }
    }
    $$('[data-order-open]').forEach(function (b) { b.addEventListener('click', function () { openDrawer(true); }); });
    $$('[data-order-close]').forEach(function (b) { b.addEventListener('click', function () { openDrawer(false); }); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { openDrawer(false); toggleFilters(false); }
    });

    function render() {
        var l = list();
        var count = l.length;
        $$('[data-order-count]').forEach(function (el) {
            el.textContent = num(count);
            el.hidden = count === 0;
        });
        if (!itemsEl) return;

        itemsEl.innerHTML = l.map(function (x) {
            var sub = x.price > 0 ? money(x.price) + ' × ' + num(x.qty) + ' ' + escapeHtml(x.unit) + ' = <b>' + money(x.price * x.qty) + '</b>' : escapeHtml(x.unit);
            return '<li data-id="' + x.id + '">' +
                '<div><div class="oi-name">' + escapeHtml(x.name) + '</div><div class="oi-sub">' + sub + '</div></div>' +
                '<div class="oi-ctrl"><div class="qty" data-qty>' +
                '<button type="button" data-qty-dec aria-label="-"><i class="mdi mdi-minus"></i></button>' +
                '<input type="number" min="1" max="100000" value="' + x.qty + '" data-qty-input aria-label="qty">' +
                '<button type="button" data-qty-inc aria-label="+"><i class="mdi mdi-plus"></i></button></div>' +
                '<button type="button" class="oi-remove" data-remove aria-label="remove"><i class="mdi mdi-trash-can-outline"></i></button></div>' +
                '</li>';
        }).join('');

        $$('li', itemsEl).forEach(function (li) {
            var id = parseInt(li.getAttribute('data-id'), 10);
            bindQty($('[data-qty]', li), function (v) {
                save(list().map(function (x) { if (x.id === id) x.qty = v; return x; }));
            });
            $('[data-remove]', li).addEventListener('click', function () {
                save(list().filter(function (x) { return x.id !== id; }));
            });
        });

        var total = l.reduce(function (s, x) { return s + (x.price > 0 ? x.price * x.qty : 0); }, 0);
        if (totalEl) totalEl.textContent = money(total);
        if (emptyEl) emptyEl.hidden = count > 0;
        if (summaryEl) summaryEl.hidden = count === 0 || total === 0;
        if (formEl) formEl.hidden = count === 0;
        if (actionsEl) actionsEl.hidden = count === 0;
    }
    render();

    function formValues() {
        var data = {};
        if (!formEl) return data;
        $$('input, textarea', formEl).forEach(function (i) {
            if ((i.type === 'radio' || i.type === 'checkbox') && !i.checked) return;
            data[i.name] = i.value.trim();
        });
        return data;
    }

    function waMessage() {
        var v = formValues();
        var lines = [(T.hello || 'Hello! I would like to order:'), ''];
        list().forEach(function (x, i) {
            lines.push(num(i + 1) + '. ' + x.name + ' — ' + num(x.qty) + ' ' + x.unit);
        });
        lines.push('');
        if (v.name) lines.push((T.name || 'Name') + ': ' + v.name);
        if (v.delivery_type) lines.push((T.deliverTo || 'Deliver to') + ': ' + (v.delivery_type === 'home' ? (T.homeOpt || 'Home') : (T.shopOpt || 'Shop')));
        if (v.area) lines.push((T.place || 'Place') + ': ' + v.area);
        if (v.message) lines.push((T.note || 'Note') + ': ' + v.message);
        return lines.join('\n');
    }

    var waBtn = $('[data-order-wa]');
    if (waBtn) {
        waBtn.addEventListener('click', function () {
            var url = 'https://wa.me/' + (SITE.whatsapp || '') + '?text=' + encodeURIComponent(waMessage());
            window.open(url, '_blank', 'noopener');
        });
    }

    var submitBtn = $('[data-order-submit]');
    if (submitBtn) {
        submitBtn.addEventListener('click', function () {
            var v = formValues();
            statusEl.className = 'form-status';
            if (!v.name || !v.mobile) {
                statusEl.textContent = T.required || 'Please enter your name and mobile number.';
                statusEl.classList.add('err');
                var first = !v.name ? $('input[name=name]', formEl) : $('input[name=mobile]', formEl);
                if (first) first.focus();
                return;
            }
            submitBtn.disabled = true;
            statusEl.textContent = T.sending || 'Sending…';

            var payload = {
                name: v.name, mobile: v.mobile, delivery_type: v.delivery_type, area: v.area, message: v.message, website: v.website,
                items: list().map(function (x) { return { id: x.id, cases: x.qty }; })
            };

            fetch(SITE.inquiryUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': SITE.csrf, 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            }).then(function (res) {
                return res.json().catch(function () { return {}; }).then(function (body) { return { ok: res.ok, body: body }; });
            }).then(function (r) {
                if (r.ok) {
                    statusEl.textContent = (r.body && r.body.message) || T.sent;
                    statusEl.classList.add('ok');
                    save([]);
                    formEl.hidden = false; // keep the success message visible
                    if (actionsEl) actionsEl.hidden = true;
                } else {
                    var msg = (r.body && r.body.errors && Object.values(r.body.errors)[0] && Object.values(r.body.errors)[0][0]) || (r.body && r.body.message) || T.error;
                    statusEl.textContent = msg;
                    statusEl.classList.add('err');
                }
            }).catch(function () {
                statusEl.textContent = T.error || 'Error';
                statusEl.classList.add('err');
            }).then(function () { submitBtn.disabled = false; });
        });
    }

    var clearBtn = $('[data-order-clear]');
    if (clearBtn) clearBtn.addEventListener('click', function () { save([]); });
})();
