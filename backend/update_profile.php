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

// recupero foto attuale
$queryOld = "SELECT foto FROM UTENTE WHERE id_utente = '$id_utente'";
$resOld = mysqli_query($cid, $queryOld);
$rowOld = mysqli_fetch_assoc($resOld);
$vecchiaFoto = $rowOld['foto'];

// logica FOTO
$percorsoFotoDB = uploadFotoProfilo($_FILES['foto'] ?? null);

// Decidiamo quale percorso salvare nel DB
if ($percorsoFotoDB != null) {
    // CASO 1: L'utente ha caricato una nuova foto
    $percorsoFinale = $percorsoFotoDB;
    rimuoviVecchiaFoto($vecchiaFoto); 
} else {
    // CASO 2: Nessuna nuova foto caricata
    $percorsoFinale = $vecchiaFoto;
}

try {
    modificaUtente($cid, $id_utente, $nome, $cognome, $email, $data_nascita, $percorsoFinale);
    
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