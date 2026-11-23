// Product module — Alpine.js component. Talks to /api/product* (JSON API).
document.addEventListener('alpine:init', () => {
    Alpine.data('productPage', () => ({
        items: window.__ITEMS__ || [],
        mode: 0, // 0 = list, 1 = create, 2 = edit
        form: {},
        message: '',
        async refresh() {
            const r = await fetch('/api/product/all', {credentials: 'same-origin'});
            if (r.ok) this.items = await r.json();
        },
        openCreate() {
            this.mode = 1;
            this.form = {display_name: '', cost_value: 0, sell_value: 0, remain_amount: 0};
            this.message = '';
        },
        openEdit(p) {
            this.mode = 2;
            this.form = Object.assign({}, p);
            this.message = '';
        },
        async submit() {
            const body = new URLSearchParams(this.form);
            const create = this.mode === 1;
            const r = await fetch('/api/product', {
                method: create ? 'POST' : 'PUT',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body
            });
            if (r.status === 201 || r.status === 204) {
                this.mode = 0;
                await this.refresh();
                return;
            }
            const d = await r.json().catch(() => ({}));
            this.message = d.message || '操作失敗';
        },
        async remove(p) {
            if (!confirm('確定要刪除「' + p.display_name + '」嗎？')) return;
            const r = await fetch('/api/product?uuid=' + encodeURIComponent(p.uuid), {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            if (r.status === 204) await this.refresh();
        }
    }));
});
