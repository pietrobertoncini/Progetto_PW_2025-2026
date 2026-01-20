<?php

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

$risposta = ["status" => "ko", "msg" => "Errore sconosciuto"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data_nascita = $_POST['data_nascita'] ?? '';
    // Controlla che la data sia valida
    if (!empty($_POST['data_nascita']) && strtotime($_POST['data_nascita']) > time()) {
        $risposta["msg"] = "La data di nascita non può essere nel futuro.";
        echo json_encode($risposta);
        exit;
    }
    // Gestione upload foto
    $percorsoFotoDB = uploadFotoProfilo($_FILES['foto'] ?? null);

    try {
        //Inserimento utente nel DB
        $id_nuovo_utente = inserisciUtente(
            $cid,
            $_POST['nome'] ?? '',
            $_POST['cognome'] ?? '',
            $_POST['email'] ?? '',
            $_POST['password'] ?? '',
            $data_nascita,
            $_POST['ruolo'] ?? 'allievo',
            (int)($_POST['id_settore'] ?? 0),
            $percorsoFotoDB
        );

        if ($id_nuovo_utente) {
            // Login automatico dopo registrazione
            $_SESSION['id_utente'] = $id_nuovo_utente;
            $_SESSION['nome'] = $_POST["nome"] ?? 'Utente';
            $_SESSION['ruolo'] = $_POST["ruolo"] ?? 'allievo';
            $_SESSION['is_responsabile'] = FALSE;
            $_SESSION['is_admin'] = FALSE;

            $risposta["status"] = "ok";
            $risposta["msg"] = "Registrazione completata con successo! Reindirizzamento...";
        } else {
            $risposta["msg"] = "Errore durante l'inserimento. Riprova.";
        }
    } catch (mysqli_sql_exception $e) {
        // Gestione errore duplicato
        if ($e->getCode() == 1062) {
            $risposta["msg"] = "Questa email è già registrata.";
        } else {
            $risposta["msg"] = "Errore database: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $risposta["msg"] = "Errore generico: " . $e->getMessage();
    }
} else {
    $risposta["msg"] = "Richiesta non valida.";
}

echo json_encode($risposta);
exit;
