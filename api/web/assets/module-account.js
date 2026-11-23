// Account module — Alpine.js component. Talks to /api/account* and /api/employee/all.
document.addEventListener('alpine:init', () => {
    Alpine.data('accountPage', () => ({
        items: window.__ITEMS__ || [],
        employees: window.__EMPLOYEES__ || [],
        mode: 0,
        form: {},
        message: '',
        empLabel(e) {
            return e.emp_name + ' (' + e.emp_id + ')';
        },
        ts(t) { return new Date(Number(t) * 1000).toLocaleString(); },
        async refresh() {
            const [ra, re] = await Promise.all([
                fetch('/api/account/all', {credentials: 'same-origin'}),
                fetch('/api/employee/all', {credentials: 'same-origin'})
            ]);
            if (ra.ok) this.items = await ra.json();
            if (re.ok) this.employees = await re.json();
        },
        openCreate() {
            this.mode = 1;
            this.form = {uuid: this.employees.length ? this.employees[0].emp_id : '', username: '', password: ''};
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
            const r = await fetch('/api/account', {
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
            if (!confirm('確定要刪除帳號「' + u.username + '」嗎？')) return;
            const r = await fetch('/api/account?uuid=' + encodeURIComponent(u.uuid), {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            if (r.status === 204) await this.refresh();
        }
    }));
});
