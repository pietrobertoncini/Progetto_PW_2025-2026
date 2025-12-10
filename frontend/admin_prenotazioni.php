<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// SICUREZZA: SOLO ADMIN 
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// RECUPERO DATI
$sale_disponibili = getAllSaleGlobal($cid);

// GESTIONE PARAMETRI FILTRO
$filtro_sala = isset($_GET['sala']) ? $_GET['sala'] : ''; // "id_settore|nome_sala"
$data_rif = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d');

$prenotazioni_griglia = [];
$elenco_lista = [];
$sala_selezionata_info = null;

// CALCOLO SETTIMANA
$timestamp_rif = strtotime($data_rif);
$lunedi_settimana = date('Y-m-d', strtotime('monday this week', $timestamp_rif));
$domenica_settimana = date('Y-m-d', strtotime('sunday this week', $timestamp_rif));
$prev_week = date('Y-m-d', strtotime($lunedi_settimana . ' -7 days'));
$next_week = date('Y-m-d', strtotime($lunedi_settimana . ' +7 days'));

if ($filtro_sala) {
    // VISTA GRIGLIA PER SINGOLA SALA
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
    // VISTA LISTA GLOBALE
    $elenco_lista = getAllPrenotazioniAdmin($cid);
}
?>

<!DOCTYPE html>
<html lang="it" class="h-100">
<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column h-100">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestione Prenotazioni</h2>
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

        <div class="card shadow-sm border-0 rounded-4 mb-4 bg-light">
            <div class="card-body p-3">
                <form method="GET" action="<?php echo BASE_URL; ?>frontend/admin_prenotazioni.php" class="row g-3 align-items-center">
                    <input type="hidden" name="week" value="<?php echo $lunedi_settimana; ?>">

                    <div class="col-auto">
                        <label class="fw-bold text-muted"><i class="bi bi-funnel-fill"></i> Filtra per Sala:</label>
                    </div>
                    <div class="col-md-5">
                        <select name="sala" class="form-select rounded-pill border-secondary" onchange="this.form.submit()">
                            <option value="">-- Mostra Tutte (Lista Globale) --</option>
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
                    </div>
                </form>
            </div>
        </div>
        <?php if ($filtro_sala): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="?week=<?php echo $prev_week; ?>&sala=<?php echo urlencode($filtro_sala); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    &larr; Settimana Prec.
                </a>
                <h5 class="mb-0 fw-bold text-center">
                    Dal <?php echo date('d/m', strtotime($lunedi_settimana)); ?>
                    al <?php echo date('d/m', strtotime($domenica_settimana)); ?>
                </h5>
                <a href="?week=<?php echo $next_week; ?>&sala=<?php echo urlencode($filtro_sala); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    Settimana Succ. &rarr;
                </a>
            </div>
        <?php endif; ?>

        <?php if ($filtro_sala && $sala_selezionata_info): ?>

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table calendar-table mb-0 text-center align-middle">
                        <thead>
                            <tr>
                                <th class="align-middle" style="width: 60px;">Ora</th>
                                <?php
                                $giorni_it = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
                                for ($i = 0; $i < 7; $i++) {
                                    $d = date('Y-m-d', strtotime($lunedi_settimana . " +$i days"));
                                    $giorno_str = date('d/m', strtotime($d));
                                    $nome_giorno = $giorni_it[$i];
                                    $class_th = ($d == date('Y-m-d')) ? 'bg-warning bg-opacity-25 text-dark' : '';

                                    echo "<th class='$class_th' style='width: 13%'>$nome_giorno<br><small class='fw-normal'>$giorno_str</small></th>";
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($ora = 9; $ora <= 23; $ora++): ?>
                                <tr>
                                    <td class="align-middle fw-bold"><?php echo $ora; ?>:00</td>

                                    <?php for ($i = 0; $i < 7; $i++):
                                        $data_curr = date('Y-m-d', strtotime($lunedi_settimana . " +$i days"));

                                        if (isset($prenotazioni_griglia[$data_curr][$ora])) {

                                            $cell = $prenotazioni_griglia[$data_curr][$ora];
                                            $info = $cell['dati'];

                                            // È l'inizio dell'impegno -> Stampiamo la cella con ROWSPAN
                                            if ($cell['is_start']) {
                                                $durata = $info['durata'];
                                    ?>
                                                <td rowspan="<?php echo $durata; ?>" class="p-2 bg-info bg-opacity-10 border border-info border-opacity-25 position-relative">
                                                    <div class="d-flex flex-column justify-content-center align-items-center h-100 w-100" style="min-height: <?php echo ($durata * 40); ?>px;">

                                                        <div class="fw-bold text-dark lh-sm mb-1">
                                                            <?php echo htmlspecialchars($info['attivita']); ?>
                                                        </div>

                                                        <div class="small text-muted mb-2">
                                                            <i class="bi bi-person-fill"></i>
                                                            <?php echo htmlspecialchars($info['nome_org'] . ' ' . substr($info['cognome_org'], 0, 1) . '.'); ?>
                                                        </div>

                                                        <form action="<?php echo BASE_URL; ?>backend/admin_prenotazioni_exe.php" method="POST" onsubmit="return confirm('Eliminare questa prenotazione?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id_settore" value="<?php echo $info['id_settore']; ?>">
                                                            <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($info['nome_sala']); ?>">
                                                            <input type="hidden" name="data" value="<?php echo $info['data']; ?>">
                                                            <input type="hidden" name="ora" value="<?php echo $info['ora']; ?>">

                                                            <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-pill shadow-sm" style="font-size: 0.7rem;">
                                                                <i class="bi bi-trash3"></i> Elimina
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                    <?php
                                            }
                                        } else {
                                            echo "<td>-</td>";
                                        }
                                    endfor; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <span class="fw-bold text-muted"><i class="bi bi-list-ul"></i> Tutte le prenotazioni (<?php echo count($elenco_lista); ?>)</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-muted text-uppercase">
                            <tr>
                                <th class="ps-4">Data & Ora</th>
                                <th>Sala & Settore</th>
                                <th>Attività</th>
                                <th>Organizzatore</th>
                                <th class="text-end pe-4">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <?php if (count($elenco_lista) > 0): ?>
                                <?php foreach ($elenco_lista as $p):
                                    $is_passata = (strtotime($p['data']) < strtotime(date('Y-m-d')));
                                ?>
                                    <tr class="<?php echo $is_passata ? 'bg-light text-muted' : ''; ?>">
                                        <td class="ps-4">
                                            <span class="fw-bold d-block"><?php echo date("d/m/Y", strtotime($p['data'])); ?></span>
                                            <small><?php echo $p['ora']; ?>:00 - <?php echo $p['ora'] + $p['durata']; ?>:00</small>
                                        </td>
                                        <td>
                                            <span class="d-block fw-bold" style="color: #7A5E4E;"><?php echo htmlspecialchars($p['nome_sala']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($p['nome_settore']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($p['attivita']); ?></td>
                                        <td>
                                            <small><?php echo htmlspecialchars($p['nome_org'] . ' ' . $p['cognome_org']); ?></small>
                                        </td>
                                        <td class="text-end pe-4">
                                            <form action="<?php echo BASE_URL; ?>backend/admin_prenotazioni_exe.php" method="POST" onsubmit="return confirm('Eliminare definitivamente?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id_settore" value="<?php echo $p['id_settore']; ?>">
                                                <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($p['nome_sala']); ?>">
                                                <input type="hidden" name="data" value="<?php echo $p['data']; ?>">
                                                <input type="hidden" name="ora" value="<?php echo $p['ora']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle"><i class="bi bi-trash3"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Nessuna prenotazione trovata.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>

</html>