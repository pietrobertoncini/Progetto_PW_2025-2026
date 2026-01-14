<?php

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Array di risposta base
$risposta = [
    "status" => "ko",
    "msg" => ""
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Recuperiamo i dati
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $utente_trovato = controllaUtente($cid, $email, $password);

        if ($utente_trovato) {

            $_SESSION['id_utente'] = $utente_trovato['id_utente'];
            $_SESSION['nome'] = $utente_trovato['nome'];
            $_SESSION['ruolo'] = $utente_trovato['ruolo'];
            $_SESSION['is_responsabile'] = (bool)$utente_trovato['is_responsabile'];
            $_SESSION['is_admin'] = (bool)$utente_trovato['is_admin'];
            
            $risposta["status"] = "ok";
            $risposta["msg"] = "Login effettuato con successo!";
        } else {
            $risposta["msg"] = "Email o password errati.";
        }

    } catch (Exception $e) {
        $risposta["msg"] = "Errore di sistema: " . $e->getMessage();
    }
} else {
    $risposta["msg"] = "Metodo non consentito.";
}

// Restituiamo il JSON
echo json_encode($risposta);
exit;
?>