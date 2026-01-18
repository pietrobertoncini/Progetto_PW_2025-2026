<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Controllo Accesso
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_responsabile'])) {
    header('Location: ' . BASE_URL . 'frontend/index.php');
    exit;
}

// Recupero Parametri URL (La chiave primaria della prenotazione)
$nome_sala = isset($_GET['sala']) ? urldecode($_GET['sala']) : null;
$data_old = isset($_GET['data']) ? $_GET['data'] : null;
$ora_old = isset($_GET['ora']) ? (int)$_GET['ora'] : null;

if (!$nome_sala || !$data_old || !$ora_old) {
    die("Errore: Parametri prenotazione mancanti.");
}

// Recupero Dati Prenotazione dal DB
$dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
$id_settore = $dati_utente['id_settore'];

$prenotazione = getPrenotazioneSingola($cid, $id_settore, $nome_sala, $data_old, $ora_old);

if (!$prenotazione) {
    die("Prenotazione non trovata o non hai i permessi per gestirla.");
}

$lista_invitati = getInvitatiPrenotazione($cid, $id_settore, $nome_sala, $data_old, $ora_old)
?>

<!DOCTYPE html>
<html lang="it">
<?php require ROOT_PATH . "/common/header.php" ?>

<body>
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-secondary bg-opacity-25 border-0 py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold" style="color: #7A5E4E;">Gestisci Prenotazione</h4>
                        <a href="<?php echo BASE_URL; ?>frontend/gestione_prenotazioni.php" class="btn btn-sm btn-outline-secondary rounded-pill">Annulla</a>
                    </div>

                    <div class="card-body p-4">

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <form action="<?php echo BASE_URL; ?>backend/modifica_prenotazione_exe.php" method="POST">

                            <input type="hidden" name="old_nome_sala" value="<?php echo htmlspecialchars($nome_sala); ?>">
                            <input type="hidden" name="old_data" value="<?php echo $data_old; ?>">
                            <input type="hidden" name="old_ora" value="<?php echo $ora_old; ?>">
                            <input type="hidden" name="id_settore" value="<?php echo $id_settore; ?>">

                            <div class="mb-3">
                                <label class="form-label fw-bold">Sala</label>
                                <input type="text" class="form-control bg-light" name="nome_sala" value="<?php echo htmlspecialchars($prenotazione['nome_sala']); ?>" readonly>
                                <div class="form-text">Per cambiare sala, elimina questa prenotazione e fanne una nuova.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="data" class="form-label fw-bold">Data</label>
                                    <input type="date" class="form-control" id="data" name="new_data"
                                        value="<?php echo $prenotazione['data']; ?>" required min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="ora" class="form-label fw-bold">Ora Inizio</label>
                                    <select class="form-select" id="ora" name="new_ora" required>
                                        <?php for ($i = 9; $i < 23; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($i == $prenotazione['ora']) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>:00
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="durata" class="form-label fw-bold">Durata (ore)</label>
                                <input type="number" class="form-control" id="durata" name="new_durata"
                                    min="1" value="<?php echo $prenotazione['durata']; ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="attivita" class="form-label fw-bold">Attività</label>
                                <input type="text" class="form-control" id="attivita" name="new_attivita"
                                    value="<?php echo htmlspecialchars($prenotazione['attivita']); ?>" required>
                            </div>

                            <div class="mb-4 text-center">
                                <button type="button" class="btn btn-outline-secondary rounded-pill w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#modalInvitati">
                                    <i class="bi bi-people-fill me-2"></i> Visualizza Invitati (<?php echo count($lista_invitati); ?>)
                                </button>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill">Salva Modifiche</button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="bg-danger bg-opacity-10 p-3 rounded-4 border border-danger border-opacity-25">
                            <h5 class="text-danger fw-bold fs-6">Zona Pericolo</h5>
                            <p class="small text-muted mb-3">L'eliminazione è irreversibile e cancellerà anche tutti gli inviti associati.</p>

                            <form action="<?php echo BASE_URL; ?>backend/elimina_prenotazione.php" method="POST" onsubmit="return confirm('Sei DAVVERO sicuro di voler eliminare questa prenotazione?');">
                                <input type="hidden" name="id_settore" value="<?php echo $id_settore; ?>">
                                <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($nome_sala); ?>">
                                <input type="hidden" name="data" value="<?php echo $data_old; ?>">
                                <input type="hidden" name="ora" value="<?php echo $ora_old; ?>">

                                <button type="submit" class="btn btn-danger w-100 rounded-pill">
                                    <i class="bi bi-trash3-fill"></i> Elimina Prenotazione
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalInvitati" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow rounded-4" style="background-color: transparent;">
                <div class="modal-header bg-secondary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-people me-2"></i> Lista Partecipanti</h5>
                    <button type="button" class="btn-close btn-close-white me-2" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body p-0">
                    <?php if (count($lista_invitati) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($lista_invitati as $inv): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                    <div>
                                        <div class="fw-bold text-dark">
                                            <?php echo htmlspecialchars($inv['nome'] . ' ' . $inv['cognome']); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars($inv['email']); ?>
                                            <span class="badge bg-light text-secondary border ms-1"><?php echo ucfirst($inv['ruolo']); ?></span>
                                        </div>
                                        <?php if (!empty($inv['motivazione'])): ?>
                                            <div class="small text-danger fst-italic mt-1">
                                                Note: "<?php echo htmlspecialchars($inv['motivazione']); ?>"
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($inv['stato'] == 'accettato'): ?>
                                        <span class="badge bg-success rounded-pill"><i class="bi bi-check-lg"></i> Sì</span>
                                    <?php elseif ($inv['stato'] == 'rifiutato'): ?>
                                        <span class="badge bg-danger rounded-pill"><i class="bi bi-x-lg"></i> No</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-hourglass"></i> ?</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-person-slash display-4 text-muted opacity-50"></i>
                            <p class="mt-3 text-muted fw-bold">Nessun invitato per questa prenotazione.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>

</html>