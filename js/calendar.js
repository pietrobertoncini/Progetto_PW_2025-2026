// Gestisce il caricamento dinamico della griglia oraria e 
// la navigazione tra le settimane senza ricaricare la pagina
document.addEventListener("DOMContentLoaded", function () {
    const selectSala = document.getElementById('sala');
    const containerCalendario = document.getElementById('calendario-container');
    const navRow = document.getElementById('nav-row');
    const btnSubmit = document.getElementById('btn-submit-row');
    const hiddenInputSala = document.getElementById('hidden-sala');
    const hiddenWeekInput = document.querySelector('input[name="week"]');

    // Se non siamo nella pagina giusta, usciamo
    if (!containerCalendario) return;

    // Funzione dedicata al recupero dei dati dal server e all'aggiornamento dell'interfaccia grafica
    function loadCalendar(salaVal, weekVal) {
        let mode = 'view';
        if (window.location.href.includes('prenota.php')) mode = 'prenota';
        if (window.location.href.includes('admin_prenotazioni.php')) mode = 'admin';
        if (window.location.href.includes('impegni.php')) mode = 'impegni';

        // Reindirizza alla lista globale se viene deselezionata la sala specifica
        if (mode !== 'impegni' && (!salaVal || salaVal === "")) {
            const newUrl = new URL(window.location);
            newUrl.searchParams.delete('sala');
            window.location.href = newUrl.toString();
            return;
        }

        let idSettoreParam = '';
        let cleanNomeSala = salaVal;

        // Estrae l'identificativo del settore e il nome della sala dalla stringa del filtro
        if (salaVal && salaVal.indexOf('|') !== -1) {
            const parts = salaVal.split('|');
            idSettoreParam = '&id_settore=' + parts[0];
            cleanNomeSala = parts[1];
        }

        // Sincronizza i campi nascosti del modulo con i parametri di ricerca correnti
        if (hiddenWeekInput) hiddenWeekInput.value = weekVal;
        if (hiddenInputSala) hiddenInputSala.value = cleanNomeSala;

        // Modifica l'indirizzo mostrato nel browser per riflettere lo stato attuale della navigazione
        const newUrl = new URL(window.location);

        if (mode !== 'impegni') {
            const valoreUrl = (mode === 'prenota') ? cleanNomeSala : salaVal;
            newUrl.searchParams.set('sala', valoreUrl);
        }

        newUrl.searchParams.set('week', weekVal);
        window.history.pushState({}, '', newUrl);

        // Applica un effetto visivo di caricamento e avvia la richiesta asincrona al server
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
                    // Inserisce il contenuto html generato dal server all'interno della pagina
                    containerCalendario.innerHTML = data.html;

                    // Mostra navigazione e tasti
                    if (navRow) navRow.classList.remove('d-none');
                    if (btnSubmit) btnSubmit.classList.remove('d-none');

                    // Aggiorna le date e i collegamenti presenti nella barra di navigazione del calendario
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

    // Monitora il cambio di selezione della sala per ricaricare i dati corrispondenti
    if (selectSala) {
        selectSala.addEventListener('change', function () {
            const currentWeek = hiddenWeekInput ? hiddenWeekInput.value : new Date().toISOString().slice(0, 10);
            loadCalendar(this.value, currentWeek);
        });
    }

    // Gestisce l'interazione con i pulsanti di cambio settimana aggiornando la vista
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

    // Calcola e aggiorna le etichette temporali mostrate sopra la griglia del calendario
    function updateNavigationUI(salaVal, currentWeekDateStr) {
        const d = new Date(currentWeekDateStr);

        // Calcola date per prev/next
        const prevD = new Date(d); prevD.setDate(d.getDate() - 7);
        const nextD = new Date(d); nextD.setDate(d.getDate() + 7);
        const sundayD = new Date(d); sundayD.setDate(d.getDate() + 6);

        const prevWeekStr = prevD.toISOString().slice(0, 10);
        const nextWeekStr = nextD.toISOString().slice(0, 10);

        // Formattazione (dd/mm)
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

        // Aggiorna Titolo
        const titleEl = navRow.querySelector('h5');
        if (titleEl) {
            titleEl.innerHTML = `Dal ${fmt(d)} al ${fmt(sundayD)}`;
        }
    }
});