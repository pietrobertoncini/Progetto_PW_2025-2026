<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';


// SICUREZZA
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_responsabile'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// RECUPERO DATI
$id_utente_loggato = $_SESSION['id_utente'];
$dati_utente = datiUtenteCompleti($cid, $id_utente_loggato);
$id_settore = $dati_utente['id_settore'];
$nome_settore = $dati_utente['nome_settore'];

// RECUPERO PRENOTAZIONI
$prenotazioni = getPrenotazioniByOrganizzatore($cid, $id_settore, $id_utente_loggato);
?>

<!DOCTYPE html>
<html lang="it" class="no-js">
<?php require ROOT_PATH . "/common/header.php" ?>

<style>
    .table-clean tbody tr td {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
    }
</style>

<body>

    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h2 class="mb-2">Le Mie Prenotazioni</h2>
                <h5 class="text-muted">
                    Settore di riferimento: <span class="text-brand fw-bold"><?php echo htmlspecialchars($nome_settore); ?></span>
                </h5>
            </div>

            <div class="col-md-4 text-end">
                <a href="<?php echo BASE_URL; ?>frontend/prenota.php" class="btn btn-primary shadow-sm rounded-pill">
                    <i class="bi bi-plus-lg"></i> Nuova Prenotazione
                </a>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary bg-opacity-25 border-0 py-3">
                <h5 class="mb-0 text-dark">Elenco Prenotazioni Create da Te</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0 table-clean">
                        <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th class="ps-4">Nome Sala</th>
                                <th>Data</th>
                                <th>Orario</th>
                                <th>Attività</th>
                                <th class="text-end pe-4">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($prenotazioni) > 0): ?>
                                <?php foreach ($prenotazioni as $p):
                                    // Calcolo Stato
                                    $ts_inizio = strtotime($p['data'] . " " . $p['ora'] . ":00");
                                    $ts_fine = $ts_inizio + ($p['durata'] * 3600);
                                    $now = time();

                                    $is_concluso = ($now >= $ts_fine);
                                    $is_in_corso = ($now >= $ts_inizio && $now < $ts_fine);

                                    // Stile riga
                                    $class_riga = $is_concluso ? 'bg-light text-muted' : ($is_in_corso ? 'table-success bg-opacity-10' : '');
                                    $ora_fine = $p['ora'] + $p['durata'];
                                ?>
                                    <tr class="<?php echo $class_riga; ?>">
                                        <td class="ps-4 py-3 fw-bold <?php echo $is_concluso ? 'text-muted' : 'text-brand'; ?>">
                                            <?php echo htmlspecialchars($p['nome_sala']); ?>
                                            <?php if ($is_concluso): ?>
                                                <span class="badge bg-secondary ms-2" style="font-size: 0.6em;">CONCLUSO</span>
                                            <?php elseif ($is_in_corso): ?>
                                                <span class="badge bg-success ms-2" style="font-size: 0.6em;">IN CORSO</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php echo date("d/m/Y", strtotime($p['data'])); ?>
                                        </td>

                                        <td>
                                            <?php echo $p['ora'] . ":00 - " . $ora_fine . ":00"; ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($p['attivita']); ?>
                                        </td>

                                        <td class="text-end pe-4">
                                            <?php if ($is_concluso): ?>
                                                <button class="btn btn-sm btn-outline-secondary rounded-pill disabled" disabled>
                                                    <i class="bi bi-archive-fill"></i> Archiviato
                                                </button>
                                            <?php else: ?>
                                                <a href="<?php echo BASE_URL; ?>frontend/modifica_prenotazione.php?sala=<?php echo urlencode($p['nome_sala']); ?>&data=<?php echo $p['data']; ?>&ora=<?php echo $p['ora']; ?>"
                                                    class="btn btn-sm btn-secondary rounded-pill shadow-sm">
                                                    <i class="bi bi-gear-fill"></i> Gestisci
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        Non hai ancora creato nessuna prenotazione attiva.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <?php require ROOT_PATH . "/common/footer.html"; ?>
</body>

</html>