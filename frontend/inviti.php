<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica della sessione attiva
if (!isset($_SESSION['id_utente'])) {
    header('Location: ' . BASE_URL . 'frontend/login.php');
    exit;
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Recupero dati
$inviti_pendenti = getInvitiPendenti($cid, $_SESSION['id_utente']);
?>

<!DOCTYPE html>
<html lang="it" class="no-js">
<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestione Inviti</h2>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-warning bg-opacity-50 border-0 py-3 d-flex align-items-center">
                <h5 class="mb-0 text-dark fw-bold">
                    Inviti in Attesa <span class="badge bg-danger rounded-pill ms-2"><?php echo count($inviti_pendenti); ?></span>
                </h5>
            </div>

            <div class="card-body p-0">
                <?php if (count($inviti_pendenti) > 0): ?>
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light text-muted text-uppercase small" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="ps-4 py-3">Attività</th>
                                    <th>Luogo e Data</th>
                                    <th>Organizzatore</th>
                                    <th class="text-end pe-4" style="min-width: 250px;">La tua Risposta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inviti_pendenti as $invito): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            <?php echo htmlspecialchars($invito['attivita']); ?>
                                        </td>
                                        <td>
                                            <span class="d-block fw-bold text-secondary">
                                                <?php echo htmlspecialchars($invito['nome_sala']); ?>
                                            </span>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                <?php echo date("d/m/Y", strtotime($invito['data'])); ?>
                                                <i class="bi bi-clock ms-2 me-1"></i>
                                                <?php echo $invito['ora']; ?>:00
                                            </small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex justify-content-center align-items-center me-2 fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                    <?php echo strtoupper(substr($invito['nome_org'], 0, 1) . substr($invito['cognome_org'], 0, 1)); ?>
                                                </div>
                                                <span><?php echo htmlspecialchars($invito['nome_org'] . " " . $invito['cognome_org']); ?></span>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex flex-column gap-2 align-items-end">

                                                <form action="<?php echo BASE_URL; ?>backend/invite_reply.php" method="POST" class="w-100">
                                                    <input type="hidden" name="id_settore" value="<?php echo $invito['id_settore']; ?>">
                                                    <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($invito['nome_sala']); ?>">
                                                    <input type="hidden" name="data" value="<?php echo $invito['data']; ?>">
                                                    <input type="hidden" name="ora" value="<?php echo $invito['ora']; ?>">
                                                    <input type="hidden" name="risposta" value="accettato">
                                                    <button type="submit" class="btn btn-success btn-sm w-100 fw-bold rounded-pill shadow-sm">Accetta</button>
                                                </form>

                                                <form action="<?php echo BASE_URL; ?>backend/invite_reply.php" method="POST" class="w-100">
                                                    <input type="hidden" name="id_settore" value="<?php echo $invito['id_settore']; ?>">
                                                    <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($invito['nome_sala']); ?>">
                                                    <input type="hidden" name="data" value="<?php echo $invito['data']; ?>">
                                                    <input type="hidden" name="ora" value="<?php echo $invito['ora']; ?>">
                                                    <input type="hidden" name="risposta" value="rifiutato">

                                                    <div class="d-flex gap-2">
                                                        <input type="text" name="motivazione" class="form-control form-control-sm rounded-pill" placeholder="Motivo rifiuto..." required>
                                                        <button class="btn btn-outline-danger btn-sm rounded-pill px-3" type="submit">Rifiuta</button>
                                                    </div>
                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-envelope-open display-4 mb-3 d-block opacity-25"></i>
                        <p class="mb-0 fs-5">Non hai nuovi inviti a cui rispondere al momento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>

</html>