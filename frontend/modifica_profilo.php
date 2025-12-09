<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

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
<?php require ROOT_PATH . "/common/header.html" ?>

<body>
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div class="card shadow-sm rounded-3 p-4 mb-4 border-0">
                    <div class="card-header bg-white border-0 text-center">
                        <h3 class="fw-bold">Modifica Profilo</h3>
                    </div>

                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <form action="<?php echo BASE_URL; ?>backend/update_profile.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-4 text-center">

                                <?php
                                // Recupero dati per la logica di visualizzazione
                                $pathFoto = !empty($dati_utente['foto']) ? $dati_utente['foto'] : '';
                                $hasFoto = !empty($pathFoto) && file_exists($pathFoto);
                                ?>

                                <div class="d-flex flex-column align-items-center gap-3">
                                    <div class="position-relative" style="width: 80px; height: 80px;">

                                        <img id="previewImg"
                                            src="<?php echo $hasFoto ? $pathFoto : '#'; ?>"
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
                                        <label class="btn btn-sm btn-outline-primary" style="cursor: pointer;">
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

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-outline-primary">Salva Modifiche</button>
                                <div class="mt-2">
                                    <a href="<?php echo BASE_URL; ?>frontend/profilo.php" class="btn btn-outline-secondary">Annulla</a>
                                </div>
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
                        <form action="<?php echo BASE_URL; ?>backend/delete_profile.php" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare definitivamente il tuo account? Questa azione non può essere annullata.');">
                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                Elimina il mio account
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php include ROOT_PATH . '/common/footer.html'; ?>

    <script>
        function previewFoto(input) {
            // Se l'utente ha selezionato un file
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    // Prendo i riferimenti ai due elementi HTML
                    var imgElement = document.getElementById('previewImg');
                    var iconElement = document.getElementById('defaultIcon');

                    // Imposto la nuova immagine
                    imgElement.src = e.target.result;

                    // Mostro l'immagine e nascondo l'icona
                    imgElement.classList.remove('d-none');
                    iconElement.classList.add('d-none');
                }

                // Leggo il file caricato
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>