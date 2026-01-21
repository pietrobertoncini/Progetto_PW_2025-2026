<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// Sicurezza: Solo Admin
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) { die("Accesso negato."); }

$action = isset($_POST['action']) ? $_POST['action'] : '';
$id_utente_target = intval($_POST['id_utente'] ?? 0);

if ($id_utente_target <= 0) {
    header("Location: " . BASE_URL . "frontend/admin_utenti.php?error=" . urlencode("ID utente non valido."));
    exit;
}

switch ($action) {
    // Assegnazione del ruolo di responsabile a un utente per la gestione di un settore specifico
    case 'promote':
        $id_settore_dest = intval($_POST['id_settore_dest'] ?? 0);
        if ($id_settore_dest <= 0) {
             header("Location: " . BASE_URL . "frontend/admin_utenti.php?error=" . urlencode("Devi selezionare un settore per la promozione."));
             exit;
        }

        if (promuoviAResponsabile($cid, $id_utente_target, $id_settore_dest)) {
            header("Location: " . BASE_URL . "frontend/admin_utenti.php?msg=" . urlencode("Utente promosso a Responsabile con successo."));
        } else {
            header("Location: " . BASE_URL . "frontend/admin_utenti.php?error=" . urlencode("Errore durante la promozione. L'utente potrebbe essere già un Admin o un Responsabile."));
        }
        break;

    // Revoca dell'incarico di responsabilità riportando l'utente allo stato standard
    case 'demote':
        if (retrocediResponsabile($cid, $id_utente_target)) {
            header("Location: " . BASE_URL . "frontend/admin_utenti.php?msg=" . urlencode("Responsabile retrocesso a utente normale."));
        } else {
            header("Location: " . BASE_URL . "frontend/admin_utenti.php?error=" . urlencode("Errore durante la retrocessione."));
        }
        break;

    // Rimozione definitiva di un account dal sistema con blocco dell'autoeliminazione
    case 'delete':
        // Passiamo anche il nostro ID per evitare auto-eliminazione
        if (eliminaUtente($cid, $id_utente_target, $_SESSION['id_utente'])) {
            header("Location: " . BASE_URL . "frontend/admin_utenti.php?msg=" . urlencode("Utente eliminato con successo."));
        } else {
            header("Location: " . BASE_URL . "frontend/admin_utenti.php?error=" . urlencode("Impossibile eliminare l'utente. Potrebbe essere un Admin o avere dati collegati (es. prenotazioni organizzate)."));
        }
        break;

    default:
        header("Location: " . BASE_URL . "frontend/admin_utenti.php");
        exit;
}