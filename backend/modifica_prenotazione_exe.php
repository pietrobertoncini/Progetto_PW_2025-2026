<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['is_responsabile'])) {

    // DATI VECCHI (IDENTIFICATIVI)
    $old_nome_sala = $_POST['old_nome_sala'];
    $old_data = $_POST['old_data'];
    $old_ora = (int)$_POST['old_ora'];
    $id_settore = (int)$_POST['id_settore'];

    // DATI NUOVI (DA SALVARE)
    $new_data = $_POST['new_data'];
    $new_ora = (int)$_POST['new_ora'];
    $new_durata = (int)$_POST['new_durata'];
    $new_attivita = trim($_POST['new_attivita']);

    $new_ora_fine = $new_ora + $new_durata;

    // Validazione dell'intervallo orario scelto
    if ($new_ora < 9 || $new_ora_fine > 23) {
        header("Location: " . BASE_URL . "frontend/modifica_prenotazione.php?error=Orario non valido (9-23)&sala=" . urlencode($old_nome_sala) . "&data=$old_data&ora=$old_ora");
        exit;
    }

    // Verifica della disponibilità della sala per evitare conflitti con altre attività già programmate
    if (checkSovrapposizioneModifica($cid, $id_settore, $old_nome_sala, $new_data, $new_ora, $new_durata, $old_data, $old_ora)) {
        header("Location: " . BASE_URL . "frontend/modifica_prenotazione.php?error=Conflitto! Sala già occupata nel nuovo orario.&sala=" . urlencode($old_nome_sala) . "&data=$old_data&ora=$old_ora");
        exit;
    }

    try {
        // Consolidamento delle modifiche nel sistema e gestione degli eventuali errori di salvataggio
        if (aggiornaPrenotazione($cid, $new_data, $new_ora, $new_durata, $new_attivita, $id_settore, $old_nome_sala, $old_data, $old_ora)) {
            header("Location: " . BASE_URL . "frontend/gestione_prenotazioni.php?msg=Prenotazione aggiornata con successo!");
        } else {
            throw new Exception("Errore generico durante l'aggiornamento.");
        }
    } catch (Exception $e) {
        header("Location: " . BASE_URL . "frontend/modifica_prenotazione.php?error=" . $e->getMessage() . "&sala=" . urlencode($old_nome_sala) . "&data=$old_data&ora=$old_ora");
    }
} else {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
