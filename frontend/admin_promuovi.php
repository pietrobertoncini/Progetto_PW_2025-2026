<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Verifica dell'identità dell'utente per limitare l'accesso esclusivamente agli amministratori 
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Recupera l'ID utente dall'URL
$id_utente_target = intval($_GET['id'] ?? 0);
if ($id_utente_target <= 0) {
    header("Location: admin_utenti.php?error=Utente non valido.");
    exit;
}

// Recupera i dati dell'utente per mostrarli
$dati_utente_target = datiUtenteCompleti($cid, $id_utente_target);

if (!$dati_utente_target || $dati_utente_target['is_admin'] || $dati_utente_target['is_responsabile']) {
    header("Location: admin_utenti.php?error=Questo utente non può essere promosso.");
    exit;
}

// Recupera i settori per il menù a tendina
$elenco_settori = getAllSettoriAdmin($cid);
?>

<!DOCTYPE html>
<html lang="it" class="no-js">
<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-primary text-white py-3 fw-bold rounded-top-4">
                        <i class="bi bi-arrow-up-circle me-2"></i> Promuovi a Responsabile
                    </div>
                    <div class="card-body p-4">
                        <p class="mb-2">
                            Stai promuovendo:
                            <strong class="text-primary">
                                <?php echo htmlspecialchars($dati_utente_target['nome'] . ' ' . $dati_utente_target['cognome']); ?>
                            </strong>
                        </p>

                        <form action="<?php echo BASE_URL; ?>backend/admin_utenti_exe.php" method="POST">
                            <input type="hidden" name="action" value="promote">
                            <input type="hidden" name="id_utente" value="<?php echo $id_utente_target; ?>">

                            <div class="mb-4">
                                <label for="id_settore_dest" class="form-label fw-bold small text-muted">Seleziona il Settore</label>
                                <select class="form-select rounded-3 p-3" id="id_settore_dest" name="id_settore_dest" required style="cursor: pointer;">
                                    <option value="" selected disabled>-- Scegli un settore --</option>
                                    <?php foreach ($elenco_settori as $settore): ?>
                                        <option value="<?php echo $settore['id_settore']; ?>">
                                            <?php echo htmlspecialchars($settore['nome']); ?>
                                            (<?php echo ucfirst($settore['tipo']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small text-danger mt-2">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Se il settore ha già un responsabile, verrà sostituito.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary rounded-pill fw-bold py-2">Conferma</button>
                                <a href="<?php echo BASE_URL; ?>frontend/admin_utenti.php" class="btn btn-outline-secondary rounded-pill py-2">Annulla</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>

</html>