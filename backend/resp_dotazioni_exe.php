<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// SICUREZZA
if (!isset($_SESSION['is_responsabile']) || $_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Recupero dati
$dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
$id_settore = $dati_utente['id_settore'];
$nome_sala = $_POST['nome_sala'];
$nuove_dotazioni = isset($_POST['dotazioni']) ? $_POST['dotazioni'] : [];

if (aggiornaDotazioniSala($cid, $id_settore, $nome_sala, $nuove_dotazioni)) {
    header("Location: " . BASE_URL . "frontend/resp_dotazioni.php?msg=" . urlencode("Dotazioni aggiornate con successo!"));
} else {
    header("Location: " . BASE_URL . "frontend/resp_edit_dotazioni.php?sala=" . urlencode($nome_sala) . "&error=Errore durante il salvataggio.");
}
exit;
?>