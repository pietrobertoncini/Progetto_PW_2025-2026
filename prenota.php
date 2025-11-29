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

// --- PARAMETRI URL ---
$id_sala_selezionata = isset($_REQUEST['sala']) ? $_REQUEST['sala'] : null;
$data_rif = isset($_REQUEST['week']) ? $_REQUEST['week'] : date('Y-m-d');

// --- LOGICA POST-SELEZIONE ---
$data_scelta = null;
$ora_scelta = null;
$durata_calcolata = 1;
$errore_selezione = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['slots'])) {
    $slots = $_POST['slots']; 
    
    if (empty($slots)) {
        $errore_selezione = "Seleziona almeno un orario.";
    } else {
        $ore_selezionate = [];
        $giorno_riferimento = null;
        $giorno_diverso = false;

        foreach ($slots as $slot) {
            $parts = explode('|', $slot);
            $d = $parts[0];
            $h = (int)$parts[1];

            if ($giorno_riferimento === null) $giorno_riferimento = $d;
            if ($d !== $giorno_riferimento) $giorno_diverso = true;

            $ore_selezionate[] = $h;
        }

        if ($giorno_diverso) {
            $errore_selezione = "Puoi selezionare orari solo per un singolo giorno alla volta.";
        } else {
            sort($ore_selezionate, SORT_NUMERIC);
            $consecutivi = true;
            for ($i = 0; $i < count($ore_selezionate) - 1; $i++) {
                if ($ore_selezionate[$i + 1] !== ($ore_selezionate[$i] + 1)) {
                    $consecutivi = false;
                    break;
                }
            }

            if (!$consecutivi) {
                $errore_selezione = "Errore: Hai selezionato orari non consecutivi. Seleziona solo ore di fila.";
            } else {
                $ora_inizio = $ore_selezionate[0];
                $ultima_ora = end($ore_selezionate);
                $durata_calcolata = ($ultima_ora - $ora_inizio) + 1;
                $data_scelta = $giorno_riferimento;
                $ora_scelta = $ora_inizio;
            }
        }
    }
}

// --- RECUPERO SALE ---
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

// --- LOGICA CALENDARIO ---
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

