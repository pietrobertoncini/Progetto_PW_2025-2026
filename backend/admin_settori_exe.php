<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Sicurezza: Solo Admin 
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) {
    die("Accesso negato.");
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($action) {
    // Creazione di un nuovo settore
    case 'create':
        // recupera dati dal form e pulisci
        $nome = trim($_POST['nome'] ?? '');
        $tipo = $_POST['tipo'] ?? '';

        if (creaSettore($cid, $nome, $tipo)) {
            header("Location: " . BASE_URL . "frontend/admin_settori.php?msg=" . urlencode("Settore creato con successo!"));
        } else {
            header("Location: " . BASE_URL . "frontend/admin_settori.php?error=" . urlencode("Errore durante la creazione. Il nome potrebbe essere già in uso."));
        }
        break;

    // Aggiornamento delle informazioni descrittive del settore
    case 'update':
        $id_settore = intval($_POST['id_settore'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $tipo = $_POST['tipo'] ?? '';

        if ($id_settore <= 0 || empty($nome) || empty($tipo)) {
            header("Location: " . BASE_URL . "frontend/admin_settori.php?error=" . urlencode("Dati non validi per l'aggiornamento."));
            exit;
        }

        if (aggiornaSettore($cid, $id_settore, $nome, $tipo)) {
            header("Location: " . BASE_URL . "frontend/admin_settori.php?msg=" . urlencode("Settore aggiornato con successo!"));
        } else {
            header("Location: " . BASE_URL . "frontend/admin_settori.php?error=" . urlencode("Errore durante l'aggiornamento."));
        }
        break;

    // Eliminazione di un settore possibile solo se privo di iscritti o sale collegate
    case 'delete':
        $id_settore = intval($_POST['id_settore'] ?? 0);

        if ($id_settore <= 0) {
            header("Location: " . BASE_URL . "frontend/admin_settori.php?error=" . urlencode("ID settore non valido."));
            exit;
        }

        if (eliminaSettore($cid, $id_settore)) {
            header("Location: " . BASE_URL . "frontend/admin_settori.php?msg=" . urlencode("Settore eliminato con successo!"));
        } else {
            header("Location: " . BASE_URL . "frontend/admin_settori.php?error=" . urlencode("Impossibile eliminare: ci sono utenti o sale associate a questo settore."));
        }
        break;

    default:

        header("Location: " . BASE_URL . "frontend/admin_settori.php");
        exit;
}
