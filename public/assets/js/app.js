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

    // Message vocal : enregistrement au micro, versé dans le champ fichier du formulaire
    document.querySelectorAll('[data-recorder]').forEach((box) => {
        const ui = box.querySelector('[data-recorder-ui]');
        const input = box.querySelector('[data-rec-input]');
        if (!ui || !input || !window.MediaRecorder || !navigator.mediaDevices?.getUserMedia || typeof DataTransfer !== 'function') return;
        const start = ui.querySelector('[data-rec-start]');
        const stop = ui.querySelector('[data-rec-stop]');
        const time = ui.querySelector('[data-rec-time]');
        const preview = ui.querySelector('[data-rec-preview]');
        const reset = ui.querySelector('[data-rec-reset]');
        const status = ui.querySelector('[data-rec-status]');
        const MAX_SECONDS = 300;
        let recorder = null;
        let timer = null;
        let previewUrl = null;
        ui.hidden = false;

        const types = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4', 'audio/ogg;codecs=opus'];
        const mimeType = types.find((t) => MediaRecorder.isTypeSupported?.(t)) || '';

        const clear = () => {
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            preview.hidden = true;
            preview.removeAttribute('src');
            reset.hidden = true;
            input.value = '';
            status.textContent = '';
            start.hidden = false;
        };

        start.addEventListener('click', async () => {
            let stream;
            try {
                stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            } catch {
                status.textContent = 'Micro inaccessible : autorisez le micro dans votre navigateur (le site doit être en https).';
                return;
            }
            clear();
            const chunks = [];
            recorder = new MediaRecorder(stream, mimeType ? { mimeType } : undefined);
            recorder.addEventListener('dataavailable', (e) => { if (e.data.size) chunks.push(e.data); });
            recorder.addEventListener('stop', () => {
                stream.getTracks().forEach((t) => t.stop());
                clearInterval(timer);
                const type = (recorder.mimeType || mimeType || 'audio/webm').split(';')[0];
                const ext = type.includes('mp4') ? 'm4a' : type.includes('ogg') ? 'ogg' : 'webm';
                const blob = new Blob(chunks, { type });
                const dt = new DataTransfer();
                dt.items.add(new File([blob], `message-vocal.${ext}`, { type }));
                input.files = dt.files;
                previewUrl = URL.createObjectURL(blob);
                preview.src = previewUrl;
                preview.hidden = false;
                reset.hidden = false;
                stop.hidden = true;
                status.textContent = 'Vocal prêt. Écoutez-le, puis enregistrez le message.';
            });
            recorder.start();
            const began = Date.now();
            start.hidden = true;
            stop.hidden = false;
            time.textContent = '0:00';
            timer = setInterval(() => {
                const s = Math.floor((Date.now() - began) / 1000);
                time.textContent = `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
                if (s >= MAX_SECONDS) recorder.stop();
            }, 250);
            status.textContent = 'Enregistrement en cours… (5 minutes maximum)';
        });

        stop.addEventListener('click', () => recorder?.state === 'recording' && recorder.stop());
        reset.addEventListener('click', clear);
        input.addEventListener('change', () => {
            if (!input.files.length || input.files[0].name.startsWith('message-vocal.')) return;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            preview.hidden = true;
            reset.hidden = true;
            status.textContent = '';
        });
    });

    // Menus « ⋯ » : un seul ouvert à la fois, fermeture au clic extérieur ou Échap
    const menus = document.querySelectorAll('.row-menu');
    // Le menu est placé en position fixe pour ne pas être coupé par le tableau défilant.
    const place = (m) => {
        const pop = m.querySelector('.row-menu-pop');
        const r = m.querySelector('summary').getBoundingClientRect();
        pop.style.position = 'fixed';
        pop.style.right = `${Math.max(8, window.innerWidth - r.right)}px`;
        const below = r.bottom + 6 + pop.offsetHeight <= window.innerHeight;
        pop.style.top = below ? `${r.bottom + 6}px` : `${Math.max(8, r.top - 6 - pop.offsetHeight)}px`;
    };
    menus.forEach((m) => m.addEventListener('toggle', () => {
        if (!m.open) return;
        menus.forEach((o) => { if (o !== m) o.open = false; });
        place(m);
    }));
    window.addEventListener('scroll', () => menus.forEach((m) => { m.open = false; }), { passive: true });
    window.addEventListener('resize', () => menus.forEach((m) => { m.open = false; }));
    document.addEventListener('click', (e) => menus.forEach((m) => { if (!m.contains(e.target)) m.open = false; }));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') menus.forEach((m) => { m.open = false; }); });

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
