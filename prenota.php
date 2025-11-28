<?php
// prenota.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'common/setup.php';
require_once 'common/function.php';

// Controllo Login
if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit;
}

$id_utente = $_SESSION['id_utente'];
$dati_utente = datiUtenteCompleti($cid, $id_utente);
$id_settore_utente = $dati_utente['id_settore'];

// --- PARAMETRI ---
$id_sala_selezionata = isset($_REQUEST['sala']) ? $_REQUEST['sala'] : null;
$data_rif = isset($_REQUEST['week']) ? $_REQUEST['week'] : date('Y-m-d');

// --- LOGICA POST-SELEZIONE (Passaggio 2 -> 3) ---
$data_scelta = null;
$ora_scelta = null;
$durata_calcolata = 1;
$errore_selezione = null;

// Se l'utente ha inviato il modulo del calendario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['slots'])) {
    $slots = $_POST['slots']; // Array di stringhe "YYYY-MM-DD|HH"
    
    if (empty($slots)) {
        $errore_selezione = "Seleziona almeno un orario.";
    } else {
        // Ordiniamo gli slot per averli in ordine cronologico (es. 10, 11, 12)
        sort($slots);
        
        // Analisi del primo slot
        $first_slot = explode('|', $slots[0]);
        $data_check = $first_slot[0];
        $prev_ora = (int)$first_slot[1];
        
        $consecutivi = true;
        
        // --- 1. CONTROLLO CONSECUTIVITÀ E STESSO GIORNO ---
        foreach ($slots as $index => $slot) {
            if ($index === 0) continue; // Saltiamo il primo
            
            $parts = explode('|', $slot);
            $curr_data = $parts[0];
            $curr_ora = (int)$parts[1];

            // Controllo Giorno
            if ($curr_data !== $data_check) {
                $errore_selezione = "Puoi selezionare orari solo per un singolo giorno alla volta.";
                $consecutivi = false;
                break;
            }
            
            // Controllo Consecutività (L'ora attuale deve essere Precedente + 1)
            if ($curr_ora !== ($prev_ora + 1)) {
                $errore_selezione = "Errore: Hai selezionato orari non consecutivi (es. 10 e 12). Seleziona solo ore di fila.";
                $consecutivi = false;
                break;
            }
            
            $prev_ora = $curr_ora; // Aggiorna per il prossimo giro
        }

        // Se tutto è andato bene
        if ($consecutivi) {
            $last_slot = explode('|', end($slots));
            $ora_fine_slot = (int)$last_slot[1];
            
            $ora_inizio = (int)$first_slot[1];
            
            // Durata = (Ultima Ora - Prima Ora) + 1. Es: 10, 11 -> (11-10)+1 = 2 ore.
            $durata_calcolata = ($ora_fine_slot - $ora_inizio) + 1;
            
            // Impostiamo le variabili per mostrare il form finale
            $data_scelta = $data_check;
            $ora_scelta = $ora_inizio;
        }
    }
}

// --- RECUPERO SALE DISPONIBILI ---
$sql = "SELECT * FROM SALA WHERE id_settore = ? ORDER BY nome_sala";
$stmt = $cid->prepare($sql);
$stmt->bind_param("i", $id_settore_utente);
$stmt->execute();
$sale = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// --- CALCOLO SETTIMANA ---
$timestamp_rif = strtotime($data_rif);
$lunedi_settimana = date('Y-m-d', strtotime('monday this week', $timestamp_rif));
$domenica_settimana = date('Y-m-d', strtotime('sunday this week', $timestamp_rif));
$prev_week = date('Y-m-d', strtotime($lunedi_settimana . ' -7 days'));
$next_week = date('Y-m-d', strtotime($lunedi_settimana . ' +7 days'));

