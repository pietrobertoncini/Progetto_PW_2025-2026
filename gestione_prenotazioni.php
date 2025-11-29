<?php
// gestione_prenotazioni.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclusione setup DB e funzioni
require_once __DIR__ . '/common/setup.php';
require_once __DIR__ . '/common/function.php';

// --- 1. SICUREZZA ---
// Accesso consentito solo ai responsabili
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_responsabile'])) {
    header('Location: dashboard.php');
    exit;
}

// --- 2. RECUPERO DATI UTENTE E SETTORE ---
$id_utente_loggato = $_SESSION['id_utente']; // ID di chi è loggato
$dati_utente = datiUtenteCompleti($cid, $id_utente_loggato);
$id_settore = $dati_utente['id_settore'];
$nome_settore = $dati_utente['nome_settore'];

// --- 3. RECUPERO PRENOTAZIONI ATTIVE (FILTRATE PER ORGANIZZATORE) ---
// Modifica: Aggiunto "AND id_organizzatore = ?" per vedere solo le TUE prenotazioni
$prenotazioni = [];
$sql_prenotazioni = "SELECT * FROM PRENOTAZIONE 
                     WHERE id_settore = ? 
                     AND id_organizzatore = ? 
                     AND data >= CURDATE() 
                     ORDER BY data ASC, ora ASC";

$stmt = $cid->prepare($sql_prenotazioni);
// "ii" indica due interi: id_settore e id_utente_loggato
$stmt->bind_param("ii", $id_settore, $id_utente_loggato);
$stmt->execute();
$result = $stmt->get_result();
$prenotazioni = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>

    <?php include 'common/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        
        <div class="row align-items-center mb-4 mt-4">
            <div class="col-md-8">
                <h2 class="mb-2">Le Mie Prenotazioni</h2>
                <h5 class="text-muted">
                    Settore di riferimento: <span class="text-brand fw-bold"><?php echo htmlspecialchars($nome_settore); ?></span>
                </h5>
            </div>
            
            <div class="col-md-4 text-end">
                <a href="prenota.php" class="btn btn-primary shadow-sm">
                    <i class="bi bi-plus-lg"></i> Nuova Prenotazione
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
                <h5 class="mb-0 text-dark">Elenco Prenotazioni Create da Te</h5>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
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
                                <?php foreach ($prenotazioni as $p): ?>
                                    <tr>
                                        <td class="ps-4 py-3 fw-bold text-brand">
                                            <?php echo htmlspecialchars($p['nome_sala']); ?>
                                        </td>
                                        
                                        <td>
                                            <?php echo date("d/m/Y", strtotime($p['data'])); ?>
                                        </td>

                                        <td> 
                                            <?php 
                                                $ora_fine = $p['ora'] + $p['durata'];
                                                echo $p['ora'] . ":00 - " . $ora_fine . ":00"; 
                                            ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($p['attivita']); ?>
                                        </td>

                                        <td class="text-end pe-4">
                                            <a href="modifica_prenotazione.php?sala=<?php echo urlencode($p['nome_sala']); ?>&data=<?php echo $p['data']; ?>&ora=<?php echo $p['ora']; ?>" 
                                            class="btn btn-sm btn-secondary">
                                            <i class="bi bi-gear-fill"></i> Gestisci
                                            </a>
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
        
        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                &larr; Torna alla Dashboard
            </a>
        </div>

    </div>

    <?php require "common/footer.html"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>