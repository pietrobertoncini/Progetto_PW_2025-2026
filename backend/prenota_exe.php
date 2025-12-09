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
    $ora_inizio = (int)$_POST['ora'];
    $durata = (int)$_POST['durata'];
    $attivita = trim($_POST['attivita']);
    $invitati = isset($_POST['invitati']) ? $_POST['invitati'] : [];

    $ora_fine = $ora_inizio + $durata;

    // Recuperiamo i dati della sala per sapere la capienza max
    $sala_info = getSalaById($cid, $id_settore, $nome_sala);

    // Partecipanti = Organizzatore (1) + Invitati
    $num_partecipanti = 1 + count($invitati);

    if ($sala_info && $num_partecipanti > $sala_info['capienza_max']) {
        header("Location: " . BASE_URL . "frontend/prenota.php?error=Errore: Numero partecipanti supera la capienza della sala.&sala=" . urlencode($nome_sala) . "&week=" . $data);
        exit;
    }

    // controllo sovrapposizioni
    if (checkSovrapposizioneNuova($cid, $id_settore, $nome_sala, $data, $ora_inizio, $durata)) {
        header("Location: " . BASE_URL . "frontend/prenota.php?error=Sala già occupata in orario sovrapposto.&sala=" . urlencode($nome_sala) . "&week=" . $data);
        exit;
    }

    // INSERIMENTO
    try {
        $cid->begin_transaction();
        
        // Funzione unica per prenotazione + inviti
        creaPrenotazioneConInviti($cid, $id_settore, $nome_sala, $data, $ora_inizio, $durata, $attivita, $id_utente, $invitati);

        $cid->commit();
        header("Location: " . BASE_URL . "frontend/gestione_prenotazioni.php?msg=Prenotazione confermata!");
        exit;

    } catch (Exception $e) {
        $cid->rollback();
        header("Location: " . BASE_URL . "frontend/prenota.php?error=Errore: " . $e->getMessage());
        exit;
    }

} else {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>