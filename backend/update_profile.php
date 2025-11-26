<?php
// backend/update_profile.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['id_utente'])) {
    header("Location: ../login.php");
    exit;
}

// Inclusione di setup e funzioni
require_once '../common/setup.php';
require_once '../common/function.php';

// Recupero dati dal POST e dalla Sessione
$id_utente = $_SESSION['id_utente'];
$nome = trim($_POST['nome']);
$cognome = trim($_POST['cognome']);
$email = trim($_POST['email']);
$data_nascita = $_POST['data_nascita'];

// --- INIZIO LOGICA UPLOAD FOTO (SEMPLIFICATA) ---
$percorsoFotoDB = null; // Di default è null (nessuna nuova foto)

// Controlliamo se è stato inviato un file e se non ci sono errori
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    
    $cartellaDestinazione = "../uploads/propic/";
    
    // Usiamo un trucco semplice per rendere il nome del file unico:
    // aggiungiamo il timestamp (time()) davanti al nome originale.
    // Es: "1715698524_miafoto.jpg"
    $nomeFileUnivoco = time() . "_" . basename($_FILES["foto"]["name"]);
    $targetFilePath = $cartellaDestinazione . $nomeFileUnivoco;

    // Spostiamo il file dalla cartella temporanea a quella definitiva
    if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath)) {
        // Se lo spostamento va a buon fine, prepariamo il percorso da salvare nel DB.
        // Deve essere relativo alla root del sito (senza i ../ iniziali)
        $percorsoFotoDB = "uploads/propic/" . $nomeFileUnivoco;
    }
}
// --- FINE LOGICA UPLOAD ---

try {
    // --- CHIAMATA ALLA FUNZIONE NEL MODEL ---
    // Passiamo tutti i dati, incluso il percorso della foto (che può essere null)
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