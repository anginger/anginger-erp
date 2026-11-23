// User module — Alpine.js component. Talks to /api/user* and /api/level*.
document.addEventListener('alpine:init', () => {
    Alpine.data('userPage', () => ({
        items: window.__ITEMS__ || [],
        levels: window.__LEVELS__ || [],
        mode: 0,
        form: {},
        message: '',
        levelName(id) {
            const l = this.levels.find(l => String(l._id) === String(id));
            return l ? l.display_name : id;
        },
        async refresh() {
            const [ru, rl] = await Promise.all([
                fetch('/api/user/all', {credentials: 'same-origin'}),
                fetch('/api/level/all', {credentials: 'same-origin'})
            ]);
            if (ru.ok) this.items = await ru.json();
            if (rl.ok) this.levels = await rl.json();
        },
        openCreate() {
            this.mode = 1;
            this.form = {username: '', password: '', level: this.levels.length ? this.levels[0]._id : 1,
                display_name: '', address: '', email: '', phone: ''};
            this.message = '';
        },
        openEdit(u) {
            this.mode = 2;
            this.form = Object.assign({password: ''}, u);
            this.message = '';
        },
        async submit() {
            const body = new URLSearchParams(this.form);
            const create = this.mode === 1;
            const r = await fetch('/api/user', {
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
        async remove(u) {
            if (!confirm('確定要刪除「' + u.username + '」嗎？')) return;
            const r = await fetch('/api/user?uuid=' + encodeURIComponent(u.uuid), {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            if (r.status === 204) await this.refresh();
        }
    }));
});
