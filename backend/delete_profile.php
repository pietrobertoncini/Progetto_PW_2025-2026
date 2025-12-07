<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../common/setup.php';
require_once '../common/function.php';

// Verifica che l'utente sia loggato e che la richiesta sia POST (per sicurezza)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_utente'])) {
    
    $id_utente = $_SESSION['id_utente'];

    try {
        
        if (eliminaMioProfilo($cid, $id_utente)) {        
            // Distruggiamo la sessione (Logout)
            $_SESSION = array();
            session_destroy();
            
            // Reindirizziamo alla home con un messaggio
            header("Location: ../index.php?msg=Account eliminato correttamente.");
            exit;
        } else {
            throw new Exception("Impossibile eliminare l'utente.");
        }

    } catch (mysqli_sql_exception $e) {
        // Se ci sono vincoli di integrità (es. l'utente è un responsabile con settori collegati)
        // potremmo non voler permettere la cancellazione diretta.
        if ($e->getCode() == 1451) { // Error Code: Cannot delete or update a parent row
            header("Location: ../modifica_profilo.php?error=Non puoi eliminare l'account perché risulti Responsabile di un settore o hai dati collegati vitali. Contatta l'amministratore.");
        } else {
            header("Location: ../modifica_profilo.php?error=Errore del database: " . $e->getMessage());
        }
    }
} else {
    header("Location: ../index.php");
    exit;
}
?>