document.addEventListener("DOMContentLoaded", function () {
    const selectSala = document.getElementById('sala');
    const containerCalendario = document.getElementById('calendario-container');
    const navRow = document.getElementById('nav-row');
    const btnSubmit = document.getElementById('btn-submit-row');
    const hiddenInputSala = document.getElementById('hidden-sala');
    const hiddenWeekInput = document.querySelector('input[name="week"]');

    // Se non siamo nella pagina giusta, usciamo
    if (!containerCalendario) return;

    // --- FUNZIONE PRINCIPALE DI CARICAMENTO ---
    function loadCalendar(salaVal, weekVal) {
        let mode = 'view';
        if (window.location.href.includes('prenota.php')) mode = 'prenota';
        if (window.location.href.includes('admin_prenotazioni.php')) mode = 'admin';
        if (window.location.href.includes('impegni.php')) mode = 'impegni';

        // Se seleziono "Tutte", ricarico la pagina pulita per vedere la lista
        if (mode !== 'impegni' && (!salaVal || salaVal === "")) {
            const newUrl = new URL(window.location);
            newUrl.searchParams.delete('sala');
            window.location.href = newUrl.toString();
            return;
        }

        let idSettoreParam = '';
        let cleanNomeSala = salaVal; // Default per 'prenota'

        // Parsing "ID|Nome" per Admin e Visualizza
        if (salaVal && salaVal.indexOf('|') !== -1) {
            const parts = salaVal.split('|');
            idSettoreParam = '&id_settore=' + parts[0];
            cleanNomeSala = parts[1];
        }

        // 1. Aggiorna Input Hidden (se presenti)
        if (hiddenWeekInput) hiddenWeekInput.value = weekVal;
        if (hiddenInputSala) hiddenInputSala.value = cleanNomeSala;

        // 2. Aggiorna URL Browser (senza reload)
        const newUrl = new URL(window.location);

        // BUGFIX: Se siamo in Admin/View dobbiamo salvare nell'URL il valore "ID|Nome" (salaVal), 
        // altrimenti al reload PHP non trova la sala. In 'prenota' basta il nome.
        if (mode !== 'impegni') {
            const valoreUrl = (mode === 'prenota') ? cleanNomeSala : salaVal;
            newUrl.searchParams.set('sala', valoreUrl);
        }

        newUrl.searchParams.set('week', weekVal);
        window.history.pushState({}, '', newUrl);

        // 3. Feedback visivo e chiamata AJAX
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

                    // Mostra navigazione e tasti
                    if (navRow) navRow.classList.remove('d-none');
                    if (btnSubmit) btnSubmit.classList.remove('d-none');

                    // AGGIORNA I LINK E IL TITOLO DELLA SETTIMANA
                    updateNavigationUI(salaVal, weekVal);
                } else {
                    containerCalendario.innerHTML = '<div class="alert alert-danger">Errore: ' + data.msg + '</div>';
                }
            })
            .catch(err => {
                console.error(err);
                containerCalendario.style.opacity = '1';
                containerCalendario.innerHTML = '<div class="alert alert-danger">Errore di comunicazione col server.</div>';
            });
    }

    // --- LISTENER CAMBIO SALA ---
    if (selectSala) {
        selectSala.addEventListener('change', function () {
            const currentWeek = hiddenWeekInput ? hiddenWeekInput.value : new Date().toISOString().slice(0, 10);
            loadCalendar(this.value, currentWeek);
        });
    }

    // --- LISTENER CAMBIO SETTIMANA (Cliccando sulle frecce) ---
    if (navRow) {
        navRow.addEventListener('click', function (e) {
            // Intercetta click sui bottoni che hanno la classe .nav-week-btn
            const btn = e.target.closest('.nav-week-btn');
            if (btn) {
                e.preventDefault(); // BLOCCA il reload della pagina

                // Legge la settimana target dall'href del bottone cliccato
                const url = new URL(btn.href);
                const targetWeek = url.searchParams.get('week');

                // In impegni non c'è selectSala, quindi passiamo stringa vuota
                const sala = selectSala ? selectSala.value : '';

                if (targetWeek) {
                    loadCalendar(sala, targetWeek);
                }
            }
        });
    }

    // --- FUNZIONE HELPER: Aggiorna UI Navigazione ---
    function updateNavigationUI(salaVal, currentWeekDateStr) {
        const d = new Date(currentWeekDateStr);

        // Calcola date per prev/next
        const prevD = new Date(d); prevD.setDate(d.getDate() - 7);
        const nextD = new Date(d); nextD.setDate(d.getDate() + 7);
        const sundayD = new Date(d); sundayD.setDate(d.getDate() + 6);

        const prevWeekStr = prevD.toISOString().slice(0, 10);
        const nextWeekStr = nextD.toISOString().slice(0, 10);

        // Helper formattazione (dd/mm)
        const fmt = (dateObj) => dateObj.getDate().toString().padStart(2, '0') + '/' + (dateObj.getMonth() + 1).toString().padStart(2, '0');

        // Aggiorna HREF dei bottoni
        const btns = document.querySelectorAll('.nav-week-btn');
        if (btns.length >= 2) {
            // Bottone Sinistro (Prec)
            const urlPrev = new URL(btns[0].href, window.location.origin);
            urlPrev.searchParams.set('sala', salaVal);
            urlPrev.searchParams.set('week', prevWeekStr);
            btns[0].href = urlPrev.toString();

            // Bottone Destro (Succ)
            const urlNext = new URL(btns[1].href, window.location.origin);
            urlNext.searchParams.set('sala', salaVal);
            urlNext.searchParams.set('week', nextWeekStr);
            btns[1].href = urlNext.toString();
        }

        // Aggiorna Titolo (es. "Dal 10/12 al 16/12")
        const titleEl = navRow.querySelector('h5');
        if (titleEl) {
            titleEl.innerHTML = `Dal ${fmt(d)} al ${fmt(sundayD)}`;
        }
    }
});