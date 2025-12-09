<?php

if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['is_responsabile'])) {
    
    $dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
    $id_settore = $dati_utente['id_settore'];
    $nome_sala = $_POST['nome_sala'];

    try {
        // La cancellazione della sala eliminerà anche le dotazioni collegate (CASCADE).
        // Se nel DB "PRENOTAZIONE" ha ON DELETE NO ACTION, l'eliminazione fallirà se ci sono prenotazioni.
        
        if (eliminaSalaResponsabile($cid, $id_settore, $nome_sala)) {
            header("Location: ../gestione_sale.php?msg=Sala eliminata definitivamente.");
        } else {
            throw new Exception("Impossibile eliminare la sala.");
        }

    } catch (mysqli_sql_exception $e) {
        // Codice 1451 = Cannot delete or update a parent row: a foreign key constraint fails
        if ($e->getCode() == 1451) {
             header("Location: ../modifica_sala.php?nome=".urlencode($nome_sala)."&error=Impossibile eliminare: ci sono prenotazioni attive per questa sala.");
        } else {
             header("Location: ../modifica_sala.php?nome=".urlencode($nome_sala)."&error=Errore DB: " . $e->getMessage());
        }
    }
} else {
    header("Location: ../index.php");
}
?>