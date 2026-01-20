<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Controllo Login
if (!isset($_SESSION['is_responsabile'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$id_utente = $_SESSION['id_utente'];
$dati_utente = datiUtenteCompleti($cid, $id_utente);
$id_settore_utente = $dati_utente['id_settore'];

// PARAMETRI URL
$id_sala_selezionata = null;
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['sala'])) {
    $id_sala_selezionata = $_POST['sala'];
} elseif (isset($_GET['sala'])) {
    $id_sala_selezionata = $_GET['sala'];
}

$data_rif = date('Y-m-d'); // Default oggi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['week'])) {
    $data_rif = $_POST['week'];
} elseif (isset($_GET['week'])) {
    $data_rif = $_GET['week'];
}

// LOGICA POST-SELEZIONE
$data_scelta = null;
$ora_scelta = null;
$durata_calcolata = 1;
$errore_selezione = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['slots'])) {
    $risultato = elaboraSlotSelezionati($_POST['slots']);

    if ($risultato['error']) {
        $errore_selezione = $risultato['error'];
    } else {
        $data_scelta = $risultato['data'];
        $ora_scelta = $risultato['ora'];
        $durata_calcolata = $risultato['durata'];

        // Verifichiamo SUBITO se l'utente ha già un impegno, PRIMA di mostrare il form di conferma
        if (checkSovrapposizioneUtente($cid, $id_utente, $data_scelta, $ora_scelta, $durata_calcolata, $id_settore_utente, $id_sala_selezionata)) {
            $errore_selezione = "Impossibile procedere: hai già un altro impegno confermato in questo orario.";
            // Resettiamo le variabili per impedire la visualizzazione del form di conferma
            $data_scelta = null;
            $ora_scelta = null;
        }
    }
}

// RECUPERO SALE
$sale = getSaleBySettore($cid, $id_settore_utente);

// CALCOLO SETTIMANA
$timestamp_rif = strtotime($data_rif);
$lunedi_settimana = date('Y-m-d', strtotime('monday this week', $timestamp_rif));
$domenica_settimana = date('Y-m-d', strtotime('sunday this week', $timestamp_rif));
$prev_week = date('Y-m-d', strtotime($lunedi_settimana . ' -7 days'));
$next_week = date('Y-m-d', strtotime($lunedi_settimana . ' +7 days'));

// LOGICA CALENDARIO (Solo se non siamo già in fase di conferma)
$occupied = [];
if ($id_sala_selezionata && !$data_scelta) {
    $occupied = getOccupazioniSettimana($cid, $id_sala_selezionata, $id_settore_utente, $lunedi_settimana, $domenica_settimana, $id_utente);
}

// LOGICA UTENTI E FILTRI (Fase Conferma)
$utenti_invitabili = [];
$lista_settori = [];
$capienza_sala_corrente = 0;

if ($data_scelta && $ora_scelta) {
    $utenti_invitabili = getUtentiInvitabili($cid, $id_utente, $id_settore_utente, $data_scelta, $ora_scelta);
    $lista_settori = getListaSettori($cid);

    foreach ($sale as $s) {
        if ($s['nome_sala'] == $id_sala_selezionata) {
            $capienza_sala_corrente = $s['capienza_max'];
            break;
        }
    }
}

// Se non c'è sala nascondiamo i tasti
$class_hidden = ($id_sala_selezionata && !$data_scelta) ? '' : 'd-none';
?>

