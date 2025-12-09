<?php

if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['is_responsabile'])) {
    
    $dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
    $id_settore = $dati_utente['id_settore'];
    
    $old_nome = $_POST['old_nome_sala'];
    $new_nome = trim($_POST['nome_sala']);
    $capienza = (int)$_POST['capienza_max'];
    $dotazioni = isset($_POST['dotazioni']) ? $_POST['dotazioni'] : [];

    try {
        $cid->begin_transaction();

        // Usiamo la funzione unica che gestisce update sala + reset dotazioni + insert nuove dotazioni
        salvaSalaConDotazioni($cid, $id_settore, $new_nome, $capienza, $dotazioni, $old_nome);

        $cid->commit();
        header("Location: " . BASE_URL . "frontend/gestione_sale.php?msg=Sala aggiornata con successo!");

    } catch (Exception $e) {
        $cid->rollback();
        header("Location: " . BASE_URL . "frontend/modifica_sala.php?nome=".urlencode($old_nome)."&error=Errore: " . $e->getMessage());
    }
} else {
    header("Location: " . BASE_URL . "index.php");
}
?>