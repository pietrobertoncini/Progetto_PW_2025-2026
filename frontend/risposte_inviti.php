<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

//  Controllo Sicurezza
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_responsabile'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

$id_responsabile = $_SESSION['id_utente'];

//  RECUPERO RISPOSTE
$risposte = getRisposteInvitiByResponsabile($cid, $id_responsabile);
?>

<!DOCTYPE html>
<html lang="it">
<?php require ROOT_PATH . "/common/header.php" ?>

<body>
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="container mt-5 mb-5 pt-4">
        <div class="mb-4">
            <h2>Esito Inviti Inviati</h2>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-info bg-opacity-10 border-0 py-3">
                <h5 class="mb-0 text-dark">
                    Stato degli inviti per i tuoi eventi
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (count($risposte) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Evento & Data</th>
                                    <th>Invitato</th>
                                    <th>Stato Risposta</th>
                                    <th>Note / Motivazione</th>
                                    <th class="text-end pe-4">Data Risposta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($risposte as $r): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <strong><?php echo htmlspecialchars($r['attivita']); ?></strong><br>
                                            <small class="text-muted">
                                                <?php echo date("d/m/Y", strtotime($r['data'])); ?> - Ore <?php echo $r['ora']; ?>:00
                                                (<?php echo htmlspecialchars($r['nome_sala']); ?>)
                                            </small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($r['nome'] . " " . $r['cognome']); ?><br>
                                            <span class="badge bg-light text-secondary border"><?php echo ucfirst($r['ruolo']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($r['stato'] == 'accettato'): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> Accettato</span>
                                            <?php elseif ($r['stato'] == 'rifiutato'): ?>
                                                <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Rifiutato</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> In attesa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if (!empty($r['motivazione'])) {
                                                echo '<span class="text-danger small fst-italic">"' . htmlspecialchars($r['motivazione']) . '"</span>';
                                            } else {
                                                echo '<span class="text-muted small">-</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end pe-4 text-muted small">
                                            <?php 
                                            if ($r['data_risposta']) {
                                                echo date("d/m/Y H:i", strtotime($r['data_risposta']));
                                            } else {
                                                echo "-";
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <p class="mb-0">Non hai ancora inviato inviti o nessuno ha ancora risposto.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>
</html>