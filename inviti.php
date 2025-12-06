<?php
// inviti.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Controllo sicurezza
if (!isset($_SESSION['id_utente'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/common/setup.php';
require_once __DIR__ . '/common/function.php';

// Recupero dati
$inviti_pendenti = [];
if (function_exists('getInvitiPendenti')) {
    $inviti_pendenti = getInvitiPendenti($cid, $_SESSION['id_utente']);
}
?>

<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>
    <?php include 'common/navbar.php'; ?>

    <div class="container mt-5 mb-5 pt-4">
        
        <div class="mb-4">
            <h2>Gestione Inviti</h2>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-warning bg-opacity-10 border-0 py-3">
                <h5 class="mb-0 text-dark">
                    Inviti in Attesa <span class="badge bg-danger rounded-pill ms-2"><?php echo count($inviti_pendenti); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($inviti_pendenti) > 0): ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Attività</th>
                                    <th>Luogo e Data</th>
                                    <th>Organizzatore</th>
                                    <th style="min-width: 200px;" class="text-end">La tua Risposta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inviti_pendenti as $invito): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($invito['attivita']); ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($invito['nome_sala']); ?><br>
                                            <small class="text-muted">
                                                <?php echo date("d/m/Y", strtotime($invito['data'])); ?> ore <?php echo $invito['ora']; ?>:00
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($invito['nome_org'] . " " . $invito['cognome_org']); ?></td>
                                        <td class="text-end">
                                            <div class="d-flex flex-column gap-2 align-items-end">
                                                <form action="backend/invite_reply.php" method="POST" class="w-100" style="max-width: 200px;">
                                                    <input type="hidden" name="id_settore" value="<?php echo $invito['id_settore']; ?>">
                                                    <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($invito['nome_sala']); ?>">
                                                    <input type="hidden" name="data" value="<?php echo $invito['data']; ?>">
                                                    <input type="hidden" name="ora" value="<?php echo $invito['ora']; ?>">
                                                    <input type="hidden" name="risposta" value="accettato">
                                                    <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">Accetta</button>
                                                </form>

                                                <form action="backend/invite_reply.php" method="POST" class="d-flex gap-1 w-100" style="max-width: 300px;">
                                                    <input type="hidden" name="id_settore" value="<?php echo $invito['id_settore']; ?>">
                                                    <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($invito['nome_sala']); ?>">
                                                    <input type="hidden" name="data" value="<?php echo $invito['data']; ?>">
                                                    <input type="hidden" name="ora" value="<?php echo $invito['ora']; ?>">
                                                    <input type="hidden" name="risposta" value="rifiutato">

                                                    <input type="text" name="motivazione" class="form-control form-control-sm" placeholder="Motivo..." required>
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Rifiuta</button>
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
                        <p class="mb-0">Non hai nuovi inviti a cui rispondere al momento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'common/footer.html'; ?>
</body>
</html>