<!DOCTYPE html>
<html lang="it" class="no-js">
<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
            <div class="text-center">
                <h2 class="m-0">Nuova Prenotazione</h2>
            </div>

            <?php if (!$data_scelta): ?>
                <div class="card shadow-sm border-0 rounded-4 bg-light">
                    <div class="card-body p-2">
                        <form action="<?php echo BASE_URL; ?>frontend/prenota.php" method="GET" class="d-flex align-items-center gap-2">
                            <input type="hidden" name="week" value="<?php echo $lunedi_settimana; ?>">

                            <label for="sala" class="fw-bold text-muted m-0 text-nowrap">
                                <i class="bi bi-geo-alt-fill"></i> Sala:
                            </label>

                            <select class="form-select form-select-sm rounded-pill border-secondary"
                                name="sala"
                                id="sala"
                                style="max-width: 300px; cursor: pointer;">

                                <option value="" disabled <?php echo !$id_sala_selezionata ? 'selected' : ''; ?>>-- Seleziona --</option>

                                <?php foreach ($sale as $s): ?>
                                    <option value="<?php echo htmlspecialchars($s['nome_sala']); ?>"
                                        <?php echo ($id_sala_selezionata == $s['nome_sala']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['nome_sala']); ?> (Cap.: <?php echo $s['capienza_max']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <noscript>
                                <button type="submit" class="btn btn-sm btn-secondary rounded-pill">Vai</button>
                            </noscript>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($errore_selezione): ?>
            <div class="alert alert-danger text-center shadow-sm rounded-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($errore_selezione); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center shadow-sm rounded-4"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>


        <?php if (!$data_scelta): ?>

            <form action="<?php echo BASE_URL; ?>frontend/prenota.php" method="POST" id="form-selezione-slot">
                <input type="hidden" id="hidden-sala" name="sala" value="<?php echo htmlspecialchars($id_sala_selezionata ?? ''); ?>">
                <input type="hidden" name="week" value="<?php echo $lunedi_settimana; ?>">

                <div id="nav-row" class="d-flex justify-content-between align-items-center mb-3 <?php echo $class_hidden; ?>">
                    <a href="<?php echo BASE_URL; ?>frontend/prenota.php?sala=<?php echo urlencode($id_sala_selezionata ?? ''); ?>&week=<?php echo $prev_week; ?>"
                        class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-2 nav-week-btn">&larr; Settimana Prec.</a>

                    <h5 class="mb-0 fw-bold text-center">
                        Settimana dal <?php echo date('d/m', strtotime($lunedi_settimana)); ?>
                        al <?php echo date('d/m', strtotime($domenica_settimana)); ?>
                    </h5>

                    <a href="<?php echo BASE_URL; ?>frontend/prenota.php?sala=<?php echo urlencode($id_sala_selezionata ?? ''); ?>&week=<?php echo $next_week; ?>"
                        class="btn btn-outline-secondary btn-sm rounded-pill px-3 ms-2 nav-week-btn">Settimana Succ. &rarr;</a>
                </div>

                <div id="calendario-container">
                    <?php if ($id_sala_selezionata): ?>
                        <?php
                        // Se JS non va, o al primo caricamento, renderizza questo.
                        if (function_exists('renderCalendarGrid')) {
                            echo renderCalendarGrid($lunedi_settimana, $occupied, false, false);
                        }
                        ?>
                        <!-- Se nessuna sala è selezionata -->
                    <?php else: ?>
                        <div class="mt-5 text-center">
                            <div class="mb-2 text-secondary" style="opacity: 0.3;">
                                <i class="bi bi-arrow-up-circle-fill" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="fw-bold text-secondary mb-2">Nessuna sala selezionata</h5>
                            <p class="text-muted text-secondary mb-0 mx-auto" style="max-width: 500px;">
                                Seleziona una sala dal menu in alto per visualizzare il calendario.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <div id="btn-submit-row" class="text-end mb-5 mt-3 <?php echo $class_hidden; ?>">
                    <div class="alert alert-info py-2 small border-0 bg-info bg-opacity-10 rounded-4 mb-3 text-start">
                        <i class="bi bi-info-circle-fill text-info ms-2"></i> Seleziona le caselle orarie consecutive che vuoi prenotare e premi "Procedi".
                    </div>

                    <div id="js-error-alert" class="alert alert-danger d-none rounded-4 shadow-sm mb-3 text-start">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-octagon-fill fs-4 me-2"></i>
                            <span id="js-error-msg"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg shadow rounded-pill px-4 fw-bold">
                        Procedi con la Selezione <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </form>

        <?php endif; ?>

        <!-- Fase di completamento prenotazione (descrizione attività, inviti) -->
        <?php if ($data_scelta && $ora_scelta && $id_sala_selezionata): ?>

            <div id="form-prenotazione" class="card border-0 shadow-sm rounded-4 overflow-hidden mx-auto" style="max-width: 800px;">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <h5 class="mb-0 fw-bold"></i>Completa Prenotazione</h5>
                </div>

                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                        <div>
                            <p class="mb-0 text-muted small">Sala selezionata:</p>
                            <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($id_sala_selezionata); ?></h6>
                        </div>
                        <div class="alert alert-success py-1 px-3 mb-0 small border-0 bg-success bg-opacity-10 rounded-pill">
                            <i class="bi bi-calendar-event me-1"></i>
                            <strong><?php echo date('d/m/Y', strtotime($data_scelta)); ?></strong> |
                            <i class="bi bi-clock me-1 ms-1"></i>
                            <strong><?php echo $ora_scelta; ?>:00 - <?php echo ($ora_scelta + $durata_calcolata); ?>:00</strong>
                        </div>
                    </div>

                    <form action="<?php echo BASE_URL; ?>backend/prenota_exe.php" method="POST">
                        <input type="hidden" name="id_settore" value="<?php echo $id_settore_utente; ?>">
                        <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($id_sala_selezionata); ?>">
                        <input type="hidden" name="data" value="<?php echo $data_scelta; ?>">
                        <input type="hidden" name="ora" value="<?php echo $ora_scelta; ?>">
                        <input type="hidden" name="durata" value="<?php echo $durata_calcolata; ?>">

                        <div class="mb-3">
                            <label for="attivita" class="form-label fw-bold small ps-1 mb-1">Descrizione Attività</label>
                            <input type="text" class="form-control form-control-sm rounded-3" id="attivita" name="attivita" placeholder="Es. Prove, Lezione..." required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 small text-uppercase text-muted">Invita Partecipanti</h6>
                            <span class="badge bg-primary px-2 py-1" style="font-size: 0.75rem;">
                                <i class="bi bi-people-fill me-1"></i>
                                <span id="counter-text">1</span> / <?php echo $capienza_sala_corrente; ?> Posti
                            </span>
                        </div>

                        <input type="hidden" id="maxCapienza" value="<?php echo $capienza_sala_corrente; ?>">

                        <div class="row g-2 mb-2">
                            <div class="col-md-4">
                                <select id="filtroSettore" class="form-select form-select-sm rounded-pill" onchange="applicaFiltri()">
                                    <option value="all">Tutti i Settori</option>
                                    <?php if (!empty($lista_settori)): ?>
                                        <?php foreach ($lista_settori as $sett): ?>
                                            <option value="<?php echo $sett['id_settore']; ?>" <?php echo ($sett['id_settore'] == $id_settore_utente) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($sett['nome']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select id="filtroRuolo" class="form-select form-select-sm rounded-pill" onchange="applicaFiltri()">
                                    <option value="all">Tutti i Ruoli</option>
                                    <option value="docente">Docenti</option>
                                    <option value="allievo">Allievi</option>
                                    <option value="tecnico">Tecnici</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-xs btn-outline-dark rounded-pill w-100 py-1 fw-bold" id="btnSelectAll" onclick="toggleSelezionaTutti()" style="font-size: 0.7rem;">
                                    <i class="bi bi-check-all"></i> SELEZIONA TUTTI
                                </button>
                            </div>
                        </div>

                        <div class="row g-1 mb-3 p-1 border rounded bg-light" style="max-height: 220px; overflow-y: auto;">
                            <?php if (!empty($utenti_invitabili)): ?>
                                <?php foreach ($utenti_invitabili as $u):
                                    $ruolo = strtolower($u['ruolo']);
                                    $id_sett_user = $u['id_settore'];
                                    $badge_class = 'bg-secondary';
                                    if (isset($u['tipo_settore'])) {
                                        if ($u['tipo_settore'] == 'musica') $badge_class = 'bg-primary';
                                        if ($u['tipo_settore'] == 'teatro') $badge_class = 'bg-danger';
                                        if ($u['tipo_settore'] == 'ballo') $badge_class = 'bg-success';
                                    }
                                ?>
                                    <div class="col-md-6 user-item" data-id-settore="<?php echo $id_sett_user; ?>" data-ruolo="<?php echo $ruolo; ?>">
                                        <div class="form-check p-1 px-2 border rounded-3 bg-white h-100 shadow-sm d-flex align-items-center" style="min-height: 45px;">
                                            <input class="form-check-input ms-0 me-2 my-0 user-checkbox" type="checkbox" name="invitati[]" value="<?php echo $u['id_utente']; ?>" id="user_<?php echo $u['id_utente']; ?>" onchange="aggiornaContatore()" style="width: 1rem; height: 1rem;">
                                            <label class="form-check-label w-100 lh-1" style="cursor: pointer; font-size: 0.8rem;" for="user_<?php echo $u['id_utente']; ?>">
                                                <span class="d-block fw-bold text-dark mb-1 text-truncate" style="max-width: 150px;">
                                                    <?php echo htmlspecialchars($u['nome'] . " " . $u['cognome']); ?>
                                                </span>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <?php if ($u['is_responsabile']): ?>
                                                        <span class="badge bg-dark" style="font-size: 0.55rem;">RESP.</span>
                                                    <?php endif; ?>
                                                    <span class="badge bg-light text-secondary border" style="font-size: 0.55rem;"><?php echo strtoupper($u['ruolo']); ?></span>
                                                    <?php if ($u['id_settore'] != $id_settore_utente): ?>
                                                        <span class="badge <?php echo $badge_class; ?> bg-opacity-75" style="font-size: 0.55rem;"><?php echo htmlspecialchars(substr($u['nome_settore'], 0, 15)); ?>...</span>
                                                    <?php endif; ?>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-3 text-muted small">Nessun utente disponibile.</div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="<?php echo BASE_URL; ?>frontend/prenota.php?sala=<?php echo urlencode($id_sala_selezionata); ?>&week=<?php echo $lunedi_settimana; ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Annulla</a>
                            <button type="submit" class="btn btn-sm btn-success px-4 fw-bold rounded-pill">Conferma Prenotazione</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <script src="<?php echo BASE_URL; ?>js/calendar.js"></script>

    <script>
        function aggiornaContatore() {
            const maxCapienza = parseInt(document.getElementById('maxCapienza').value);
            const checkboxes = document.querySelectorAll('.user-checkbox');
            let checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
            let occupati = checkedCount + 1; // +1 per l'organizzatore

            const counterSpan = document.getElementById('counter-text');
            if (counterSpan) counterSpan.innerText = occupati;

            // Logica disabilitazione
            const limitReached = occupati >= maxCapienza;
            if (limitReached) {
                if (counterSpan) {
                    counterSpan.parentElement.classList.remove('bg-primary');
                    counterSpan.parentElement.classList.add('bg-danger');
                }
            } else {
                if (counterSpan) {
                    counterSpan.parentElement.classList.remove('bg-danger');
                    counterSpan.parentElement.classList.add('bg-primary');
                }
            }

            // Disabilita i non selezionati solo se limite raggiunto
            checkboxes.forEach(cb => {
                if (!cb.checked) {
                    cb.disabled = limitReached;
                    if (limitReached) cb.closest('.form-check').classList.add('opacity-50');
                    else cb.closest('.form-check').classList.remove('opacity-50');
                } else {
                    cb.disabled = false;
                    cb.closest('.form-check').classList.remove('opacity-50');
                }
            });
        }

        function applicaFiltri() {
            const settoreSelezionato = document.getElementById('filtroSettore').value;
            const ruoloSelezionato = document.getElementById('filtroRuolo').value;
            const items = document.querySelectorAll('.user-item');

            items.forEach(item => {
                const itemSettore = item.getAttribute('data-id-settore');
                const itemRuolo = item.getAttribute('data-ruolo');
                const matchSettore = (settoreSelezionato === 'all') || (itemSettore === settoreSelezionato);
                const matchRuolo = (ruoloSelezionato === 'all') || (itemRuolo === ruoloSelezionato);

                // Gestione checkbox e visualizzazione
                const checkbox = item.querySelector('.user-checkbox');
                if (matchSettore && matchRuolo) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                    if (checkbox) checkbox.checked = false; // Deseleziona se nascosto
                }
            });
            aggiornaContatore();
        }

        function toggleSelezionaTutti() {
            const maxCapienza = parseInt(document.getElementById('maxCapienza').value);
            // Consideriamo solo gli utenti visibili dai filtri
            const visibleItems = Array.from(document.querySelectorAll('.user-item')).filter(item => item.style.display !== 'none');

            // Calcoli attuali
            const totalChecked = document.querySelectorAll('.user-checkbox:checked').length;
            const currentOccupied = totalChecked + 1; // +1 per l'organizzatore

            // Quanti tra quelli VISIBILI sono attualmente selezionati?
            const visibleCheckedCount = visibleItems.filter(item => item.querySelector('.user-checkbox').checked).length;
            const allVisibleAreChecked = (visibleItems.length > 0 && visibleCheckedCount === visibleItems.length);

            // Condizione "Siamo pieni?": Sì se occupati >= capienza
            const isFull = currentOccupied >= maxCapienza;

            // LOGICA INTELLIGENTE:
            // Deselezioniamo se:
            // 1. Tutti quelli che vedo sono già selezionati (comportamento standard)
            // 2. OPPURE: Siamo pieni E ho almeno un selezionato visibile (il caso che non ti funzionava)
            if (allVisibleAreChecked || (isFull && visibleCheckedCount > 0)) {
                // DESELEZIONA TUTTI I VISIBILI
                visibleItems.forEach(item => {
                    item.querySelector('.user-checkbox').checked = false;
                });
            } else {
                // SELEZIONA (fino a riempimento)
                let slotsFree = maxCapienza - currentOccupied;

                visibleItems.forEach(item => {
                    const cb = item.querySelector('.user-checkbox');
                    // Seleziona solo se non è già checkato e se abbiamo spazio
                    if (!cb.checked && slotsFree > 0) {
                        cb.checked = true;
                        slotsFree--;
                    }
                });
            }

            aggiornaContatore();
        }

        document.addEventListener("DOMContentLoaded", function() {
            if (document.getElementById('filtroSettore')) {
                applicaFiltri(); // Inizializza stato
            }

            // Validazione selezione slot
            const formSelezione = document.getElementById('form-selezione-slot');
            const errorAlert = document.getElementById('js-error-alert');
            const errorMsg = document.getElementById('js-error-msg');
            const selectSala = document.getElementById('sala'); // Recuperiamo il select della sala

            if (formSelezione) {
                formSelezione.addEventListener('submit', function(e) {
                    // Conta quante caselle orarie sono state selezionate
                    const slotsSelezionati = formSelezione.querySelectorAll('input[name="slots[]"]:checked');

                    if (slotsSelezionati.length === 0) {
                        // Blocca l'invio del form
                        e.preventDefault();

                        // Mostra l'alert di Bootstrap invece di quello del browser
                        if (errorAlert && errorMsg) {
                            errorMsg.textContent = "Attenzione: devi selezionare almeno un'ora nel calendario per procedere.";
                            errorAlert.classList.remove('d-none'); // Rende visibile l'avviso

                            // Scorri la pagina verso l'errore per renderlo visibile
                            errorAlert.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    } else {
                        // Se ci sono selezioni, nascondi l'eventuale errore precedente
                        if (errorAlert) errorAlert.classList.add('d-none');
                    }
                });
            }

            // Nasconde l'alert quando si cambia sala
            if (selectSala) {
                selectSala.addEventListener('change', function() {
                    if (errorAlert) {
                        errorAlert.classList.add('d-none');
                    }
                });
            }
        });
    </script>

    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>

</html>