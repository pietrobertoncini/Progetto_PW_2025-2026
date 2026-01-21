<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        // Controllo della validità delle credenziali tramite la funzione di sistema
        $utente_trovato = controllaUtente($cid, $email, $password);

        if ($utente_trovato) {
            // Configurazione della sessione utente con le informazioni sui permessi di accesso
            $_SESSION['id_utente'] = $utente_trovato['id_utente'];
            $_SESSION['nome'] = $utente_trovato['nome'];
            $_SESSION['ruolo'] = $utente_trovato['ruolo'];
            $_SESSION['is_responsabile'] = (bool)$utente_trovato['is_responsabile'];
            $_SESSION['is_admin'] = (bool)$utente_trovato['is_admin'];
            // Accesso consentito e ritorno alla pagina iniziale del portale
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        } else {
            header('Location: ' . BASE_URL . 'frontend/login.php?error=Email o password non validi.');
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        header('Location: ' . BASE_URL . 'frontend/login.php?error=Errore del database: ' . $e->getMessage());
        exit;
    }
} else {
    header('Location: ' . BASE_URL . 'frontend/login.php');
    exit;
}
