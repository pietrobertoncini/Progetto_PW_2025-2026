<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../common/setup.php'; 
require_once '../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    try {
        $utente_trovato = controllaUtente($cid, $_POST["email"], $_POST["password"]);

        if ($utente_trovato) {
            $_SESSION['id_utente'] = $utente_trovato['id_utente'];
            $_SESSION['nome'] = $utente_trovato['nome'];
            $_SESSION['ruolo'] = $utente_trovato['ruolo'];
            $_SESSION['is_responsabile'] = (bool)$utente_trovato['is_responsabile'];
            $_SESSION['is_admin'] = (bool)$utente_trovato['is_admin'];
            
            header('Location: ../dashboard.php');
            exit;
        } else {
            header('Location: ../login.php?error=Email o password non validi.');
            exit;
        }

    } catch (mysqli_sql_exception $e) {
        header('Location: ../login.php?error=Errore del database: ' . $e->getMessage());
        exit;
    }
} else {
    header('Location: ../login.php');
    exit;
}
?>