document.addEventListener("DOMContentLoaded", function () {
    const selectSala = document.getElementById('sala');
    const containerCalendario = document.getElementById('calendario-container');
    const navRow = document.getElementById('nav-row');
    const btnSubmit = document.getElementById('btn-submit-row');
    const hiddenInputSala = document.getElementById('hidden-sala');

    // Se non siamo nella pagina giusta (elementi non trovati), usciamo
    if (!selectSala || !containerCalendario) return;

    selectSala.addEventListener('change', function () {
        const valSala = this.value;
        const weekInput = document.querySelector('input[name="week"]');
        const weekVal = weekInput ? weekInput.value : new Date().toISOString().slice(0, 10);

        let mode = 'view';
        if (window.location.href.includes('prenota.php')) mode = 'prenota';
        if (window.location.href.includes('admin_prenotazioni.php')) mode = 'admin';

        let idSettoreParam = '';
        let cleanNomeSala = valSala;

        // Gestione caso Admin (value="ID|Nome")
        if (valSala.indexOf('|') !== -1) {
            const parts = valSala.split('|');
            idSettoreParam = '&id_settore=' + parts[0];
            cleanNomeSala = parts[1];
        }

        if (!cleanNomeSala) return;

        // 1. Aggiorna Input Hidden per il submit del form
        if (hiddenInputSala) {
            hiddenInputSala.value = cleanNomeSala;
        }

        // 2. Aggiorna URL del browser senza ricaricare
        if (mode === 'prenota') {
            const newUrl = new URL(window.location);
            newUrl.searchParams.set('sala', cleanNomeSala);
            window.history.pushState({}, '', newUrl);
        }

        // 3. Feedback visivo e Chiamata AJAX
        containerCalendario.style.opacity = '0.5';

        const apiUrl = '../backend/api_get_calendar.php?sala=' + encodeURIComponent(cleanNomeSala) +
            '&week=' + weekVal +
            '&mode=' + mode +
            idSettoreParam;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                containerCalendario.style.opacity = '1';
                if (data.status === 'ok') {
                    containerCalendario.innerHTML = data.html;

                    if (navRow) navRow.classList.remove('d-none');
                    if (btnSubmit) btnSubmit.classList.remove('d-none');

                    updateNavigationLinks(valSala);
                } else {
                    containerCalendario.innerHTML = '<div class="alert alert-danger">Errore: ' + data.msg + '</div>';
                }
            })
            .catch(err => {
                console.error(err);
                containerCalendario.style.opacity = '1';
                containerCalendario.innerHTML = '<div class="alert alert-danger">Errore di comunicazione col server.</div>';
            });
    });

    function updateNavigationLinks(valoreSelect) {
        const navLinks = document.querySelectorAll('.nav-week-btn');
        navLinks.forEach(link => {
            try {
                const url = new URL(link.href, window.location.origin);
                url.searchParams.set('sala', valoreSelect);
                link.href = url.toString();
            } catch (e) {
                // Ignora errori di parsing URL
            }
        });
    }
});