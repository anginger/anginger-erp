// History module — Alpine.js component. Talks to /api/history* (read-only).
document.addEventListener('alpine:init', () => {
    Alpine.data('historyPage', () => ({
        items: window.__ITEMS__ || [],
        async load() {
            const r = await fetch('/api/history/all', {credentials: 'same-origin'});
            if (r.ok) this.items = await r.json();
        },
        ts(t) { return new Date(t * 1000).toLocaleString(); }
    }));
});
