<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ---> Questo è il "GUARD" <---
if (!isset($_SESSION['id_utente'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>

    <?php include 'common/navbar.php'; ?>

    <div class="container mt-5">
        <h2>La tua Dashboard</h2>
        <p class="fs-5">Ciao, <strong><?php echo htmlspecialchars(ucfirst($_SESSION['nome'])); ?></strong>!</p>
        <p>Il tuo ruolo è: <?php echo htmlspecialchars(ucfirst($_SESSION['ruolo'])); ?></p>

        <?php if ($_SESSION['is_responsabile']): ?>
            <div class="alert alert-info" role="alert">
                Sei un responsabile di settore. Puoi accedere alla <a href="gestione_prenotazioni.php" class="alert-link">gestione delle sale</a>.
            </div>
        <?php endif; ?>

        <p>Da qui potrai vedere i tuoi inviti e le tue prenotazioni.</p>
    </div>

    <?php
    require "common/footer.html";
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>