<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/common/setup.php';
require_once __DIR__ . '/common/function.php';

// SICUREZZA: SOLO ADMIN
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}

// RECUPERO DATI
$sale = getAllSaleGlobal($cid);
?>

<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>

    <?php include 'common/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        
        <div class="row align-items-center mb-4 mt-4">
            <div class="col-md-8">
                <h2 class="mb-2">Gestione Sale (Globale)</h2>
                <p class="text-muted">Amministrazione completa di tutte le sale.</p>
            </div>
            
            <div class="col-md-4 text-end">
                <a href="nuova_sala.php" class="btn btn-primary shadow-sm">
                    <i class="bi bi-plus-lg"></i> Nuova Sala
                </a>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
            <div class="card-header bg-primary bg-opacity-10 border-0 py-3">
                <h5 class="mb-0 text-dark">Elenco Completo Sale</h5>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 20%;">Settore</th>
                                <th style="width: 25%;">Nome Sala</th>
                                <th style="width: 10%;">Capienza</th>
                                <th style="width: 30%;">Dotazioni</th>
                                <th class="text-end pe-4" style="width: 15%;">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($sale) > 0): ?>
                                <?php foreach ($sale as $s): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-secondary bg-opacity-10 text-dark border">
                                                <?php echo htmlspecialchars($s['nome_settore']); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold text-brand">
                                            <?php echo htmlspecialchars($s['nome_sala']); ?>
                                        </td>
                                        <td> 
                                            <span class="badge bg-secondary rounded-pill fs-6">
                                                <?php echo $s['capienza_max']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                $dotazioni = getDotazioniSala($cid, $s['id_settore'], $s['nome_sala']);
                                                if ($dotazioni) {
                                                    echo '<small class="text-muted">' . htmlspecialchars($dotazioni) . '</small>';
                                                } else {
                                                    echo '<span class="text-muted small fst-italic">Nessuna dotazione</span>';
                                                }
                                            ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="modifica_sala.php?nome=<?php echo urlencode($s['nome_sala']); ?>&id_settore=<?php echo $s['id_settore']; ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-gear-fill"></i> Gestisci
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        Nessuna sala presente nel sistema.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>

    <?php require "common/footer.html"; ?>
</body>
</html>