<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Controllo sicurezza
if (!isset($_SESSION['id_utente'])) {
    header('Location: ' . BASE_URL . 'frontend/login.php');
    exit;
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// GESTIONE DATE SETTIMANA 
$data_rif = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d');
$timestamp_rif = strtotime($data_rif);

$lunedi_settimana = date('Y-m-d', strtotime('monday this week', $timestamp_rif));
$domenica_settimana = date('Y-m-d', strtotime('sunday this week', $timestamp_rif));

$prev_week = date('Y-m-d', strtotime($lunedi_settimana . ' -7 days'));
$next_week = date('Y-m-d', strtotime($lunedi_settimana . ' +7 days'));

// RECUPERO DATI 
$planning = [];
if (function_exists('getImpegniFuturi')) {
    $planning = getImpegniFuturi($cid, $_SESSION['id_utente']);
}
?>

<!DOCTYPE html>
<html lang="it" class="no-js">
<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>I Tuoi Impegni</h2>
        </div>

        <div id="nav-row" class="d-flex justify-content-between align-items-center mb-3 <?php echo $class_nav_hidden; ?>">
            <a href="?week=<?php echo $prev_week; ?>&sala=<?php echo urlencode($filtro_sala); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 nav-week-btn">
                &larr; Settimana Prec.
            </a>
            <h5 class="mb-0 fw-bold text-center">
                Dal <?php echo date('d/m', strtotime($lunedi_settimana)); ?>
                al <?php echo date('d/m', strtotime($domenica_settimana)); ?>
            </h5>
            <a href="?week=<?php echo $next_week; ?>&sala=<?php echo urlencode($filtro_sala); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 nav-week-btn">
                Settimana Succ. &rarr;
            </a>
        </div>

        <input type="hidden" name="week" value="<?php echo $lunedi_settimana; ?>">

        <div id="calendario-container">
            <?php
            // Recuperiamo lo stato di responsabile dalla sessione
            $is_resp = !empty($_SESSION['is_responsabile']);

            if (function_exists('renderCalendarGrid_Impegni')) {
                echo renderCalendarGrid_Impegni($lunedi_settimana, $planning, $is_resp);
            }
            ?>
        </div>

        <div class="alert alert-info d-inline-block py-2 small border-0 bg-info bg-opacity-10 rounded-4 mb-2 text-start">
            <i class="bi bi-info-circle-fill text-info ms-2"></i> Visualizzi solo gli impegni che hai accettato.
        </div>

    </div>

    <script src="<?php echo BASE_URL; ?>js/calendar.js"></script>

    <?php require ROOT_PATH . "/common/footer.html"; ?>
</body>

</html>