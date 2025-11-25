<?php
// backend/update_profile.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../common/setup.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_utente'])) {
    
    $id_utente = $_SESSION['id_utente'];
    $nome = trim($_POST['nome']);
    $cognome = trim($_POST['cognome']);
    $email = trim($_POST['email']);
    $data_nascita = $_POST['data_nascita'];

    try {
        // Query di aggiornamento
        $sql = "UPDATE UTENTE 
                SET nome = ?, cognome = ?, email = ?, data_nascita = ? 
                WHERE id_utente = ?";
        
        $stmt = $cid->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $cognome, $email, $data_nascita, $id_utente);
        
        if ($stmt->execute()) {
            // Aggiorniamo anche la sessione per riflettere subito le modifiche
            $_SESSION['nome'] = $nome;
            
            // Torniamo al profilo
            header("Location: ../profilo.php?success=1");
        } else {
            throw new Exception("Errore durante l'aggiornamento.");
        }

    } catch (mysqli_sql_exception $e) {
         // Gestione errore duplicate email
        if ($e->getCode() == 1062) {
            header("Location: ../modifica_profilo.php?error=Email già utilizzata da un altro utente.");
        } else {
            header("Location: ../modifica_profilo.php?error=Errore del sistema: " . $e->getMessage());
        }
    }
} else {
    header("Location: ../index.php");
}
?>