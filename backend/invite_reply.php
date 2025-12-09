<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_utente'])) {

    $id_utente = $_SESSION['id_utente'];
    $id_settore = $_POST['id_settore'];
    $nome_sala = $_POST['nome_sala'];
    $data = $_POST['data'];
    $ora = $_POST['ora'];

    // Accettato o Rifiutato
    $risposta = $_POST['risposta'];

    // Motivazione (opzionale)
    $motivazione = !empty($_POST['motivazione']) ? $_POST['motivazione'] : null;

    try {
        // Se accetta, controlliamo che non abbia sovrapposizioni (Query 4)
        if ($risposta === 'accettato') {

            // Dobbiamo recuperare la DURATA della prenotazione target per fare il controllo
            $info_pren = getPrenotazioneSingola($cid, $id_settore, $nome_sala, $data, $ora);
            if (!$info_pren) {
                throw new Exception("Prenotazione non trovata.");
            }
            $durata_target = $info_pren['durata'];

            if (checkSovrapposizioneUtente($cid, $id_utente, $data, $ora, $durata_target, $id_settore, $nome_sala)) {
                header("Location: " . BASE_URL . "frontend/inviti.php?error=Impossibile accettare: hai già un altro impegno accettato in questo orario.");
                exit;
            }
        }

        // Se passiamo i controlli o se rifiutiamo, eseguiamo l'update
        if (rispondiInvito($cid, $id_utente, $id_settore, $nome_sala, $data, $ora, $risposta, $motivazione)) {
            if ($risposta === 'accettato') {
                header("Location: " . BASE_URL . "frontend/impegni.php?msg=Invito accettato con successo!");
            } else {
                header("Location: " . BASE_URL . "frontend/inviti.php?msg=Invito rifiutato.");
            }
        } else {
            header("Location: " . BASE_URL . "frontend/inviti.php?error=Errore durante l'aggiornamento.");
        }
        exit;

    } catch (Exception $e) {
        header("Location: " . BASE_URL . "frontend/inviti.php?error=" . $e->getMessage());
        exit;
    }
} else {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
