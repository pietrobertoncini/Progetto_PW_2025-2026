<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['id_utente'])) {
    header("Location: ../login.php");
    exit;
}

require_once '../common/setup.php';
require_once '../common/function.php';

// Recupero dati dal POST e dalla Sessione
$id_utente = $_SESSION['id_utente'];
$nome = trim($_POST['nome']);
$cognome = trim($_POST['cognome']);
$email = trim($_POST['email']);
$data_nascita = $_POST['data_nascita'];

// logica FOTO
$percorsoFotoDB = uploadFotoProfilo($_FILES['foto'] ?? null);

try {
    modificaUtente($cid, $id_utente, $nome, $cognome, $email, $data_nascita, $percorsoFotoDB);
    
    // Aggiorniamo la sessione col nuovo nome
    $_SESSION['nome'] = $nome;
            
    // Torniamo al profilo con un messaggio di successo
    header("Location: ../profilo.php?msg=Profilo aggiornato con successo!");
    exit;

} catch (mysqli_sql_exception $e) {
     // Gestione errori specifici (es. email duplicata)
    if ($e->getCode() == 1062) {
        header("Location: ../modifica_profilo.php?error=Email già utilizzata da un altro utente.");
    } else {
        // Errore generico del database
        header("Location: ../modifica_profilo.php?error=Errore del sistema: " . $e->getMessage());
    }
    exit;
} catch (Exception $e) {
    // Altri errori generici
    header("Location: ../modifica_profilo.php?error=Si è verificato un errore imprevisto.");
    exit;
}
?>