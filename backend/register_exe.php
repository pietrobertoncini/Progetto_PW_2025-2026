<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data_nascita = $_POST['data_nascita'] ?? '';

    // Controlla che la data sia valida (non futura)
    if (!empty($data_nascita) && strtotime($data_nascita) > time()) {
        header('Location: ' . BASE_URL . 'frontend/register.php?error=La data di nascita non può essere nel futuro.');
        exit;
    }

    // Gestione del caricamento dell'immagine del profilo e assegnazione al settore di competenza scelto
    $percorsoFotoDB = uploadFotoProfilo($_FILES['foto'] ?? null); // restituisce il percorso o null

    try {
        // Registrazione nel sistema e configurazione immediata della sessione per l'accesso automatico
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
