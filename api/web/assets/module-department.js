// Department module — Alpine.js component. Talks to /api/department* and /api/employee/all.
document.addEventListener('alpine:init', () => {
    Alpine.data('departmentPage', () => ({
        items: window.__ITEMS__ || [],
        q: '',
        filtered() {
            const q = (this.q || '').trim().toLowerCase();
            const list = this.items || this.conversations || [];
            if (!q) return list;
            return list.filter(row => JSON.stringify(row).toLowerCase().includes(q));
        },
        employees: window.__EMPLOYEES__ || [],
        mode: 0,
        form: {},
        message: '',
        empLabel(e) {
            return e.emp_name + ' (' + e.emp_id + ')';
        },
        managerName(empId) {
            const e = this.employees.find(e => String(e.emp_id) === String(empId));
            return e ? e.emp_name : (empId || '');
        },
        async refresh() {
            const [rd, re] = await Promise.all([
                fetch('/api/department/all', {credentials: 'same-origin'}),
                fetch('/api/employee/all', {credentials: 'same-origin'})
            ]);
            if (rd.ok) this.items = await rd.json();
            if (re.ok) this.employees = await re.json();
        },
        openCreate() {
            this.mode = 1;
            this.form = {dep_name: '', manager_emp_id: this.employees.length ? this.employees[0].emp_id : ''};
            this.message = '';
        },
        openEdit(d) {
            this.mode = 2;
            this.form = Object.assign({}, d);
            this.message = '';
        },
        async submit() {
            const body = new URLSearchParams(this.form);
            const create = this.mode === 1;
            const r = await fetch('/api/department', {
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
        async remove(d) {
            if (!(await appConfirm('確定要刪除「' + d.dep_name + '」嗎？'))) return;
            const r = await fetch('/api/department?dep_id=' + encodeURIComponent(d.dep_id), {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            if (r.status === 204) {
                await this.refresh();
            } else {
                const d2 = await r.json().catch(() => ({}));
                appToast(d2.message || '刪除失敗（該部門可能仍有員工）', 'error');
            }
        }
    }));
});
