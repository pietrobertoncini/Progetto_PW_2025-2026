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

    <div class="container my-5">
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

    <div class="sezioni">
        
        <section id="musica" class="py-5 border-top border-1 border-secondary-subtle">
            <div class="container my-4">
                <div class="row gy-4 gx-lg-5 align-items-start">

                    <div class="col-lg-6 order-1 px-3 px-lg-0" >
                        <h2 class="display-5 fw-bold mb-4 me-lg-4">Musica</h2>

                        <p class="lead text-muted mb-4 me-lg-4"> Immergiti in ambienti progettati per il suono puro senza compromessi.
                            Le nostre sale musica offrono un'acustica trattata professionalmente con pannelli fonoassorbenti e bass traps, ideali per band al completo, ensemble acustici o sessioni di pratica individuale. Troverai spazi climatizzati, dotati di amplificazione di base di alta qualità, mixer multicanale e batteria standard.
                        </p>

                        <div class="row g-5 justify-content-center">
                            <div class="col-4">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center">
                                    <span class="d-block h2 fw-bold mb-1">1</span>
                                    <small class="text-muted text-uppercase fw-bold ls-1">Settore</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center">
                                    <span class="d-block h2 fw-bold mb-1">3</span>
                                    <small class="text-muted text-uppercase fw-bold ls-1">Sale Totali</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 order-2 mt-5" >
                        <img src="images/musica.jpg"
                            alt="Sala Musica"
                            class="img-fluid rounded-4 shadow-lg w-100"
                            style="object-fit: cover;">
                    </div>

                </div>
                <div class="row mt-5">
                    <div class="col-12 col-lg-6 text-center">
                        <a href="gestione_sale.php" class="btn btn-secondary btn-lg px-5 rounded-3 shadow-sm">Scopri le sale</a>
                    </div>
                </div>

            </div>
        </section>

        <section id="teatro" class="py-5 border-top border-1 border-secondary-subtle">
            <div class="container my-4">
                <div class="row gy-4 gx-lg-5 align-items-start">

                    <div class="col-lg-6 order-2 px-3 px-lg-0">
                        <h2 class="display-5 fw-bold mb-4 ms-lg-4">Teatro</h2>

                        <p class="lead text-muted mb-4 ms-lg-4"> Dai vita alle tue storie sul palcoscenico. I nostri spazi teatrali sono versatili e modulari, adatti per prove di recitazione, workshop intensivi e piccole messe in scena.
                            Alcune sale dispongono di quinte mobili, illuminazione di base regolabile e un'area platea per un pubblico ristretto, offrendo un'esperienza di prova autentica e coinvolgente.
                        </p>

                        <div class="row g-5 justify-content-center">
                            <div class="col-4">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center">
                                    <span class="d-block h2 fw-bold mb-1">1</span>
                                    <small class="text-muted text-uppercase fw-bold ls-1">Settore</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center">
                                    <span class="d-block h2 fw-bold mb-1">1</span>
                                    <small class="text-muted text-uppercase fw-bold ls-1">Sale Totali</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 order-1 mt-5">
                        <img src="images/teatro.jpg"
                            alt="Sala Musica"
                            class="img-fluid rounded-4 shadow-lg w-100"
                            style="object-fit: cover;">
                    </div>

                </div>
                <div class="row mt-5">
                    <div class="col-12 col-lg-6"></div>
                    <div class="col-12 col-lg-6 text-center">
                        <a href="gestione_sale.php" class="btn btn-secondary btn-lg px-5 rounded-3 shadow-sm">Scopri le sale</a>
                    </div>
                </div>

            </div>
        </section>

        <section id="ballo" class="py-5 border-top border-1 border-secondary-subtle">
            <div class="container my-4">
                <div class="row gy-4 gx-lg-5 align-items-start">

                    <div class="col-lg-6 order-1 px-3 px-lg-0">
                        <h2 class="display-5 fw-bold mb-4 me-lg-4">Ballo</h2>

                        <p class="lead text-muted mb-4 me-lg-4"> Libera il movimento in spazi ampi e luminosi. Le nostre sale da ballo sono dotate di pavimentazioni specifiche ammortizzanti, pareti a specchio su tutta la lunghezza e sbarre per la danza classica.
                            Perfette per corpi di ballo, lezioni di gruppo o prove coreografiche, offrono l'ambiente sicuro e spazioso di cui hai bisogno per esprimerti al meglio.
                        </p>

                        <div class="row g-5 justify-content-center">
                            <div class="col-4">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center">
                                    <span class="d-block h2 fw-bold mb-1">1</span>
                                    <small class="text-muted text-uppercase fw-bold ls-1">Settore</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center">
                                    <span class="d-block h2 fw-bold mb-1">1</span>
                                    <small class="text-muted text-uppercase fw-bold ls-1">Sale Totali</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 order-2 mt-5">
                        <img src="images/ballo.jpg"
                            alt="Sala Musica"
                            class="img-fluid rounded-4 shadow-lg w-100"
                            style="object-fit: cover;">
                    </div>

                </div>
                <div class="row mt-5">
                    <div class="col-12 col-lg-6 text-center">
                        <a href="gestione_sale.php" class="btn btn-secondary btn-lg px-5 rounded-3 shadow-sm">Scopri le sale</a>
                    </div>
                </div>

            </div>
        </section>
    </div>


    <?php
    require "common/footer.html";
    ?>

</body>

</html>