<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../common/setup.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password_inserita = $_POST['password'];

    try {
        // 1. Prepara la query
        $sql = "SELECT id_utente, nome, ruolo, password_hash, is_responsabile 
                FROM UTENTE 
                WHERE email = ?";
        $stmt = $cid->prepare($sql);
        
        // 2. Collega i parametri (bind)
        // "s" significa che la variabile $email è una Stringa
        $stmt->bind_param("s", $email);
        
        // 3. Esegui
        $stmt->execute();
        
        // 4. Ottieni il risultato
        $result = $stmt->get_result();
        $utente = $result->fetch_assoc(); // <--- fetch_assoc() invece di fetch()

        if ($utente && password_verify($password_inserita, $utente['password_hash'])) {
            
            $_SESSION['id_utente'] = $utente['id_utente'];
            $_SESSION['nome'] = $utente['nome'];
            $_SESSION['ruolo'] = $utente['ruolo'];
            $_SESSION['is_responsabile'] = (bool)$utente['is_responsabile'];
            
            header('Location: ../dashboard.php');
            exit;

        } else {
            header('Location: ../login.php?error=Email o password non validi.');
            exit;
        }

    } catch (mysqli_sql_exception $e) {
        header('Location: ../login.php?error=Errore del database: ' . $e->getMessage());
        exit;
    }
} else {
    header('Location: ../login.php');
    exit;
}
?>