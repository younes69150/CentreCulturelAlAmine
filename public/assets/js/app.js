// Petits comportements de l'interface. Le site fonctionne sans JavaScript.
document.addEventListener('DOMContentLoaded', () => {
    // Menu mobile
    const toggle = document.querySelector('.nav-toggle');
    const nav = document.getElementById('main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(open));
        });
    }

    // Messages affichés à la connexion
    const dialog = document.querySelector('dialog[data-autoshow]');
    if (dialog) {
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        } else {
            dialog.setAttribute('open', '');
            dialog.querySelector('form[method="dialog"]')?.addEventListener('submit', (e) => {
                e.preventDefault();
                dialog.remove();
            });
        }
    }

    // Notifications : fermeture au clic, et automatique après 5 s pour les succès
    const dismiss = (toast) => {
        toast.classList.add('is-leaving');
        setTimeout(() => toast.remove(), 200);
    };
    document.querySelectorAll('.toast').forEach((toast) => {
        toast.querySelector('[data-dismiss]')?.addEventListener('click', () => dismiss(toast));
        if (toast.classList.contains('toast-success')) setTimeout(() => dismiss(toast), 5000);
    });

    // Confirmation avant suppression
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    // Champ classe masqué si le code n'est pas pour un élève
    const roleSelect = document.querySelector('[data-role-select]');
    const classeField = document.querySelector('[data-classe-field]');
    if (roleSelect && classeField) {
        const sync = () => { classeField.hidden = roleSelect.value !== 'eleve'; };
        roleSelect.addEventListener('change', sync);
        sync();
    }

    document.querySelectorAll('[data-autosubmit]').forEach((el) => {
        el.addEventListener('change', () => el.form.submit());
    });

    document.querySelectorAll('[data-print]').forEach((btn) => btn.addEventListener('click', () => window.print()));

    document.querySelectorAll('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(btn.dataset.copy);
                const label = btn.textContent;
                btn.textContent = 'Copié ✓';
                setTimeout(() => { btn.textContent = label; }, 1800);
            } catch {
                window.prompt('Copiez les codes :', btn.dataset.copy);
            }
        });
    });

    // Code d'inscription : majuscules et tirets automatiques
    const codeInput = document.querySelector('.input-code');
    if (codeInput) {
        codeInput.addEventListener('input', () => {
            const raw = codeInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 12);
            codeInput.value = raw.match(/.{1,4}/g)?.join('-') ?? '';
        });
    }
});
