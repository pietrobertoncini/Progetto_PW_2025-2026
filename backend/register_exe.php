<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../common/setup.php';
require_once '../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- INIZIO LOGICA UPLOAD SEMPLIFICATA ---
    $percorsoFotoDB = null; // Di base è null (nessuna foto)

    // Controlliamo se è stato inviato un file e se non ci sono errori (error == 0)
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {

        // Dove salvare fisicamente il file (rispetto a questo file PHP)
        $cartellaDestinazione = "../uploads/propic/";

        // Nome originale del file caricato dall'utente
        $nomeFileOriginale = basename($_FILES["foto"]["name"]);

        // PERCORSO COMPLETO dove spostare il file
        // Es: ../uploads/propic/miabella.jpg
        // NOTA: Per un esame base, usiamo il nome originale. In produzione sarebbe insicuro (sovrascritture).
        $targetFilePath = $cartellaDestinazione . $nomeFileOriginale;

        // Spostiamo il file dalla cartella temporanea alla nostra cartella
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $targetFilePath)) {
            // Se lo spostamento va a buon fine, prepariamo il percorso da salvare nel DB.
            // Nel DB salviamo il percorso relativo alla ROOT del sito (senza i ../ iniziale)
            $percorsoFotoDB = "uploads/propic/" . $nomeFileOriginale;
        }
         // Se move_uploaded_file fallisce, $percorsoFotoDB resta null e l'utente si registra senza foto.
    }
    // --- FINE LOGICA UPLOAD ---
    
    try {
        $id_nuovo_utente = inserisciUtente($cid, $_POST['nome'], $_POST['cognome'], 
                                                 $_POST['email'], $_POST['password'], $_POST['data_nascita'],
                                                 $_POST['ruolo'], (int)$_POST['id_settore'], $percorsoFotoDB);

        $_SESSION['id_utente'] = $id_nuovo_utente;
        $_SESSION['nome'] = $_POST["nome"];
        $_SESSION['ruolo'] = $_POST["ruolo"];
        $_SESSION['is_responsabile'] = FALSE; 

        header('Location: ../index.php');
        exit;

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // 1062 è il codice MySQLi per "Duplicate entry"
            header('Location: ../register.php?error=Email gia in uso.');
        } else {
            header('Location: ../register.php?error=Errore del database: ' . $e->getMessage());
        }
        exit;
    }
} else {
    header('Location: ../register.php');
    exit;
}
?>