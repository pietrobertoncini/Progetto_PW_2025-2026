<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'common/setup.php';

if (isset($_SESSION['id_utente'])) {
    header('Location: dashboard.php');
    exit;
}

try {
    $query = "SELECT id_settore, nome FROM SETTORE ORDER BY nome";
    $result = $cid->query($query);

    $settori = $result->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    die("Errore: impossibile caricare i settori. " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>

    <?php include 'common/navbar.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="card-title text-center mb-4">Registra un nuovo account</h2>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="backend/register_exe.php" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cognome" class="form-label">Cognome</label>
                                    <input type="text" class="form-control" id="cognome" name="cognome" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="data_nascita" class="form-label">Data di Nascita</label>
                                <input type="date" class="form-control" id="data_nascita" name="data_nascita" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="ruolo" class="form-label">Ruolo</label>
                                    <select class="form-select" id="ruolo" name="ruolo" required>
                                        <option value="allievo">Allievo</option>
                                        <option value="docente">Docente</option>
                                        <option value="tecnico">Tecnico</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="id_settore" class="form-label">Settore di afferenza</label>
                                    <select class="form-select" id="id_settore" name="id_settore" required>
                                        <option value="">-- Seleziona un settore --</option>
                                        <?php foreach ($settori as $settore): ?>
                                            <option value="<?php echo $settore['id_settore']; ?>">
                                                <?php echo htmlspecialchars($settore['nome']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="foto" class="form-label">Foto Profilo (Opzionale)</label>
                                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
                                </div>
                            </div>
                            <div class="d-grid mt-3">
                                <button type="submit" class="btn btn-primary btn-lg">Registrati</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    require "common/footer.html";
    ?>
</body>

</html>