<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// SICUREZZA
if (!isset($_SESSION['is_responsabile'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$nome_sala = isset($_GET['sala']) ? urldecode($_GET['sala']) : null;
if (!$nome_sala) {
    header("Location: " . BASE_URL . "frontend/responsabile_dotazioni.php");
    exit;
}

$dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
$id_settore = $dati_utente['id_settore'];

// Recuperiamo TUTTE le dotazioni possibili
$tutte_dotazioni = getAllDotazioni($cid);

// Recuperiamo gli ID delle dotazioni che la sala HA GIÀ
$dotazioni_attuali_ids = getDotazioniIdsBySala($cid, $id_settore, $nome_sala);
?>

<!DOCTYPE html>
<html lang="it" class="no-js">
<?php require ROOT_PATH . "/common/header.php"; ?>

<body class="d-flex flex-column">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-primary text-white py-2 fw-bold rounded-top-4">
                        <i class="bi bi-box-seam me-2"></i> Gestisci Dotazioni
                    </div>

                    <div class="card-body p-3">
                        <h5 class="card-title mb-1 fw-bold"><?php echo htmlspecialchars($nome_sala); ?></h5>
                        <p class="text-muted small mb-3">Seleziona l'equipaggiamento disponibile.</p>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger py-2 small rounded-4"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <form action="<?php echo BASE_URL; ?>backend/resp_dotazioni_exe.php" method="POST">
                            <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($nome_sala); ?>">

                            <div class="row g-2 mb-4 p-2 border rounded-4 bg-light bg-opacity-50" style="max-height: 280px; overflow-y: auto;">
                                <?php if (count($tutte_dotazioni) > 0): ?>
                                    <?php foreach ($tutte_dotazioni as $d):
                                        $checked = in_array($d['id_dotazione'], $dotazioni_attuali_ids) ? 'checked' : '';
                                    ?>
                                        <div class="col-12">
                                            <div class="form-check border rounded-3 p-2 bg-white d-flex align-items-center shadow-sm <?php echo $checked ? 'border-primary bg-primary bg-opacity-10' : ''; ?>">
                                                <input class="form-check-input ms-1 me-3 my-0" type="checkbox" name="dotazioni[]"
                                                    value="<?php echo $d['id_dotazione']; ?>"
                                                    id="dot_<?php echo $d['id_dotazione']; ?>"
                                                    style="transform: scale(1.1);"
                                                    <?php echo $checked; ?>>

                                                <label class="form-check-label w-100 fw-bold small mb-0" for="dot_<?php echo $d['id_dotazione']; ?>" style="cursor: pointer;">
                                                    <?php echo htmlspecialchars($d['tipo']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12 text-center py-4 text-muted small">
                                        Nessuna dotazione nel catalogo globale.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-sm btn-success rounded-pill fw-bold shadow-sm py-2">
                                    <i class="bi bi-check-lg me-1"></i> Salva Configurazione
                                </button>
                                <a href="<?php echo BASE_URL; ?>frontend/resp_dotazioni.php" class="btn btn-sm btn-outline-secondary rounded-pill py-2">Annulla</a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php require ROOT_PATH . "/common/footer.html"; ?>
</body>

</html>