<?php

if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['is_responsabile'])) {

    $dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
    $id_settore = $dati_utente['id_settore'];

    $nome_sala = trim($_POST['nome_sala']);
    $capienza = (int)$_POST['capienza_max'];
    $dotazioni = isset($_POST['dotazioni']) ? $_POST['dotazioni'] : [];

    try {
        $cid->begin_transaction();

        salvaSalaConDotazioni($cid, $id_settore, $nome_sala, $capienza, $dotazioni);

        $cid->commit();
        header("Location: " . BASE_URL . "frontend/gestione_sale.php?msg=Sala creata con successo!");
    } catch (mysqli_sql_exception $e) {
        $cid->rollback();
        if ($e->getCode() == 1062) { 
            header("Location: " . BASE_URL . "frontend/nuova_sala.php?error=Esiste già una sala con questo nome nel tuo settore.");
        } else {
            header("Location: " . BASE_URL . "frontend/nuova_sala.php?error=Errore DB: " . $e->getMessage());
        }
    }
} else {
    header("Location: " . BASE_URL . "index.php");
}
