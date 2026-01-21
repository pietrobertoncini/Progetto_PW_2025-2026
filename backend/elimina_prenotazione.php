<?php

if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Controllo dei permessi di accesso per assicurare che solo un responsabile possa cancellare eventi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['is_responsabile'])) {

    $id_settore = (int)$_POST['id_settore'];
    $nome_sala = $_POST['nome_sala'];
    $data = $_POST['data'];
    $ora = (int)$_POST['ora'];

    try {
        // Procedura di eliminazione della prenotazione e ritorno alla gestione operativa del settore
        if (eliminaPrenotazioneResponsabile($cid, $id_settore, $nome_sala, $data, $ora)) {
            header("Location: " . BASE_URL . "frontend/gestione_prenotazioni.php?msg=Prenotazione eliminata definitivamente.");
        } else {
            throw new Exception("Impossibile eliminare la prenotazione.");
        }
    } catch (Exception $e) {
        // Gestione dell'errore per riportare l'utente alla pagina di modifica con le informazioni necessarie
        header("Location: " . BASE_URL . "frontend/modifica_prenotazione.php?error=" . $e->getMessage() . "&sala=" . urlencode($nome_sala) . "&data=$data&ora=$ora");
    }
} else {
    header("Location: " . BASE_URL . "index.php");
}
