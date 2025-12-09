<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Controllo Accesso
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_responsabile'])) {
    header('Location: index.php');
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
?>

<!DOCTYPE html>
<html lang="it">
<?php require ROOT_PATH . "/common/header.html" ?>

<body>
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-secondary bg-opacity-10 border-0 py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold text-dark">Gestisci Prenotazione</h4>
                        <a href="<?php echo BASE_URL; ?>frontend/gestione_prenotazioni.php" class="btn btn-sm btn-outline-secondary">Annulla</a>
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
                                        <?php for($i=9; $i<=23; $i++): ?>
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
                                       min="1" max="4" value="<?php echo $prenotazione['durata']; ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="attivita" class="form-label fw-bold">Attività</label>
                                <input type="text" class="form-control" id="attivita" name="new_attivita" 
                                       value="<?php echo htmlspecialchars($prenotazione['attivita']); ?>" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Salva Modifiche</button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="bg-danger bg-opacity-10 p-3 rounded border border-danger border-opacity-25">
                            <h5 class="text-danger fw-bold fs-6">Zona Pericolo</h5>
                            <p class="small text-muted mb-3">L'eliminazione è irreversibile e cancellerà anche tutti gli inviti associati.</p>
                            
                            <form action="<?php echo BASE_URL; ?>backend/elimina_prenotazione.php" method="POST" onsubmit="return confirm('Sei DAVVERO sicuro di voler eliminare questa prenotazione?');">
                                <input type="hidden" name="id_settore" value="<?php echo $id_settore; ?>">
                                <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($nome_sala); ?>">
                                <input type="hidden" name="data" value="<?php echo $data_old; ?>">
                                <input type="hidden" name="ora" value="<?php echo $ora_old; ?>">
                                
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-trash3-fill"></i> Elimina Prenotazione
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>
</html>