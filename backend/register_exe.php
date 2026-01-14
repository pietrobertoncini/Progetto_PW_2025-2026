<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // logica UPLOAD FOTO
    $percorsoFotoDB = uploadFotoProfilo($_FILES['foto'] ?? null); // restituisce il percorso o null

    try {
        $id_nuovo_utente = inserisciUtente(
            $cid, 
            $_POST['nome'] ?? '', 
            $_POST['cognome']?? '', 
            $_POST['email'] ?? '', 
            $_POST['password']?? '', 
            $_POST['data_nascita'] ?? '',
            $_POST['ruolo'] ?? 'allievo', 
            (int)($_POST['id_settore'] ?? 0),
            $percorsoFotoDB
        );

        // Login automatico dopo registrazione
        $_SESSION['id_utente'] = $id_nuovo_utente;
        $_SESSION['nome'] = $_POST["nome"];
        $_SESSION['ruolo'] = $_POST["ruolo"];
        $_SESSION['is_responsabile'] = FALSE;
        $_SESSION['is_admin'] = FALSE; 

        header('Location: ' . BASE_URL . 'index.php');
        exit;

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            header('Location: ' . BASE_URL . 'frontend/register.php?error=Email gia in uso.');
        } else {
            header('Location: ' . BASE_URL . 'frontend/register.php?error=Errore del database: ' . $e->getMessage());
        }
        exit;
    }
} else {
    header('Location: ' . BASE_URL . 'frontend/register.php');
    exit;
}
?>