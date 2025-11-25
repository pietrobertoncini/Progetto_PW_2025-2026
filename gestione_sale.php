<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/common/setup.php';
require_once __DIR__ . '/common/function.php';

// --- 1. SICUREZZA ---
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_responsabile'])) {
    header('Location: dashboard.php');
    exit;
}

// --- 2. RECUPERO DATI ---
$dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
$id_settore = $dati_utente['id_settore'];
$nome_settore = $dati_utente['nome_settore'];

// --- 3. RECUPERO SALE ---
$sale = [];
$sql_sale = "SELECT * FROM SALA WHERE id_settore = ? ORDER BY nome_sala";
$stmt = $cid->prepare($sql_sale);
$stmt->bind_param("i", $id_settore);
$stmt->execute();
$result_sale = $stmt->get_result();
$sale = $result_sale->fetch_all(MYSQLI_ASSOC);

function getDotazioniSala($cid, $id_settore, $nome_sala) {
    $sql = "SELECT D.tipo 
            FROM SALA_DOTAZIONE SD
            JOIN DOTAZIONE_DI_SUPPORTO D ON SD.id_dotazione = D.id_dotazione
            WHERE SD.id_settore = ? AND SD.nome_sala = ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("is", $id_settore, $nome_sala);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $lista = [];
    while($row = $res->fetch_assoc()) {
        $lista[] = $row['tipo'];
    }
    return implode(", ", $lista);
}
?>

<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>

    <?php include 'common/navbar.php'; ?>

    <div class="container mt-5 pt-5 mb-5">
        
        <div class="row align-items-center mb-4 mt-4">
            <div class="col-md-8">
                <h2 class="mb-1">Gestione Sale</h2>
                <h5 class="text-muted">
                    Settore di riferimento: <span class="text-brand fw-bold"><?php echo htmlspecialchars($nome_settore); ?></span>
                </h5>
            </div>
            
            <div class="col-md-4 text-end">
                <a href="prenota.php" class="btn btn-primary shadow-sm">
                    + Nuova Prenotazione
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-light border-0 py-3">
                <h5 class="mb-0 text-dark">Elenco Sale e Dotazioni</h5>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 30%;">Nome Sala</th>
                                <th style="width: 15%;">Capienza</th>
                                <th style="width: 40%;">Dotazioni</th>
                                <th class="text-end pe-4" style="width: 15%;">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($sale) > 0): ?>
                                <?php foreach ($sale as $s): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-brand">
                                            <?php echo htmlspecialchars($s['nome_sala']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary rounded-pill">
                                                <?php echo $s['capienza_max']; ?> posti
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                $dotazioni = getDotazioniSala($cid, $id_settore, $s['nome_sala']);
                                                if ($dotazioni) {
                                                    echo '<small class="text-muted">' . htmlspecialchars($dotazioni) . '</small>';
                                                } else {
                                                    echo '<span class="text-muted small fst-italic">Nessuna dotazione</span>';
                                                }
                                            ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-secondary disabled">Dettagli</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        Nessuna sala trovata per questo settore.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                &larr; Torna alla Dashboard
            </a>
        </div>

    </div>

    <?php require "common/footer.html"; ?>
</body>
</html>