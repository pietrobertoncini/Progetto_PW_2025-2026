<?php

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

$risposta = ["status" => "ko", "html" => ""];

// Verifica parametri minimi
if (!isset($_GET['sala']) || !isset($_GET['week'])) {
    $risposta["msg"] = "Parametri mancanti";
    echo json_encode($risposta);
    exit;
}

$nome_sala = urldecode($_GET['sala']);
$data_rif = $_GET['week'];
$mode = $_GET['mode'] ?? 'view'; // 'prenota', 'view', 'admin'

// Determina settore (dalla sessione o parametro se admin)
$id_settore = 0;
if (isset($_GET['id_settore'])) {
    $id_settore = (int)$_GET['id_settore'];
} elseif (isset($_SESSION['id_utente'])) {
    $dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
    $id_settore = $dati_utente['id_settore'];
}

// Calcolo Date
$timestamp_rif = strtotime($data_rif);
$lunedi_settimana = date('Y-m-d', strtotime('monday this week', $timestamp_rif));
$domenica_settimana = date('Y-m-d', strtotime('sunday this week', $timestamp_rif));

// Recupero Occupazioni
$occupied = [];

// Usiamo la funzione giusta in base alla modalità
if ($mode === 'admin' || $mode === 'view') {
    $occupied = getPrenotazioniGriglia($cid, $id_settore, $nome_sala, $lunedi_settimana, $domenica_settimana);
} else {
    // Modalità PRENOTA (Checkbox)
    $res_p = getOccupazioniSettimana($cid, $nome_sala, $id_settore, $lunedi_settimana, $domenica_settimana);
    if ($res_p) {
        while ($row = $res_p->fetch_assoc()) {
            for ($i = 0; $i < $row['durata']; $i++) {
                $h = $row['ora'] + $i;
                $occupied[$row['data']][$h] = [
                    'info' => $row, // Qui usiamo 'info' come chiave
                    'is_start' => ($i === 0)
                ];
            }
        }
    }
}

// Generazione HTML
try {
    $html = "";

    // SELEZIONE FUNZIONE CORRETTA
    if ($mode === 'view') {
        // Usa la NUOVA funzione specifica per la visualizzazione
        $html = renderCalendarGrid_AdminView($lunedi_settimana, $occupied, false);
    } elseif ($mode === 'admin') {
        // Per ora usiamo quella view anche per admin (provvisorio, poi ne faremo una dedicata se serve)
        // O se preferisci lasciare admin com'era, possiamo creare renderCalendarGrid_Admin dopo.
        $html = renderCalendarGrid_AdminView($lunedi_settimana, $occupied, true);
    } else {
        // Modalità Prenota (quella originale con checkbox)
        $html = renderCalendarGrid($lunedi_settimana, $occupied, false, false);
    }

    $risposta["status"] = "ok";
    $risposta["html"] = $html;
} catch (Exception $e) {
    $risposta["msg"] = "Errore generazione vista: " . $e->getMessage();
}

echo json_encode($risposta);
exit;