// --- LOGICA CALENDARIO (Occupazione) ---
$occupied = []; 
if ($id_sala_selezionata) {
    $sql_p = "SELECT data, ora, durata FROM PRENOTAZIONE 
              WHERE nome_sala = ? AND id_settore = ? 
              AND data BETWEEN ? AND ?";
    $stmt_p = $cid->prepare($sql_p);
    $stmt_p->bind_param("siss", $id_sala_selezionata, $id_settore_utente, $lunedi_settimana, $domenica_settimana);
    $stmt_p->execute();
    $res_p = $stmt_p->get_result();
    while ($row = $res_p->fetch_assoc()) {
        for ($i = 0; $i < $row['durata']; $i++) {
            $occupied[$row['data']][$row['ora'] + $i] = true;
        }
    }
}

// --- LOGICA UTENTI DISPONIBILI (Per il form finale) ---
$utenti_invitabili = [];
if ($data_scelta && $ora_scelta) {
    $sql_u = "SELECT id_utente, nome, cognome, ruolo FROM UTENTE WHERE id_utente != ? AND id_settore = ? ORDER BY cognome";
    $stmt_u = $cid->prepare($sql_u);
    $stmt_u->bind_param("ii", $id_utente, $id_settore_utente);
    $stmt_u->execute();
    $utenti_invitabili = $stmt_u->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>
    <?php include 'common/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        
        <h2 class="mb-4 text-center fw-bold" style="color: #7A5E4E;">Nuova Prenotazione</h2>
        
        <?php if ($errore_selezione): ?>
            <div class="alert alert-danger text-center shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($errore_selezione); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center shadow-sm"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-body bg-light">
                <form action="prenota.php" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="week" value="<?php echo $lunedi_settimana; ?>">
                    <div class="col-md-8">
                        <label for="sala" class="form-label fw-bold">1. Scegli la Sala:</label>
                        <select class="form-select" name="sala" id="sala" onchange="this.form.submit()">
                            <option value="" disabled <?php echo !$id_sala_selezionata ? 'selected' : ''; ?>>-- Seleziona --</option>
                            <?php foreach ($sale as $s): ?>
                                <option value="<?php echo htmlspecialchars($s['nome_sala']); ?>" 
                                    <?php echo ($id_sala_selezionata == $s['nome_sala']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['nome_sala']); ?> (Capienza: <?php echo $s['capienza_max']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Aggiorna Calendario</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($id_sala_selezionata && !$data_scelta): ?>
            
            <form action="prenota.php" method="POST">
                <input type="hidden" name="sala" value="<?php echo htmlspecialchars($id_sala_selezionata); ?>">
                <input type="hidden" name="week" value="<?php echo $lunedi_settimana; ?>">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="prenota.php?sala=<?php echo urlencode($id_sala_selezionata); ?>&week=<?php echo $prev_week; ?>" class="btn btn-outline-secondary btn-sm">&larr; Settimana Prec.</a>
                    <h5 class="mb-0 fw-bold">Settimana dal <?php echo date('d/m', strtotime($lunedi_settimana)); ?> al <?php echo date('d/m', strtotime($domenica_settimana)); ?></h5>
                    <a href="prenota.php?sala=<?php echo urlencode($id_sala_selezionata); ?>&week=<?php echo $next_week; ?>" class="btn btn-outline-secondary btn-sm">Settimana Succ. &rarr;</a>
                </div>

                <div class="alert alert-info py-2 small border-0 bg-info bg-opacity-10">
                    <i class="bi bi-info-circle-fill text-info"></i> Seleziona le caselle orarie consecutive che vuoi prenotare e premi "Procedi".
                </div>

                <div class="table-responsive shadow-sm mb-4">
                    <table class="table table-bordered calendar-table mb-0 bg-white text-center">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Ora</th>
                                <?php 
                                $giorni = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
                                foreach($giorni as $index => $giorno) {
                                    $d = date('d/m', strtotime($lunedi_settimana . " +$index days"));
                                    echo "<th>$giorno<br><small class='fw-normal'>$d</small></th>";
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($ora = 9; $ora <= 23; $ora++): ?>
                                <tr>
                                    <td class="fw-bold bg-light align-middle"><?php echo $ora; ?>:00</td>
                                    <?php for ($i = 0; $i < 7; $i++): 
                                        $data_curr = date('Y-m-d', strtotime($lunedi_settimana . " +$i days"));
                                        $is_occupied = isset($occupied[$data_curr][$ora]);
                                        $is_past = (strtotime($data_curr . " " . $ora . ":00") < time());
                                        $value = $data_curr . "|" . $ora;
                                    ?>
                                        <?php if ($is_occupied): ?>
                                            <td class="cell-occupied align-middle" title="Occupato"><i class="bi bi-x-circle"></i></td>
                                        <?php elseif ($is_past): ?>
                                            <td class="bg-light text-muted small align-middle">-</td>
                                        <?php else: ?>
                                            <td class="cell-free p-0">
                                                <label class="check-container">
                                                    <input type="checkbox" name="slots[]" value="<?php echo $value; ?>">
                                                </label>
                                            </td>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-end mb-5">
                    <button type="submit" class="btn btn-success btn-lg shadow">
                        Procedi con la Selezione <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </form>

        <?php endif; ?>

        <?php if ($data_scelta && $ora_scelta && $id_sala_selezionata): ?>
            <div id="form-prenotazione" class="card border-primary shadow rounded-3">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Completa Prenotazione</h4>
                </div>
                <div class="card-body p-4">
                    <p class="lead mb-2">Sala: <strong><?php echo htmlspecialchars($id_sala_selezionata); ?></strong></p>
                    
                    <div class="alert alert-success border-success border-opacity-25 bg-success bg-opacity-10">
                        <i class="bi bi-calendar-check-fill text-success me-2"></i>
                        Prenotazione per il giorno <strong><?php echo date('d/m/Y', strtotime($data_scelta)); ?></strong><br>
                        <span class="ms-4">
                            Dalle ore <strong><?php echo $ora_scelta; ?>:00</strong> 
                            alle ore <strong><?php echo ($ora_scelta + $durata_calcolata); ?>:00</strong>.
                        </span>
                    </div>

                    <form action="backend/prenota_exe.php" method="POST">
                        <input type="hidden" name="id_settore" value="<?php echo $id_settore_utente; ?>">
                        <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($id_sala_selezionata); ?>">
                        <input type="hidden" name="data" value="<?php echo $data_scelta; ?>">
                        <input type="hidden" name="ora" value="<?php echo $ora_scelta; ?>">
                        <input type="hidden" name="durata" value="<?php echo $durata_calcolata; ?>">

                        <div class="mb-3">
                            <label for="attivita" class="form-label fw-bold">Descrizione Attività</label>
                            <input type="text" class="form-control" id="attivita" name="attivita" placeholder="Es. Prove, Lezione..." required>
                        </div>

                        <hr>
                        
                        <h5 class="fw-bold mb-3">Invita Utenti (Opzionale)</h5>
                        <div class="row g-2 mb-4" style="max-height: 200px; overflow-y: auto;">
                            <?php if (!empty($utenti_invitabili)): ?>
                                <?php foreach ($utenti_invitabili as $u): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-check p-2 border rounded bg-white">
                                            <input class="form-check-input ms-1" type="checkbox" name="invitati[]" value="<?php echo $u['id_utente']; ?>" id="user_<?php echo $u['id_utente']; ?>">
                                            <label class="form-check-label ms-2 w-75" for="user_<?php echo $u['id_utente']; ?>">
                                                <?php echo htmlspecialchars($u['nome'] . " " . $u['cognome']); ?>
                                                <small class="text-muted d-block"><?php echo ucfirst($u['ruolo']); ?></small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">Nessun altro utente disponibile nel tuo settore.</p>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="prenota.php?sala=<?php echo urlencode($id_sala_selezionata); ?>&week=<?php echo $lunedi_settimana; ?>" class="btn btn-secondary">Annulla</a>
                            <button type="submit" class="btn btn-success px-4 fw-bold">Conferma Prenotazione</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <?php include 'common/footer.html'; ?>
</body>
</html>