<?php
// impegni.php
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
$impegni_futuri = [];
if (function_exists('getImpegniFuturi')) {
    $impegni_futuri = getImpegniFuturi($cid, $_SESSION['id_utente']);
}
?>

<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body class="d-flex flex-column h-100">
    <?php include 'common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>I Tuoi Impegni</h2>
        </div>

        <div class="card shadow-sm border-0 overflow-hidden rounded-3">
            <div class="card-header bg-success bg-opacity-10 border-0 py-3">
                <h5 class="mb-0 text-dark">
                    Calendario Attività <span class="badge bg-success rounded-pill ms-2"><?php echo count($impegni_futuri); ?></span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($impegni_futuri) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Data</th>
                                    <th>Orario</th>
                                    <th>Sala</th>
                                    <th>Attività</th>
                                    <th class="text-end pe-4">Gestione</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($impegni_futuri as $impegno): ?>
                                    <tr>
                                        <td class="ps-4 py-3"><strong><?php echo date("d/m/Y", strtotime($impegno['data'])); ?></strong></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo $impegno['ora']; ?>:00 - <?php echo $impegno['ora'] + $impegno['durata']; ?>:00
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($impegno['nome_sala']); ?></td>
                                        <td><?php echo htmlspecialchars($impegno['attivita']); ?></td>
                                        <td class="text-end pe-4">
                                            <form action="backend/invite_reply.php" method="POST">
                                                <input type="hidden" name="id_settore" value="<?php echo $impegno['id_settore']; ?>">
                                                <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($impegno['nome_sala']); ?>">
                                                <input type="hidden" name="data" value="<?php echo $impegno['data']; ?>">
                                                <input type="hidden" name="ora" value="<?php echo $impegno['ora']; ?>">
                                                <input type="hidden" name="risposta" value="rifiutato">
                                                <input type="hidden" name="motivazione" value="Disdetta successiva dell'utente">

                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Sei sicuro di voler disdire la partecipazione?');">
                                                    <i class="bi bi-x-circle"></i> Disdici
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-check display-4 text-muted opacity-25 mb-3 d-block"></i>
                        <p class="lead text-muted">Non hai impegni confermati in programma.</p>
                        <p class="small text-secondary">Attendi che un responsabile ti inviti a una prova.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require "common/footer.html"; ?>
</body>

</html>