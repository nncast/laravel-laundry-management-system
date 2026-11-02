/* Soap Opera app shell: navigation drawer, toasts, dialogs, confirmations and
   small helpers shared by every page (window.* so page scripts can use them). */
(function () {
    'use strict';

    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

    window.escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, c => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));
    window.peso = value => '₱' + Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // ---------------- Toasts ----------------
    // The region is a manual popover so it sits in the top layer, above an open
    // <dialog>; showing it again moves it above dialogs opened since.
    const toastIcons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', warning: 'fa-triangle-exclamation', info: 'fa-circle-info' };

    function toastRegion() {
        let region = document.getElementById('toastRegion');
        if (!region) {
            region = document.createElement('div');
            region.id = 'toastRegion';
            region.className = 'toast-region';
            region.setAttribute('aria-live', 'polite');
            document.body.append(region);
        }
        if (region.showPopover) {
            region.popover = 'manual';
            try { if (region.matches(':popover-open')) region.hidePopover(); region.showPopover(); } catch (e) { /* not supported */ }
        }
        return region;
    }

    window.showToast = function (message, type = 'info', timeout = 5000, action = null) {
        if (!message) return null;
        const region = toastRegion();
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');

        const icon = document.createElement('i');
        icon.className = `fas ${toastIcons[type] || toastIcons.info}`;
        icon.setAttribute('aria-hidden', 'true');
        const text = document.createElement('div');
        text.className = 'toast-text';
        text.textContent = message;
        toast.append(icon, text);

        const remove = () => { toast.classList.add('toast-hide'); setTimeout(() => toast.remove(), 220); };
        if (action) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'toast-action';
            button.textContent = action.label;
            button.addEventListener('click', () => { action.onClick(); remove(); });
            toast.append(button);
        }
        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'toast-close';
        close.setAttribute('aria-label', 'Dismiss');
        close.innerHTML = '<i class="fas fa-xmark" aria-hidden="true"></i>';
        close.addEventListener('click', remove);
        toast.append(close);

        region.append(toast);
        if (timeout) setTimeout(remove, timeout);
        return toast;
    };

    // A toast for the next page (e.g. "Order saved" shown after redirecting to it)
    window.flashToast = (message, type = 'success') => {
        try { sessionStorage.setItem('flashToast', JSON.stringify({ message, type })); } catch (e) { /* storage blocked */ }
    };
    document.addEventListener('DOMContentLoaded', () => {
        try {
            const flash = JSON.parse(sessionStorage.getItem('flashToast') || 'null');
            sessionStorage.removeItem('flashToast');
            if (flash) window.showToast(flash.message, flash.type);
        } catch (e) { /* storage blocked */ }
    });

    // ---------------- Requests ----------------
    // Read a JSON error response (Laravel validation or {message}) into one string
    window.responseErrorMessage = async function (response, fallback = 'Something went wrong. Please try again.') {
        try {
            const data = await response.clone().json();
            if (data.errors) return Object.values(data.errors).flat().join('\n');
            if (data.message) return data.message;
        } catch (e) { /* not JSON */ }
        if (response.status === 419) return 'Your session expired. Please refresh the page.';
        if (response.status === 401) return 'Your session ended. Please log in again.';
        if (response.status === 403) return 'You do not have permission to do that.';
        return fallback;
    };

    // JSON request with the CSRF token; resolves with the data or throws an Error
    // whose message is ready to show. Laravel validation errors are on error.errors.
    window.sendJson = async function (url, body = {}, method = 'POST') {
        let response;
        try {
            response = await fetch(url, {
                method: method === 'GET' ? 'GET' : 'POST',
                headers: { 'X-CSRF-TOKEN': window.CSRF_TOKEN, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: method === 'GET' ? undefined : JSON.stringify(method === 'POST' ? body : { ...body, _method: method }),
            });
        } catch (e) {
            throw new Error('Could not reach the server. Check the connection and try again.');
        }
        if (!response.ok) {
            const error = new Error(await window.responseErrorMessage(response));
            try { error.errors = (await response.clone().json()).errors || null; } catch (e) { error.errors = null; }
            throw error;
        }
        return response.json();
    };

    // ---------------- Dialogs ----------------
    const dialogFrom = d => (typeof d === 'string' ? document.getElementById(d) : d);
    window.openDialog = d => { d = dialogFrom(d); if (d && !d.open) d.showModal(); return d; };
    window.closeDialog = d => { d = dialogFrom(d); if (d && d.open) d.close(); };

    document.addEventListener('click', e => {
        const opener = e.target.closest('[data-dialog-open]');
        if (opener) { e.preventDefault(); window.openDialog(opener.dataset.dialogOpen); return; }
        const closer = e.target.closest('[data-dialog-close]');
        if (closer) { e.preventDefault(); window.closeDialog(closer.closest('dialog')); return; }

        // Tapping the backdrop closes a dialog (the click lands on <dialog> itself, outside its box)
        const d = e.target;
        if (d instanceof HTMLDialogElement && d.open && !d.hasAttribute('data-keep-open')) {
            const r = d.getBoundingClientRect();
            if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) d.close();
        }
    });

    // One dialog for "add" and "edit" on the list pages:
    //   <button data-form-dialog="id" data-title="Edit X" data-action="url" data-method="PUT" data-values='{"name": "…"}'>
    // fills the dialog's form (empty for add) and opens it. The hidden _dialog/_action/_title
    // fields come back with the old input when the server rejects it, and the page reopens
    // the dialog (data-reopen) so the person sees what to fix.
    const META = ['_token', '_method', '_dialog', '_action', '_title'];
    document.addEventListener('click', e => {
        const trigger = e.target.closest('[data-form-dialog]');
        if (!trigger) return;
        const dialog = document.getElementById(trigger.dataset.formDialog);
        const form = dialog.querySelector('form');
        const values = trigger.dataset.values ? JSON.parse(trigger.dataset.values) : {};

        Array.from(form.elements).forEach(field => {
            if (!field.name || META.includes(field.name) || field.type === 'submit' || field.type === 'button') return;
            const value = values[field.name] ?? field.dataset.default ?? '';
            if (field.type === 'checkbox') field.checked = Boolean(Number(value)) || value === true;
            else if (field.type === 'radio') field.checked = String(field.value) === String(value);
            else if (field.type === 'file') field.value = '';
            else field.value = value;
            field.removeAttribute('aria-invalid');
        });
        form.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; });

        form.action = trigger.dataset.action;
        form.querySelector('[name="_method"]').value = trigger.dataset.method || 'POST';
        form.querySelector('[name="_action"]').value = trigger.dataset.action;
        form.querySelector('[name="_title"]').value = trigger.dataset.title;
        dialog.querySelector('.dialog-title').textContent = trigger.dataset.title;
        const submit = form.querySelector('[type="submit"]');
        if (submit && trigger.dataset.submit) submit.textContent = trigger.dataset.submit;
        window.openDialog(dialog);
        form.querySelector('input:not([type="hidden"]), select, textarea')?.focus();
    });
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('dialog[data-reopen="1"]').forEach(d => {
            window.openDialog(d);
            d.querySelector('[aria-invalid="true"]')?.focus();
        });
    });

    // Promise-based replacement for window.confirm(), rendered in the layout's #confirmDialog
    window.confirmAction = function ({ title = 'Are you sure?', message = '', confirmText = 'Confirm', cancelText = 'Cancel', tone = 'danger' } = {}) {
        const d = document.getElementById('confirmDialog');
        if (!d) return Promise.resolve(window.confirm([title, message].filter(Boolean).join('\n\n')));

        d.querySelector('#confirmTitle').textContent = title;
        d.querySelector('#confirmMessage').textContent = message;
        d.querySelector('.confirm-body').classList.toggle('is-neutral', tone !== 'danger');
        const ok = d.querySelector('[data-confirm-ok]');
        ok.textContent = confirmText;
        ok.className = 'btn ' + (tone === 'danger' ? 'btn-danger' : 'btn-primary');
        d.querySelector('[data-confirm-cancel]').textContent = cancelText;
        d.returnValue = '';
        d.showModal();
        (tone === 'danger' ? d.querySelector('[data-confirm-cancel]') : ok).focus();
        return new Promise(resolve => d.addEventListener('close', () => resolve(d.returnValue === 'ok'), { once: true }));
    };

    // <form data-confirm="Message" data-confirm-title="…" data-confirm-button="Delete"> asks first
    document.addEventListener('submit', async e => {
        const form = e.target;
        const source = e.submitter?.dataset.confirm !== undefined ? e.submitter : form;
        if (source.dataset.confirm === undefined || form.dataset.confirmed) return;
        e.preventDefault();
        e.stopImmediatePropagation();
        const ok = await window.confirmAction({
            title: source.dataset.confirmTitle || 'Are you sure?',
            message: source.dataset.confirm,
            confirmText: source.dataset.confirmButton || 'Delete',
            tone: source.dataset.confirmTone || 'danger',
        });
        if (!ok) return;
        form.dataset.confirmed = '1';
        form.requestSubmit(e.submitter || undefined);
        delete form.dataset.confirmed;
    }, true);

    // <form data-ajax> (dialog forms, row deletes) submits in the background:
    // - validation errors (422) appear under their fields and the dialog stays open
    // - a JSON success shows its message, a redirect lets the server's flash message
    //   show; either way the page reloads to show the change
    document.addEventListener('submit', async e => {
        const form = e.target;
        if (!form.matches('form[data-ajax]') || e.defaultPrevented) return;
        e.preventDefault();
        const button = e.submitter || form.querySelector('[type="submit"]');
        button?.classList.add('is-loading');
        form.querySelectorAll('[aria-invalid="true"]').forEach(f => f.removeAttribute('aria-invalid'));
        form.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; });

        const done = () => button?.classList.remove('is-loading');
        let response;
        try {
            response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.CSRF_TOKEN },
                redirect: 'manual',
            });
        } catch (err) {
            done();
            window.showToast('Could not reach the server. Check the connection and try again.', 'error');
            return;
        }

        if (response.type === 'opaqueredirect') { window.location.reload(); return; }
        let data = null;
        try { data = await response.clone().json(); } catch (err) { /* not JSON */ }

        if (response.ok && data?.success !== false) {
            window.flashToast(data?.message || form.dataset.success || 'Saved.');
            window.location.reload();
            return;
        }
        done();
        if (response.status === 422 && data?.errors) {
            let first = null;
            Object.entries(data.errors).forEach(([name, messages]) => {
                const field = form.querySelector(`[name="${name}"]`);
                if (!field) { window.showToast(messages[0], 'error'); return; }
                field.setAttribute('aria-invalid', 'true');
                const error = field.closest('.field')?.querySelector('.field-error');
                if (error) error.textContent = messages[0];
                first ??= field;
            });
            first?.focus();
            return;
        }
        window.showToast(data?.message || await window.responseErrorMessage(response), 'error', 8000);
    });

    // Prevent double-submitting regular forms (slow connections)
    document.addEventListener('submit', e => {
        const form = e.target;
        if (form.method === 'dialog' || form.method.toLowerCase() === 'get' || form.dataset.allowResubmit !== undefined) return;
        setTimeout(() => {
            if (e.defaultPrevented) return;
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach(b => { b.disabled = true; });
        }, 0);
    });
    // Re-enable buttons when the page comes back from the browser's Back button
    window.addEventListener('pageshow', e => {
        if (e.persisted) $$('form button[disabled]').forEach(b => { b.disabled = false; });
    });

    // ---------------- Navigation drawer (tablets and phones) ----------------
    const sidebar = document.getElementById('sidebar');
    const setNav = open => {
        if (!sidebar) return;
        sidebar.classList.toggle('is-open', open);
        document.body.classList.toggle('nav-open', open);
        $$('[aria-controls="sidebar"]').forEach(b => b.setAttribute('aria-expanded', String(open)));
        if (open) sidebar.querySelector('a, button')?.focus();
    };
    document.addEventListener('click', e => {
        if (e.target.closest('[data-nav-open]')) setNav(true);
        else if (e.target.closest('[data-nav-close]')) setNav(false);
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && document.body.classList.contains('nav-open')) setNav(false);
    });
    window.matchMedia('(min-width: 1024px)').addEventListener('change', e => { if (e.matches) setNav(false); });

    // ---------------- Tables + shortcuts ----------------
    // A row with data-href opens on tap anywhere outside its own buttons and links
    document.addEventListener('click', e => {
        const row = e.target.closest('tr[data-href]');
        if (!row || e.target.closest('a, button, input, select, textarea, label, summary')) return;
        if (e.ctrlKey || e.metaKey) window.open(row.dataset.href, '_blank');
        else window.location.href = row.dataset.href;
    });

    // <input data-table-filter="tableId"> hides rows that don't contain the typed text
    document.addEventListener('input', e => {
        const input = e.target.closest('[data-table-filter]');
        if (!input) return;
        const query = input.value.trim().toLowerCase();
        const table = document.getElementById(input.dataset.tableFilter);
        let shown = 0;
        table.querySelectorAll('tbody tr:not(.no-match)').forEach(row => {
            const hit = row.textContent.toLowerCase().includes(query);
            row.hidden = !hit;
            if (hit) shown++;
        });
        const none = table.querySelector('tr.no-match');
        if (none) none.hidden = shown > 0;
    });

    // Close open dropdown menus when tapping elsewhere
    document.addEventListener('click', e => {
        $$('details.menu[open]').forEach(menu => { if (!menu.contains(e.target)) menu.open = false; });
    });

    // "/" jumps to the search box (keyboards only; touch users tap it)
    document.addEventListener('keydown', e => {
        if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;
        if (e.target.closest('input, textarea, select, [contenteditable="true"]') || document.querySelector('dialog[open]')) return;
        const search = document.querySelector('[data-search-shortcut]');
        if (search) { e.preventDefault(); search.focus(); search.select?.(); }
    });
})();
