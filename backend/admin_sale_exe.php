<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// SICUREZZA: SOLO ADMIN
if (!isset($_SESSION['is_admin']) || empty($_SESSION['is_admin'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    // Inserimento di una nuova sala definendone nome e capienza massima consentita
    case 'create':
        $nome = trim($_POST['nome_sala']);
        $capienza = (int)$_POST['capienza_max'];
        $id_settore = (int)$_POST['id_settore'];

        try {
            if (creaSala($cid, $id_settore, $nome, $capienza)) {
                header("Location: " . BASE_URL . "frontend/admin_sale.php?msg=" . urlencode("Sala creata con successo!"));
            } else {
                throw new Exception("Errore generico creazione.");
            }
        } catch (mysqli_sql_exception $e) {
            header("Location: " . BASE_URL . "frontend/admin_sale.php?error=" . urlencode("Errore DB: " . $e->getMessage()));
        }
        break;

    // Modifica dei dati identificativi della sala mantenendo la coerenza con le prenotazioni esistenti
    case 'update':
        // Dati Vecchi
        $old_nome = $_POST['old_nome_sala'];
        $old_id_settore = (int)$_POST['old_id_settore'];

        // Dati Nuovi
        $new_nome = trim($_POST['nome_sala']);
        $new_capienza = (int)$_POST['capienza_max'];
        $new_id_settore = (int)$_POST['id_settore'];

        try {
            if (aggiornaSala($cid, $old_id_settore, $old_nome, $new_id_settore, $new_nome, $new_capienza)) {
                header("Location: " . BASE_URL . "frontend/admin_sale.php?msg=" . urlencode("Sala aggiornata correttamente!"));
            } else {
                throw new Exception("Nessuna modifica effettuata o errore query.");
            }
        } catch (mysqli_sql_exception $e) {
            header("Location: " . BASE_URL . "frontend/admin_sale.php?error=" . urlencode("Errore aggiornamento: " . $e->getMessage()));
        }
        break;

    // Cancellazione sicura di una sala che fallisce automaticamente in presenza di impegni futuri
    case 'delete':
        $nome = $_POST['nome_sala'];
        $id_settore = (int)$_POST['id_settore'];

        try {
            if (eliminaSala($cid, $id_settore, $nome)) {
                header("Location: " . BASE_URL . "frontend/admin_sale.php?msg=" . urlencode("Sala eliminata."));
            } else {
                throw new Exception("Impossibile eliminare.");
            }
        } catch (mysqli_sql_exception $e) {
            // Gestione del vincolo di integrità se esistono prenotazioni collegate
            if ($e->getCode() == 1451) {
                header("Location: " . BASE_URL . "frontend/admin_sale.php?error=" . urlencode("Impossibile eliminare: ci sono prenotazioni associate a questa sala."));
            } else {
                header("Location: " . BASE_URL . "frontend/admin_sale.php?error=" . urlencode("Errore DB: " . $e->getMessage()));
            }
        }
        break;

    default:
        header("Location: " . BASE_URL . "frontend/admin_sale.php");
        exit;
}
