<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Sicurezza: Solo Admin
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) {
    die("Accesso negato.");
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'delete') {
    // Recuperiamo la chiave composta della prenotazione
    $id_settore = intval($_POST['id_settore'] ?? 0);
    $nome_sala = $_POST['nome_sala'] ?? '';
    $data = $_POST['data'] ?? '';
    $ora = intval($_POST['ora'] ?? 0);

    if ($id_settore <= 0 || empty($nome_sala) || empty($data) || $ora <= 0) {
        header("Location: ../admin_prenotazioni.php?error=" . urlencode("Dati prenotazione non validi."));
        exit;
    }

    if (eliminaPrenotazioneAdmin($cid, $id_settore, $nome_sala, $data, $ora)) {
        header("Location: ../admin_prenotazioni.php?msg=" . urlencode("Prenotazione eliminata con successo."));
    } else {
        header("Location: ../admin_prenotazioni.php?error=" . urlencode("Errore durante l'eliminazione."));
    }
    exit;
}

header("Location: ../admin_prenotazioni.php");
exit;
?>