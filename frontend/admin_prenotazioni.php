<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// SICUREZZA: SOLO ADMIN 
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) {
    header("Location: index.php");
    exit;
}

// RECUPERO DATI
$elenco_prenotazioni = getAllPrenotazioniAdmin($cid);
?>

<!DOCTYPE html>
<html lang="it" class="h-100">
<?php require ROOT_PATH . "/common/header.html" ?>

<body class="d-flex flex-column h-100">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Prenotazioni Globali</h2>
                <p class="text-muted small mb-0">Panoramica completa di tutte le attività in tutte le sale.</p>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success shadow-sm rounded-4 mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger shadow-sm rounded-4 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-3">
                <span class="fw-bold text-muted me-2"><i class="bi bi-list-ul"></i> Totale Prenotazioni:</span>
                <span class="badge bg-dark rounded-pill px-3"><?php echo count($elenco_prenotazioni); ?></span>
            </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted text-uppercase">
                        <tr>
                            <th class="ps-4">Data & Ora</th>
                            <th>Sala & Settore</th>
                            <th>Attività</th>
                            <th>Organizzatore</th>
                            <th class="text-end pe-4">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if (count($elenco_prenotazioni) > 0): ?>
                            <?php foreach ($elenco_prenotazioni as $p):
                                // Calcoli per visualizzazione
                                $data_prenotazione = $p['data'];
                                $is_passata = (strtotime($data_prenotazione) < strtotime(date('Y-m-d')));
                                $ora_fine = $p['ora'] + $p['durata'];
                            ?>
                                <tr class="<?php echo $is_passata ? 'bg-light text-muted' : ''; ?>">

                                    <td class="ps-4">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold <?php echo $is_passata ? 'text-secondary' : 'text-dark'; ?>">
                                                <?php echo date("d/m/Y", strtotime($data_prenotazione)); ?>
                                            </span>
                                            <small class="text-muted">
                                                <?php echo $p['ora']; ?>:00 - <?php echo $ora_fine; ?>:00
                                                <span class="badge bg-light text-dark border ms-1"><?php echo $p['durata']; ?>h</span>
                                            </small>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="d-block fw-bold" style="color: #7A5E4E;">
                                            <?php echo htmlspecialchars($p['nome_sala']); ?>
                                        </span>
                                        <small class="text-muted text-uppercase" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($p['nome_settore']); ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($p['attivita']); ?>
                                        <?php if ($is_passata): ?>
                                            <span class="badge bg-secondary ms-2" style="font-size: 0.65rem;">ARCHIVIATA</span>
                                        <?php else: ?>
                                            <span class="badge bg-success ms-2" style="font-size: 0.65rem;">ATTIVA</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center text-secondary fw-bold me-2" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                <?php echo strtoupper(substr($p['nome_org'], 0, 1)); ?>
                                            </div>
                                            <span class="small">
                                                <?php echo htmlspecialchars($p['nome_org'] . ' ' . $p['cognome_org']); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <td class="text-end pe-4">
                                        <form action="backend/admin_prenotazioni_exe.php" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questa prenotazione? Questa azione cancellerà anche tutti gli inviti associati.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_settore" value="<?php echo $p['id_settore']; ?>">
                                            <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($p['nome_sala']); ?>">
                                            <input type="hidden" name="data" value="<?php echo $p['data']; ?>">
                                            <input type="hidden" name="ora" value="<?php echo $p['ora']; ?>">

                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" title="Elimina Prenotazione">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted lead">
                                    <i class="bi bi-calendar-x display-4 d-block mb-3 opacity-25"></i>
                                    Nessuna prenotazione trovata nel sistema.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>

</html>