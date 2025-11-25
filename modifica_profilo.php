<?php
// modifica_profilo.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'common/setup.php';
require_once 'common/function.php';

// Controllo login
if (!isset($_SESSION['id_utente'])) {
    header("Location: login.php");
    exit;
}

// Recupero dati attuali
$dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
?>

<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>
    <?php include 'common/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                
                <div class="card card-custom bg-white p-4 mb-4">
                    <div class="card-header bg-white border-0 text-center">
                        <h3 class="fw-bold text-brand">Modifica Profilo</h3>
                    </div>

                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <form action="backend/update_profile.php" method="POST">
                            <div class="mb-3">
                                <label for="nome" class="form-label text-muted">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" 
                                       value="<?php echo htmlspecialchars($dati_utente['nome']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="cognome" class="form-label text-muted">Cognome</label>
                                <input type="text" class="form-control" id="cognome" name="cognome" 
                                       value="<?php echo htmlspecialchars($dati_utente['cognome']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label text-muted">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($dati_utente['email']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="data_nascita" class="form-label text-muted">Data di Nascita</label>
                                <input type="date" class="form-control" id="data_nascita" name="data_nascita" 
                                       value="<?php echo $dati_utente['data_nascita']; ?>" required>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-brand btn-lg">Salva Modifiche</button>
                                <a href="profilo.php" class="btn btn-outline-secondary">Annulla</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-danger shadow-sm">
                    <div class="card-body text-center">
                        <h5 class="card-title text-danger fw-bold">Zona Pericolo</h5>
                        <p class="card-text small text-muted">
                            L'eliminazione dell'account è irreversibile. Tutte le tue prenotazioni future verranno mantenute ma non potrai più accedere.
                        </p>
                        <form action="backend/delete_profile.php" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare definitivamente il tuo account? Questa azione non può essere annullata.');">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                Elimina il mio account
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include 'common/footer.html'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>