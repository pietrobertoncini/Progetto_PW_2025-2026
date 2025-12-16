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
$impegni_lista = [];
if (function_exists('getImpegniFuturi')) {
    $impegni_lista = getImpegniFuturi($cid, $_SESSION['id_utente']);
}

// ORGANIZZAZIONE DATI IN GRIGLIA 
$planning = [];

foreach ($impegni_lista as $imp) {
    $data_imp = $imp['data'];
    $ora_inizio = $imp['ora'];
    $durata = $imp['durata'];

    for ($i = 0; $i < $durata; $i++) {
        $ora_corrente = $ora_inizio + $i;

        // Salviamo i dati per ogni ora occupata
        $planning[$data_imp][$ora_corrente] = [
            'dati' => $imp,
            'is_start' => ($i === 0) // True solo per la prima ora
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="it">
<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column h-100">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>I Tuoi Impegni</h2>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="?week=<?php echo $prev_week; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                &larr; Settimana Prec.
            </a>
            <h5 class="mb-0 fw-bold text-center">
                Dal <?php echo date('d/m', strtotime($lunedi_settimana)); ?>
                al <?php echo date('d/m', strtotime($domenica_settimana)); ?>
            </h5>
            <a href="?week=<?php echo $next_week; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                Settimana Succ. &rarr;
            </a>
        </div>

        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table calendar-table mb-0 text-center align-middle">
                    <thead>
                        <tr>
                            <th class="align-middle" style="width: 40px;">Ora</th>
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
                        <?php for ($ora = 9; $ora < 23; $ora++): ?>
                            <tr>
                                <td class="align-middle fw-bold"><?php echo $ora; ?>:00</td>

                                <?php for ($i = 0; $i < 7; $i++):
                                    $data_curr = date('Y-m-d', strtotime($lunedi_settimana . " +$i days"));

                                    // Verifichiamo se c'è un impegno
                                    if (isset($planning[$data_curr][$ora])) {

                                        $cell = $planning[$data_curr][$ora];
                                        $info = $cell['dati'];

                                        // È l'inizio dell'impegno -> Stampiamo la cella con ROWSPAN
                                        if ($cell['is_start']) {
                                            $durata = $info['durata'];

                                            // logica passato/futuro
                                            // Calcoliamo il timestamp di inizio evento
                                            $ts_evento = strtotime($info['data'] . " " . $info['ora'] . ":00");
                                            $is_passato = ($ts_evento < time());

                                            if ($is_passato) {
                                                $bg_class = "bg-secondary bg-opacity-10 border-secondary border-opacity-25";
                                                $text_class = "text-muted";
                                                $icon_class = "text-muted";
                                            } else {
                                                $bg_class = "bg-success bg-opacity-10 border-success border-opacity-25";
                                                $text_class = "text-success";
                                                $icon_class = "text-success";
                                            }
                                ?>
                                            <td rowspan="<?php echo $durata; ?>" class="p-0 border position-relative align-middle <?php echo $bg_class; ?>">

                                                <div class="d-flex flex-column justify-content-center align-items-center w-100 h-100"
                                                    style="min-height: <?php echo ($durata * 40); ?>px;">

                                                    <?php if ($durata == 1): ?>
                                                        <div class="dropdown w-100 h-100 d-flex align-items-center justify-content-between px-2">

                                                            <span class="fw-bold small text-truncate text-start <?php echo $text_class; ?>" style="max-width: 80%;">
                                                                <?php echo htmlspecialchars($info['attivita']); ?>
                                                            </span>

                                                            <button class="btn btn-sm btn-link p-0 text-decoration-none <?php echo $icon_class; ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="bi bi-three-dots-vertical me-1"></i>
                                                            </button>

                                                            <ul class="dropdown-menu shadow border-0">
                                                                <li class="px-3 py-2">
                                                                    <h6 class="dropdown-header p-0 fw-bold text-dark">Dettagli <?php echo $is_passato ? '(Concluso)' : ''; ?></h6>
                                                                    <div class="small text-muted fst-italic" style="min-width: 200px;">
                                                                        <?php echo htmlspecialchars($info['nome_sala']); ?><br>
                                                                        (<?php echo $info['ora']; ?>:00 - <?php echo $info['ora'] + $durata; ?>:00)<br>
                                                                        <i class="bi bi-calendar me-1"></i> <?php echo date("d/m/Y", strtotime($info['data'])); ?>
                                                                    </div>
                                                                </li>

                                                                <?php if (!$is_passato): ?>
                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>
                                                                    <li>
                                                                        <?php if (!empty($_SESSION['is_responsabile']) && $_SESSION['is_responsabile']): ?>
                                                                            <div class="d-flex justify-content-center">
                                                                                <a href="<?php echo BASE_URL; ?>frontend/modifica_prenotazione.php?sala=<?php echo urlencode($info['nome_sala']); ?>&data=<?php echo $info['data']; ?>&ora=<?php echo $info['ora']; ?>"
                                                                                    class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill shadow-sm">
                                                                                    <i class="bi bi-gear-fill"></i> Gestisci
                                                                                </a>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <div class="d-flex justify-content-center">
                                                                                <form action="<?php echo BASE_URL; ?>backend/invite_reply.php" method="POST" class="px-2 pb-1">
                                                                                    <input type="hidden" name="id_settore" value="<?php echo $info['id_settore']; ?>">
                                                                                    <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($info['nome_sala']); ?>">
                                                                                    <input type="hidden" name="data" value="<?php echo $info['data']; ?>">
                                                                                    <input type="hidden" name="ora" value="<?php echo $info['ora']; ?>">
                                                                                    <input type="hidden" name="risposta" value="rifiutato">
                                                                                    <input type="hidden" name="motivazione" value="Disdetta manuale">

                                                                                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-pill shadow-sm" onclick="return confirm('Vuoi davvero disdire?');">
                                                                                        <i class="bi bi-trash3"></i> Disdici
                                                                                    </button>
                                                                                </form>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </li>
                                                                <?php endif; // fine check passato 
                                                                ?>
                                                            </ul>
                                                        </div>

                                                    <?php else: ?>
                                                        <div class="p-2 text-center w-100">
                                                            <div class="fw-bold lh-sm mb-1 text-truncate px-1 <?php echo $text_class; ?>">
                                                                <?php echo htmlspecialchars($info['attivita']); ?>
                                                            </div>

                                                            <div class="small text-muted fst-italic mb-2">
                                                                <span class="d-block text-truncate"><?php echo htmlspecialchars($info['nome_sala']); ?></span>
                                                                (<?php echo $info['ora']; ?>:00 - <?php echo $info['ora'] + $durata; ?>:00)
                                                            </div>

                                                            <?php if (!$is_passato): ?>
                                                                <?php if (!empty($_SESSION['is_responsabile']) && $_SESSION['is_responsabile']): ?>
                                                                    <a href="<?php echo BASE_URL; ?>frontend/modifica_prenotazione.php?sala=<?php echo urlencode($info['nome_sala']); ?>&data=<?php echo $info['data']; ?>&ora=<?php echo $info['ora']; ?>"
                                                                        class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill shadow-sm">
                                                                        <i class="bi bi-gear-fill"></i> Gestisci
                                                                    </a>
                                                                <?php else: ?>
                                                                    <form action="<?php echo BASE_URL; ?>backend/invite_reply.php" method="POST" class="px-2 pb-1">
                                                                        <input type="hidden" name="id_settore" value="<?php echo $info['id_settore']; ?>">
                                                                        <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($info['nome_sala']); ?>">
                                                                        <input type="hidden" name="data" value="<?php echo $info['data']; ?>">
                                                                        <input type="hidden" name="ora" value="<?php echo $info['ora']; ?>">
                                                                        <input type="hidden" name="risposta" value="rifiutato">
                                                                        <input type="hidden" name="motivazione" value="Disdetta manuale">

                                                                        <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-pill shadow-sm" onclick="return confirm('Vuoi davvero disdire?');">
                                                                            <i class="bi bi-trash3"></i> Disdici
                                                                        </button>
                                                                    </form>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary text-white rounded-pill" style="font-size: 0.7rem;">Concluso</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>

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

        <div class="mt-3 text-muted small">
            <i class="bi bi-info-circle me-1"></i> Visualizzi solo gli impegni che hai accettato.
        </div>

    </div>

    <?php require ROOT_PATH . "/common/footer.html"; ?>
</body>

</html>