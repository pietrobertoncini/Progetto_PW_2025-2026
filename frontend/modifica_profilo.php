<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Controllo login
if (!isset($_SESSION['id_utente'])) {
    header("Location: " . BASE_URL . "frontend/login.php");
    exit;
}

// Recupero dati attuali
$dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
?>

<!DOCTYPE html>
<html lang="it">
<?php require ROOT_PATH . "/common/header.php" ?>

<body>
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div class="card shadow-sm rounded-4 p-4 mb-4 border-0 overflow-hidden">
                    <div class="card-header bg-white border-0 text-center">
                        <h2 class="fw-bold">Modifica Profilo</h2>
                    </div>

                    <div class="card-body">

                        <div id="messaggioAjax" class="alert d-none"></div>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <form action="<?php echo BASE_URL; ?>backend/update_profile.php" method="POST" enctype="multipart/form-data" id="updateProfileForm">
                            <div class="mb-4 text-center">

                                <?php
                                // Recupero dati per la logica di visualizzazione
                                $fotoDbPath = $dati_utente['foto'];
                                $percorsoFisico = __DIR__ . '/../' . $fotoDbPath; // Risale alla root
                                $percorsoWeb = BASE_URL . $fotoDbPath;
                                $hasFoto = !empty($fotoDbPath) && file_exists($percorsoFisico);
                                ?>

                                <div class="d-flex flex-column align-items-center gap-3">
                                    <div class="position-relative" style="width: 80px; height: 80px;">

                                        <img id="previewImg"
                                            src="<?php echo $hasFoto ? $percorsoWeb : '#'; ?>"
                                            alt="Anteprima"
                                            class="rounded-circle shadow-sm <?php echo $hasFoto ? '' : 'd-none'; ?>"
                                            style="width: 80px; height: 80px; object-fit: cover;">

                                        <div id="defaultIcon"
                                            class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center shadow-sm <?php echo $hasFoto ? 'd-none' : ''; ?>"
                                            style="width: 80px; height: 80px;">
                                            <i class="bi bi-person-fill fs-2"></i>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-center mt-3">
                                        <label class="btn btn-sm btn-outline-primary rounded-pill" style="cursor: pointer;">
                                            Cambia Foto
                                            <input type="file" name="foto" id="fileInput" class="d-none" onchange="previewFoto(this)">
                                        </label>

                                        <?php if ($hasFoto): ?>
                                            <a href="<?php echo BASE_URL; ?>backend/remove_foto.php"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Elimina foto attuale"
                                                onclick="return confirm('Sei sicuro di voler eliminare la foto?');">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="nome" class="form-label text-muted">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome"
                                    value="<?php echo htmlspecialchars($dati_utente['nome']); ?>" required>
                                <span id="errNome" class="text-danger small text-danger-custom"></span>
                            </div>

                            <div class="mb-3">
                                <label for="cognome" class="form-label text-muted">Cognome</label>
                                <input type="text" class="form-control" id="cognome" name="cognome"
                                    value="<?php echo htmlspecialchars($dati_utente['cognome']); ?>" required>
                                <span id="errCognome" class="text-danger small text-danger-custom"></span>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label text-muted">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($dati_utente['email']); ?>" required>
                                <span id="errEmail" class="text-danger small text-danger-custom"></span>
                            </div>

                            <div class="mb-3">
                                <label for="data_nascita" class="form-label text-muted">Data di Nascita</label>
                                <input type="date" class="form-control" id="data_nascita" name="data_nascita"
                                    value="<?php echo $dati_utente['data_nascita']; ?>" required>
                                <span id="errData" class="text-danger small text-danger-custom"></span>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-outline-primary rounded-pill">Salva Modifiche</button>
                                <div class="mt-2">
                                    <a href="<?php echo BASE_URL; ?>frontend/profilo.php" class="btn btn-outline-secondary rounded-pill">Annulla</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-danger shadow-sm rounded-4">
                    <div class="card-body text-center">
                        <h5 class="card-title text-danger fw-bold">Zona Pericolo</h5>
                        <p class="card-text small text-muted">
                            L'eliminazione dell'account è irreversibile. Tutte le tue prenotazioni future verranno mantenute ma non potrai più accedere.
                        </p>
                        <form action="<?php echo BASE_URL; ?>backend/delete_profile.php" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare definitivamente il tuo account? Questa azione non può essere annullata.');">
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill">
                                Elimina il mio account
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include ROOT_PATH . '/common/footer.html'; ?>

    <script src="<?php echo BASE_URL; ?>js/modifica_profilo.js"></script>
</body>

</html>