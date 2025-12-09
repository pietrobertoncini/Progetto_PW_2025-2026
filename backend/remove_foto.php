<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Sicurezza
if (!isset($_SESSION['id_utente'])) {
    header("Location: " . BASE_URL . "frontend/login.php");
    exit;
}

$id_utente = $_SESSION['id_utente'];

// Cerco la foto attuale nel DB
$sqlSelect = "SELECT foto FROM UTENTE WHERE id_utente = ?";
$stmt = $cid->prepare($sqlSelect);
$stmt->bind_param("i", $id_utente);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Se la foto esiste, la cancello
if ($row && !empty($row['foto'])) {

    rimuoviVecchiaFoto($row['foto']);
    // Aggiorno il DB rimuovendo il percorso
    $sqlUpdate = "UPDATE utente SET foto = NULL WHERE id_utente = ?";
    $stmtUpdate = $cid->prepare($sqlUpdate);
    $stmtUpdate->bind_param("i", $id_utente);

    if ($stmtUpdate->execute()) {
        // Successo
        header("Location: " . BASE_URL . "frontend/modifica_profilo.php?msg=Foto rimossa correttamente");
    } else {
        // Errore SQL
        header("Location: " . BASE_URL . "frontend/modifica_profilo.php?error=Errore durante la rimozione");
    }

    $stmtUpdate->close();
} else {
    header("Location: " . BASE_URL . "frontend/modifica_profilo.php?msg=Foto eliminata con successo");
}
exit;
