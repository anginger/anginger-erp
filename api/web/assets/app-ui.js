// Shared UI helpers: modal confirm dialog (showModal) + toast notifications + dialog a11y.
(function () {
    var dlg, msgEl, okBtn, cancelBtn, resolver = null;

    function ensureDialog() {
        if (dlg) return dlg;
        dlg = document.createElement('dialog');
        dlg.className = 'ts-modal';
        dlg.id = 'app-confirm';
        dlg.setAttribute('aria-labelledby', 'app-confirm-title');
        dlg.innerHTML =
            '<div class="content">' +
            '<h2 id="app-confirm-title">確認</h2>' +
            '<p class="muted" id="app-confirm-msg" style="margin:0 0 1.25rem"></p>' +
            '<div class="ts-buttons" style="justify-content:flex-end">' +
            '<button type="button" class="ts-button is-outlined" data-cancel>取消</button>' +
            '<button type="button" class="ts-button is-negative" data-ok>確定</button>' +
            '</div></div>';
        document.body.appendChild(dlg);
        msgEl = dlg.querySelector('#app-confirm-msg');
        okBtn = dlg.querySelector('[data-ok]');
        cancelBtn = dlg.querySelector('[data-cancel]');
        okBtn.addEventListener('click', function () { dlg.close('ok'); });
        cancelBtn.addEventListener('click', function () { dlg.close('cancel'); });
        dlg.addEventListener('close', function () {
            var r = resolver; resolver = null;
            if (r) r(dlg.returnValue === 'ok');
        });
        return dlg;
    }

    // Promise-based confirm() replacement using a real <dialog> (Escape closes, focus is trapped).
    window.appConfirm = function (message, opts) {
        opts = opts || {};
        ensureDialog();
        msgEl.textContent = message;
        dlg.querySelector('#app-confirm-title').textContent = opts.title || '確認';
        okBtn.textContent = opts.okText || '確定';
        okBtn.className = 'ts-button ' + (opts.danger === false ? '' : 'is-negative');
        return new Promise(function (resolve) {
            resolver = resolve;
            if (dlg.open) dlg.close();
            dlg.showModal();
            okBtn.focus();
        });
    };

    // Toast notifications (bottom-right), aria-live polite.
    var toastBox;
    window.appToast = function (message, type) {
        if (!toastBox) {
            toastBox = document.createElement('div');
            toastBox.id = 'app-toasts';
            toastBox.setAttribute('aria-live', 'polite');
            document.body.appendChild(toastBox);
        }
        var el = document.createElement('div');
        el.className = 'app-toast ' + (type === 'error' ? 'is-error' : 'is-ok');
        el.textContent = message;
        toastBox.appendChild(el);
        requestAnimationFrame(function () { el.classList.add('is-visible'); });
        setTimeout(function () {
            el.classList.remove('is-visible');
            setTimeout(function () { el.remove(); }, 250);
        }, 4000);
    };

    // a11y for Alpine :open dialogs: Escape closes, opening focuses the first field.
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('dialog[open]').forEach(function (d) {
            if (!d.classList.contains('ts-modal')) return;
            d.removeAttribute('open'); // non-showModal dialogs need manual close
        });
    });
    new MutationObserver(function (muts) {
        muts.forEach(function (m) {
            if (m.type === 'attributes' && m.attributeName === 'open') {
                var d = m.target;
                if (d.hasAttribute('open')) {
                    var f = d.querySelector('input, select, textarea, button:not([data-cancel])');
                    if (f) setTimeout(function () { f.focus(); }, 30);
                }
            }
        });
    }).observe(document.documentElement, { subtree: true, attributes: true, attributeFilter: ['open'] });
})();
