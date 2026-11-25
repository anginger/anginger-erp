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

    // x-modal="expr" on a <dialog>: true -> showModal() (native focus trap + Escape),
    // false -> close(). Native close (Escape) fires 'modal-close' so the view can sync state.
    document.addEventListener('alpine:init', function () {
        Alpine.directive('modal', function (el, { expression }, { effect, evaluate }) {
            if (el.tagName !== 'DIALOG') return;
            el.addEventListener('close', function () {
                el.dispatchEvent(new CustomEvent('modal-close', { bubbles: true }));
            });
            effect(function () {
                var v = evaluate(expression);
                if (v && !el.open) { try { el.showModal(); } catch (e) { el.setAttribute('open', ''); } }
                else if (!v && el.open) { el.close(); }
            });
        });
    });

    // a11y: when any modal dialog opens, move focus to the first field.
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
