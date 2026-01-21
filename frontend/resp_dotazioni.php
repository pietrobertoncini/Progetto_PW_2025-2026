<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Controllo di sicurezza per limitare l'accesso esclusivamente agli utenti con ruolo di responsabile
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_responsabile'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// Recupero dati utente e settore
$dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
$id_settore = $dati_utente['id_settore'];
$nome_settore = $dati_utente['nome_settore'];

// Recupero le sale di questo settore
$sale = getSaleBySettore($cid, $id_settore);
?>

<!DOCTYPE html>
<html lang="it" class="no-js">
<?php require ROOT_PATH . "/common/header.php"; ?>

<body class="d-flex flex-column">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2>Allestimento Sale</h2>
                <h5 class="text-muted">Settore di riferimento: <span class="text-brand fw-bold"><?php echo htmlspecialchars($nome_settore); ?></span></h5>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary bg-opacity-25 border-0 py-3">
                <h5 class="mb-0 text-dark">Elenco Sale da Gestire</h5>
            </div>
            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light small text-muted text-uppercase" style="position: sticky; top: 0; z-index: 1;">
                        <tr>
                            <th class="ps-4">Nome Sala</th>
                            <th>Dotazioni Attuali</th>
                            <th class="text-end pe-4">Gestione</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($sale) > 0): ?>
                            <?php foreach ($sale as $s): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        <?php echo htmlspecialchars($s['nome_sala']); ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php
                                        $lista_dot = getDotazioniSala($cid, $id_settore, $s['nome_sala']);
                                        if ($lista_dot) {
                                            echo htmlspecialchars($lista_dot);
                                        } else {
                                            echo '<span class="fst-italic text-secondary">Nessuna dotazione assegnata</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="<?php echo BASE_URL; ?>frontend/resp_edit_dotazioni.php?sala=<?php echo urlencode($s['nome_sala']); ?>"
                                            title="Modifica Dotazioni"
                                            class="btn btn-primary btn-sm rounded-pill fw-bold shadow-sm px-2 px-md-3">
                                            <i class="bi bi-box-seam"></i>
                                            <span class="d-none d-lg-inline ms-1">Modifica Dotazioni</span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">Non ci sono sale in questo settore.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php require ROOT_PATH . "/common/footer.html"; ?>
</body>

</html>