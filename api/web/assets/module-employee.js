// Employee module — Alpine.js component. Talks to /api/employee* and /api/department/all.
document.addEventListener('alpine:init', () => {
    Alpine.data('employeePage', () => ({
        items: window.__ITEMS__ || [],
        q: '',
        filtered() {
            const q = (this.q || '').trim().toLowerCase();
            const list = this.items || this.conversations || [];
            if (!q) return list;
            return list.filter(row => JSON.stringify(row).toLowerCase().includes(q));
        },
        departments: window.__DEPARTMENTS__ || [],
        mode: 0,
        form: {},
        message: '',
        depName(id) {
            const d = this.departments.find(d => String(d.dep_id) === String(id));
            return d ? d.dep_name : id;
        },
        async refresh() {
            const [re, rd] = await Promise.all([
                fetch('/api/employee/all', {credentials: 'same-origin'}),
                fetch('/api/department/all', {credentials: 'same-origin'})
            ]);
            if (re.ok) this.items = await re.json();
            if (rd.ok) this.departments = await rd.json();
        },
        openCreate() {
            this.mode = 1;
            this.form = {emp_name: '', job_title: '', dep_id: this.departments.length ? this.departments[0].dep_id : '',
                city: '', address: '', phone: '', zip_code: '', month_salary: '', annual_leave: ''};
            this.message = '';
        },
        openEdit(e) {
            this.mode = 2;
            this.form = Object.assign({}, e);
            this.message = '';
        },
        async submit() {
            const body = new URLSearchParams(this.form);
            const create = this.mode === 1;
            const r = await fetch('/api/employee', {
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
        async remove(e) {
            if (!(await appConfirm('確定要刪除「' + e.emp_name + '」嗎？'))) return;
            const r = await fetch('/api/employee?emp_id=' + encodeURIComponent(e.emp_id), {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            if (r.status === 204) {
                await this.refresh();
            } else {
                const d = await r.json().catch(() => ({}));
                appToast(d.message || '刪除失敗（該員工可能擁有登入帳號）', 'error');
            }
        }
    }));
});