// --- LOGICA UTENTI ---
$utenti_invitabili = [];
if ($data_scelta && $ora_scelta) {
    $sql_u = "SELECT U.id_utente, U.nome, U.cognome, U.ruolo 
              FROM UTENTE U
              WHERE U.id_utente != ? AND U.id_settore = ? 
              AND U.id_utente NOT IN (SELECT I.id_utente FROM INVITO I WHERE I.data = ? AND I.ora = ? AND I.stato = 'accettato')
              AND U.id_utente NOT IN (SELECT P.id_organizzatore FROM PRENOTAZIONE P WHERE P.data = ? AND P.ora = ?)
              ORDER BY U.cognome";
    $stmt_u = $cid->prepare($sql_u);
    $stmt_u->bind_param("iisisi", $id_utente, $id_settore_utente, $data_scelta, $ora_scelta, $data_scelta, $ora_scelta);
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
            <div class="alert alert-danger text-center shadow-sm rounded-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($errore_selezione); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger text-center shadow-sm rounded-4"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4 border-0 rounded-5 overflow-hidden">
            <div class="card-body bg-light p-4">
                <form action="prenota.php" method="GET" class="row g-3 align-items-end">
                    <input type="hidden" name="week" value="<?php echo $lunedi_settimana; ?>">
                    
                    <div class="col-md-8">
                        <label for="sala" class="form-label fw-bold text-muted text-uppercase small ps-1">1. Scegli la Sala</label>
                        <select class="form-select rounded-pill shadow-sm py-2 px-3 fw-bold text-dark" name="sala" id="sala" onchange="this.form.submit()" style="cursor: pointer; border-color: #D2B48C;">
                            <option value="" disabled <?php echo !$id_sala_selezionata ? 'selected' : ''; ?>>-- Seleziona una sala dal menu --</option>
                            <?php foreach ($sale as $s): ?>
                                <option value="<?php echo htmlspecialchars($s['nome_sala']); ?>" 
                                    <?php echo ($id_sala_selezionata == $s['nome_sala']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['nome_sala']); ?> (Capienza: <?php echo $s['capienza_max']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm py-2 fw-bold">
                            <i class="bi bi-calendar-event me-2"></i> Aggiorna Calendario
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($id_sala_selezionata && !$data_scelta): ?>
            
            <form action="prenota.php" method="POST">
                <input type="hidden" name="sala" value="<?php echo htmlspecialchars($id_sala_selezionata); ?>">
                <input type="hidden" name="week" value="<?php echo $lunedi_settimana; ?>">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="prenota.php?sala=<?php echo urlencode($id_sala_selezionata); ?>&week=<?php echo $prev_week; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">&larr; Settimana Prec.</a>
                    <h5 class="mb-0 fw-bold">Settimana dal <?php echo date('d/m', strtotime($lunedi_settimana)); ?> al <?php echo date('d/m', strtotime($domenica_settimana)); ?></h5>
                    <a href="prenota.php?sala=<?php echo urlencode($id_sala_selezionata); ?>&week=<?php echo $next_week; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Settimana Succ. &rarr;</a>
                </div>

                <div class="alert alert-info py-2 small border-0 bg-info bg-opacity-10 rounded-4 mb-3">
                    <i class="bi bi-info-circle-fill text-info ms-2"></i> Seleziona le caselle orarie consecutive che vuoi prenotare e premi "Procedi".
                </div>

                <div class="table-responsive shadow-sm mb-4 rounded-4 overflow-hidden">
                    <table class="table calendar-table mb-0 text-center">
                        <thead>
                            <tr>
                                <th class="align-middle" style="width: 80px;">Ora</th>
                                <?php 
                                $giorni = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
                                foreach($giorni as $index => $giorno) {
                                    $d = date('d/m', strtotime($lunedi_settimana . " +$index days"));
                                    echo "<th style='width: 120px'>$giorno<br><small class='fw-normal'>$d</small></th>";
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($ora = 9; $ora <= 23; $ora++): ?>
                                <tr>
                                    <td class="fw-bold align-middle"><?php echo $ora; ?>:00</td>
                                    <?php for ($i = 0; $i < 7; $i++): 
                                        $data_curr = date('Y-m-d', strtotime($lunedi_settimana . " +$i days"));
                                        $is_occupied = isset($occupied[$data_curr][$ora]);
                                        $is_past = (strtotime($data_curr . " " . $ora . ":00") < time());
                                        $value = $data_curr . "|" . $ora;
                                    ?>
                                        <?php if ($is_occupied): ?>
                                            <td class="cell-occupied align-middle" title="Occupato"><i class="bi bi-x-circle text-danger"></i></td>
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
                    <button type="submit" class="btn btn-success btn-lg shadow rounded-pill px-4 fw-bold">
                        Procedi con la Selezione <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </form>

        <?php endif; ?>

        <?php if ($data_scelta && $ora_scelta && $id_sala_selezionata): ?>
            <div id="form-prenotazione" class="card border-primary shadow rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0 fs-5 fw-bold ms-2">Completa Prenotazione</h4>
                </div>
                <div class="card-body p-4">
                    <p class="lead mb-2">Sala: <strong><?php echo htmlspecialchars($id_sala_selezionata); ?></strong></p>
                    
                    <div class="alert alert-success border-success border-opacity-25 bg-success bg-opacity-10 rounded-4">
                        <i class="bi bi-calendar-check-fill text-success me-2 ms-2"></i>
                        Prenotazione per il giorno <strong><?php echo date('d/m/Y', strtotime($data_scelta)); ?></strong><br>
                        <span class="ms-5">
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
                            <label for="attivita" class="form-label fw-bold ps-1">Descrizione Attività</label>
                            <input type="text" class="form-control rounded-3" id="attivita" name="attivita" placeholder="Es. Prove, Lezione..." required>
                        </div>

                        <hr>
                        
                        <h5 class="fw-bold mb-3">Invita Utenti (Opzionale)</h5>
                        <div class="alert alert-info py-1 small rounded-3">
                            <i class="bi bi-info-circle ms-1"></i> Vengono mostrati solo gli utenti attualmente liberi in questo orario.
                        </div>
                        <div class="row g-2 mb-4" style="max-height: 200px; overflow-y: auto;">
                            <?php if (!empty($utenti_invitabili)): ?>
                                <?php foreach ($utenti_invitabili as $u): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="form-check p-2 border rounded-3 bg-white">
                                            <input class="form-check-input ms-1" type="checkbox" name="invitati[]" value="<?php echo $u['id_utente']; ?>" id="user_<?php echo $u['id_utente']; ?>">
                                            <label class="form-check-label ms-2 w-75" for="user_<?php echo $u['id_utente']; ?>">
                                                <?php echo htmlspecialchars($u['nome'] . " " . $u['cognome']); ?>
                                                <small class="text-muted d-block"><?php echo ucfirst($u['ruolo']); ?></small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted ms-1">Nessun utente disponibile nel tuo settore in questo orario.</p>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="prenota.php?sala=<?php echo urlencode($id_sala_selezionata); ?>&week=<?php echo $lunedi_settimana; ?>" class="btn btn-secondary rounded-pill px-4">Annulla</a>
                            <button type="submit" class="btn btn-success px-4 fw-bold rounded-pill">Conferma Prenotazione</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <?php include 'common/footer.html'; ?>
</body>
</html>