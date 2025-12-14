<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/common/setup.php';
require_once __DIR__ . '/common/function.php';

// Recupero statistiche settori e sale
// Musica
$num_settori_musica = getNumeroSettoriPerTipo($cid, 'musica');
$num_sale_musica = getNumeroSalePerTipo($cid, 'musica');

// Teatro
$num_settori_teatro = getNumeroSettoriPerTipo($cid, 'teatro');
$num_sale_teatro = getNumeroSalePerTipo($cid, 'teatro');

// Ballo
$num_settori_ballo = getNumeroSettoriPerTipo($cid, 'ballo');
$num_sale_ballo = getNumeroSalePerTipo($cid, 'ballo');
?>

<!DOCTYPE html>
<html lang="it">
<?php require ROOT_PATH . "/common/header.php" ?>

<body>

    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <header class="hero-section text-center text-white d-flex align-items-center justify-content-center mb-0">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Prenota il tuo spazio creativo</h1>

                    <p class="lead mb-4 fw-normal">
                        Gestisci le sale prova, organizza eventi teatrali e pianifica le tue lezioni di ballo.
                        Tutto in un unico posto, semplice e veloce.
                    </p>

                    <div class="d-flex justify-content-center gap-3">
                        <a href="#musica" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold rounded-pill">
                            Scopri <i class="bi bi-arrow-down-short"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="sezioni">

        <section id="musica" class="py-5 border-top border-1 border-secondary-subtle">
            <div class="container-xl my-4">
                <div class="row gy-4 gx-lg-5 align-items-center">

                    <div class="col-lg-6 px-4 px-lg-0">
                        <h2 class="display-6 fw-bold mb-3">Musica</h2>

                        <p class="text-muted mb-5 fs-6">
                            Immergiti in ambienti progettati per il suono puro senza compromessi.
                            Le nostre sale musica offrono un'acustica trattata professionalmente con pannelli fonoassorbenti e bass traps, ideali per band al completo, ensemble acustici o sessioni di pratica individuale. Troverai spazi climatizzati, dotati di amplificazione di base di alta qualità, mixer multicanale e batteria standard.
                        </p>

                        <div class="row g-4 justify-content-center">
                            <div class="col-auto">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center stat-card" style="min-width: 120px;">
                                    <span class="d-block h4 fw-bold mb-0"><?php echo $num_settori_musica; ?></span>
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Settore</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center stat-card" style="min-width: 120px;">
                                    <span class="d-block h4 fw-bold mb-0"><?php echo $num_sale_musica; ?></span>
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Sale Totali</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mt-5 mt-lg-0 px-4">
                        <img src="images/musica.jpg"
                            alt="Sala Musica"
                            class="img-fluid rounded-4 shadow-lg w-100"
                            style="object-fit: cover; min-height: 350px;">
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12 col-lg-6 text-center">
                        <a href="#" class="btn btn-outline-secondary px-5 rounded-pill shadow-sm">Scopri le sale</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="teatro" class="py-5 border-top border-1 border-secondary-subtle">
            <div class="container-xl my-4">
                <div class="row gy-4 gx-lg-5 align-items-center">

                    <div class="col-lg-6 px-4 px-lg-0 order-1 order-lg-2">
                        <h2 class="display-6 fw-bold mb-3">Teatro</h2>

                        <p class="text-muted mb-5 fs-6">
                            Dai vita alle tue storie sul palcoscenico. I nostri spazi teatrali sono versatili e modulari, adatti per prove di recitazione, workshop intensivi e piccole messe in scena.
                            Alcune sale dispongono di quinte mobili, illuminazione di base regolabile e un'area platea per un pubblico ristretto, offrendo un'esperienza di prova autentica e coinvolgente.
                        </p>

                        <div class="row g-4 justify-content-center">
                            <div class="col-auto">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center stat-card" style="min-width: 120px;">
                                    <span class="d-block h4 fw-bold mb-0"><?php echo $num_settori_teatro; ?></span>
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Settore</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center stat-card" style="min-width: 120px;">
                                    <span class="d-block h4 fw-bold mb-0"><?php echo $num_sale_teatro; ?></span>
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Sale Totali</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mt-5 mt-lg-0 order-2 order-lg-1 px-4">
                        <img src="images/teatro.jpg"
                            alt="Sala Teatro"
                            class="img-fluid rounded-4 shadow-lg w-100"
                            style="object-fit: cover; min-height: 350px;">
                    </div>
                </div>

                <div class="row mt-5 order-3">
                    <div class="col-12 col-lg-6"></div>
                    <div class="col-12 col-lg-6 text-center">
                        <a href="#" class="btn btn-outline-secondary px-5 rounded-pill shadow-sm">Scopri le sale</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="ballo" class="py-5 border-top border-1 border-secondary-subtle">
            <div class="container-xl my-4">
                <div class="row gy-4 gx-lg-5 align-items-center">

                    <div class="col-lg-6 px-4 px-lg-0">
                        <h2 class="display-6 fw-bold mb-3">Ballo</h2>

                        <p class="text-muted mb-5 fs-6">
                            Libera il movimento in spazi ampi e luminosi. Le nostre sale da ballo sono dotate di pavimentazioni specifiche ammortizzanti, pareti a specchio su tutta la lunghezza e sbarre per la danza classica.
                            Perfette per corpi di ballo, lezioni di gruppo o prove coreografiche, offrono l'ambiente sicuro e spazioso di cui hai bisogno per esprimerti al meglio.
                        </p>

                        <div class="row g-4 justify-content-center">
                            <div class="col-auto">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center stat-card" style="min-width: 120px;">
                                    <span class="d-block h4 fw-bold mb-0"><?php echo $num_settori_ballo; ?></span>
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Settore</small>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="p-3 bg-white rounded-4 text-center shadow-sm border h-100 d-flex flex-column justify-content-center stat-card" style="min-width: 120px;">
                                    <span class="d-block h4 fw-bold mb-0"><?php echo $num_sale_ballo; ?></span>
                                    <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Sale Totali</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mt-5 mt-lg-0 px-4">
                        <img src="images/ballo.jpg"
                            alt="Sala Ballo"
                            class="img-fluid rounded-4 shadow-lg w-100"
                            style="object-fit: cover; min-height: 350px;">
                    </div>
                </div>

                <div class="row mt-5">
                    <div class="col-12 col-lg-6 text-center">
                        <a href="#" class="btn btn-outline-secondary px-5 rounded-pill shadow-sm">Scopri le sale</a>
                    </div>
                </div>
            </div>
        </section>
    </div>


    <?php
    require ROOT_PATH . "/common/footer.html";
    ?>

</body>

</html>