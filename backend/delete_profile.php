<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Verifica che l'utente sia loggato e che la richiesta sia POST 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_utente'])) {

    $id_utente = $_SESSION['id_utente'];

    try {
        // Tentativo di rimozione definitiva dell'account e pulizia della sessione di lavoro attiva
        if (eliminaMioProfilo($cid, $id_utente)) {
            // Distruggiamo la sessione (Logout)
            $_SESSION = array();
            session_destroy();

            // Reindirizziamo alla home con un messaggio
            header("Location: " . BASE_URL . "/index.php?msg=Account eliminato correttamente.");
            exit;
        } else {
            throw new Exception("Impossibile eliminare l'utente.");
        }
    } catch (mysqli_sql_exception $e) {
        // Gestione dei vincoli di integrità del database nel caso l'utente ricopra ruoli di responsabilità
        if ($e->getCode() == 1451) {
            header("Location: " . BASE_URL . "frontend/modifica_profilo.php?error=Non puoi eliminare l'account perché risulti Responsabile di un settore o hai dati collegati vitali. Contatta l'amministratore.");
        } else {
            header("Location: " . BASE_URL . "frontend/modifica_profilo.php?error=Errore del database: " . $e->getMessage());
        }
    }
} else {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
