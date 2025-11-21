<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>

    <?php include 'common/navbar.php'; ?>

    <div class="container mt-5">
        <div class="p-5 mb-4 bg-light rounded-3 shadow">
            <div class="container-fluid py-4">
                <h1 class="display-5 fw-bold">Benvenuto su Play Room Planner!</h1>
                <p class="col-md- fs-4">Accedi o registrati per iniziare a prenotare le sale e gestire i tuoi inviti.</p>

                <?php if (!isset($_SESSION['id_utente'])): ?>
                    <a href="login.php" class="btn btn-primary btn-md me-2">Accedi</a>
                    <a href="register.php" class="btn btn-outline-secondary btn-md">Registrati</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-success btn-lg">Vai alla tua Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Card section -->
    <div class="container-fluid my-5 bg-light"">
        <div class=" p-4 p-md-5">
        <div class="row justify-content-center g-4">
            <div class="col-10 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm" style="background-color: #F3EADF; border: 1px solid #D2B48C;">
                    <div class="card-body text-center d-flex flex-column">
                        <h4 class="card-title">Musica</h4>
                        <img class="rounded" src="images/musica.jpg" alt="Musica" style="width:100%;">
                        <p class="card-text mt-3 mb-auto">Sale attrezzate per prove, lezioni e registrazione.</p>

                        <div class="mt-3">
                            <a href="#" class="btn btn-outline-secondary">Scopri le sale</a>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-10 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm" style="background-color: #F3EADF; border: 1px solid #D2B48C;">
                    <div class="card-body d-flex flex-column text-center">
                        <h4 class="card-title">Teatro</h4>
                        <img class="rounded" src="images/teatro.jpg" alt="Musica" style="width:100%;">
                        <p class="card-text mt-3 mb-auto">Spazi con palcoscenico per workshop e performance.</p>

                        <div class="mt-3">
                            <a href="#" class="btn btn-outline-secondary">Scopri le sale</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-10 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm" style="background-color: #F3EADF; border: 1px solid #D2B48C;">
                    <div class="card-body d-flex flex-column text-center">
                        <h4 class="card-title">Ballo</h4>
                        <img class="rounded" src="images/ballo.jpg" alt="Musica" style="width:100%;">
                        <p class="card-text mt-3 mb-auto">Sale con pavimento tecnico, specchi e sbarre.</p>

                        <div class="mt-3">
                            <a href="#" class="btn btn-outline-secondary">Scopri le sale</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>