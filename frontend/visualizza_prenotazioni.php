<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// SICUREZZA
if (!isset($_SESSION['id_utente'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// RECUPERO DATI
$sale_disponibili = getAllSaleGlobal($cid);

// PARAMETRI
$filtro_sala = isset($_GET['sala']) ? $_GET['sala'] : ''; // "id_settore|nome_sala"
$data_rif = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d');

$prenotazioni_griglia = [];
$elenco_lista = [];
$sala_selezionata_info = null;

// DATE
$timestamp_rif = strtotime($data_rif);
$lunedi_settimana = date('Y-m-d', strtotime('monday this week', $timestamp_rif));
$domenica_settimana = date('Y-m-d', strtotime('sunday this week', $timestamp_rif));
$prev_week = date('Y-m-d', strtotime($lunedi_settimana . ' -7 days'));
$next_week = date('Y-m-d', strtotime($lunedi_settimana . ' +7 days'));

if ($filtro_sala) {
    // GRIGLIA
    $parts = explode('|', $filtro_sala);
    if (count($parts) === 2) {
        $id_sett_sel = (int)$parts[0];
        $nome_sala_sel = $parts[1];

        foreach ($sale_disponibili as $s) {
            if ($s['id_settore'] == $id_sett_sel && $s['nome_sala'] == $nome_sala_sel) {
                $sala_selezionata_info = $s;
                break;
            }
        }
        $prenotazioni_griglia = getPrenotazioniGriglia($cid, $id_sett_sel, $nome_sala_sel, $lunedi_settimana, $domenica_settimana);
    }
} else {
    // LISTA
    $elenco_lista = getAllPrenotazioniAdmin($cid);
}

// Classe per nascondere la navigazione se non c'è sala selezionata
$class_nav_hidden = $filtro_sala ? '' : 'd-none';
?>

<!DOCTYPE html>
<html lang="it" class="h-100">
<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column h-100">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
            <h2 class="m-0 text-nowrap">Visualizza Prenotazioni</h2>

            <div class="card shadow-sm border-0 rounded-4 bg-light">
                <div class="card-body p-2">
                    <form method="GET" action="<?php echo BASE_URL; ?>frontend/visualizza_prenotazioni.php" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="week" value="<?php echo $lunedi_settimana; ?>">

                        <label class="fw-bold text-muted m-0 text-nowrap"><i class="bi bi-funnel-fill"></i> Sala:</label>

                        <select name="sala" id="sala" class="form-select form-select-sm rounded-pill border-secondary" style="max-width: 300px;">
                            <option value="" <?php echo !$filtro_sala ? 'selected' : ''; ?>>-- Tutte --</option>
                            <?php foreach ($sale_disponibili as $s): ?>
                                <?php
                                $val = $s['id_settore'] . '|' . $s['nome_sala'];
                                $selected = ($filtro_sala === $val) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $val; ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($s['nome_sala']); ?> (<?php echo htmlspecialchars($s['nome_settore']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <noscript><button type="submit" class="btn btn-sm btn-secondary ms-2 rounded-pill">Vai</button></noscript>
                    </form>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success shadow-sm rounded-4 mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger shadow-sm rounded-4 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

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

        <div id="calendario-container">
            <?php if ($filtro_sala && $sala_selezionata_info): ?>

                <?php
                if (function_exists('renderCalendarGrid_View')) {
                    echo renderCalendarGrid_AdminView($lunedi_settimana, $prenotazioni_griglia);
                }
                ?>

            <?php else: ?>

                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom">
                        <span class="fw-bold text-muted"><i class="bi bi-list-ul"></i> Tutte le prenotazioni (<?php echo count($elenco_lista); ?>)</span>
                    </div>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="ps-4">Data & Ora</th>
                                    <th>Sala & Settore</th>
                                    <th>Attività</th>
                                    <th class="pe-4">Organizzatore</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php if (count($elenco_lista) > 0): ?>
                                    <?php foreach ($elenco_lista as $p):
                                        $ts_evento = strtotime($p['data'] . " " . $p['ora'] . ":00");
                                        $is_passata = ($ts_evento < time());
                                    ?>
                                        <tr class="<?php echo $is_passata ? 'bg-light text-muted' : ''; ?>">
                                            <td class="ps-4">
                                                <span class="fw-bold d-block"><?php echo date("d/m/Y", strtotime($p['data'])); ?></span>
                                                <small><?php echo $p['ora']; ?>:00 - <?php echo $p['ora'] + $p['durata']; ?>:00</small>
                                                <?php if ($is_passata): ?>
                                                    <span class="badge bg-secondary mt-1" style="font-size: 0.7em;">Conclusa</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="d-block fw-bold" style="color: #7A5E4E;"><?php echo htmlspecialchars($p['nome_sala']); ?></span>
                                                <small class="text-muted"><?php echo htmlspecialchars($p['nome_settore']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($p['attivita']); ?></td>
                                            <td>
                                                <small><?php echo htmlspecialchars($p['nome_org'] . ' ' . $p['cognome_org']); ?></small>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">Nessuna prenotazione trovata.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>
        </div>

    </div>

    <script src="<?php echo BASE_URL; ?>js/calendar.js"></script>

    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>

</html>