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

    // CASO 1: CREA
    case 'create':
        $tipo = trim($_POST['tipo'] ?? '');

        if (empty($tipo)) {
            header("Location: " . BASE_URL . "frontend/admin_dotazioni.php?error=" . urlencode("Il nome della dotazione è obbligatorio."));
            exit;
        }

        try {
            if (creaDotazione($cid, $tipo)) {
                header("Location: " . BASE_URL . "frontend/admin_dotazioni.php?msg=" . urlencode("Dotazione aggiunta con successo!"));
            } else {
                throw new Exception("Errore generico.");
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Duplicate entry
                header("Location: " . BASE_URL . "frontend/admin_dotazioni.php?error=" . urlencode("Esiste già una dotazione con questo nome."));
            } else {
                header("Location: " . BASE_URL . "frontend/admin_dotazioni.php?error=" . urlencode("Errore DB: " . $e->getMessage()));
            }
        }
        break;

    // CASO 2: AGGIORNA
    case 'update':
        $id_dotazione = intval($_POST['id_dotazione'] ?? 0);
        $tipo = trim($_POST['tipo'] ?? '');

        if ($id_dotazione <= 0 || empty($tipo)) {
            header("Location: " . BASE_URL . "frontend/admin_dotazioni.php?error=" . urlencode("Dati non validi."));
            exit;
        }

        try {
            if (aggiornaDotazione($cid, $id_dotazione, $tipo)) {
                header("Location: " . BASE_URL . "frontend/admin_dotazioni.php?msg=" . urlencode("Dotazione modificata con successo!"));
            }
        } catch (mysqli_sql_exception $e) {
             if ($e->getCode() == 1062) {
                header("Location: " . BASE_URL . "frontend/admin_dotazioni.php?error=" . urlencode("Esiste già una dotazione con questo nome."));
            } else {
                header("Location: " . BASE_URL . "frontend/admin_dotazioni.php?error=" . urlencode("Errore: " . $e->getMessage()));
            }
        }
        break;

    // CASO 3: ELIMINA
    case 'delete':
        $id_dotazione = intval($_POST['id_dotazione'] ?? 0);

        if (eliminaDotazione($cid, $id_dotazione)) {
            header("Location: " . BASE_URL . "frontend/admin_dotazioni.php?msg=" . urlencode("Dotazione eliminata."));
        } else {
            header("Location: " . BASE_URL . "frontend/admin_dotazioni.php?error=" . urlencode("Impossibile eliminare: questa dotazione è presente in una o più sale."));
        }
        break;

    default:
        header("Location: " . BASE_URL . "frontend/admin_dotazioni.php");
        exit;
}