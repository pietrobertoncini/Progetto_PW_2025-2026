<?php
// nuova_sala.php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once 'common/setup.php';
require_once 'common/function.php';

if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_responsabile'])) {
    header('Location: dashboard.php'); exit;
}

// Recuperiamo la lista di tutte le dotazioni possibili per mostrarle nei checkbox
$dotazioni_disponibili = getAllDotazioni($cid);
?>

<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>
    <?php include 'common/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="fw-bold text-center" style="color: #7A5E4E;">Crea Nuova Sala</h3>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>
                        
                        <form action="backend/nuova_sala_exe.php" method="POST">
                            
                            <div class="mb-3">
                                <label for="nome_sala" class="form-label fw-bold">Nome Sala</label>
                                <input type="text" class="form-control" id="nome_sala" name="nome_sala" placeholder="Es. Sala Verdi" required>
                            </div>

                            <div class="mb-4">
                                <label for="capienza" class="form-label fw-bold">Capienza Massima</label>
                                <input type="number" class="form-control" id="capienza" name="capienza_max" min="1" placeholder="Es. 20" required>
                            </div>

                            <h5 class="fw-bold mb-3 fs-6 text-uppercase text-muted">Dotazioni Presenti</h5>
                            <div class="row g-2 mb-4">
                                <?php foreach ($dotazioni_disponibili as $d): ?>
                                    <div class="col-md-6">
                                        <div class="form-check border rounded p-2 bg-light">
                                            <input class="form-check-input ms-1" type="checkbox" name="dotazioni[]" value="<?php echo $d['id_dotazione']; ?>" id="dot_<?php echo $d['id_dotazione']; ?>">
                                            <label class="form-check-label ms-2" for="dot_<?php echo $d['id_dotazione']; ?>">
                                                <?php echo htmlspecialchars($d['tipo']); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Crea Sala</button>
                                <a href="gestione_sale.php" class="btn btn-outline-secondary">Annulla</a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'common/footer.html'; ?>
</body>
</html>