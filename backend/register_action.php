<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../common/setup.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $password_inserita = $_POST['password'];
    $data_nascita = $_POST['data_nascita'];
    $ruolo = $_POST['ruolo'];
    $id_settore = (int)$_POST['id_settore'];

    $password_hash = password_hash($password_inserita, PASSWORD_DEFAULT);

    try {
        // 1. Prepara la query
        $sql = "INSERT INTO UTENTE 
                    (nome, cognome, email, password_hash, data_nascita, ruolo, id_settore, is_responsabile) 
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, FALSE)";
        
        $stmt = $cid->prepare($sql);
        
        // 2. Collega i parametri (bind)
        // "ssssssi" = String, String, String, String, String, String, Integer
        $stmt->bind_param("ssssssi", $nome, $cognome, $email, $password_hash, $data_nascita, $ruolo, $id_settore);
        
        // 3. Esegui
        $stmt->execute();
        
        // 4. Ottieni l'ID (si usa insert_id in MySQLi)
        $id_nuovo_utente = $db->insert_id; // <--- Diversa da lastInsertId()

        // 5. Fai il login automatico
        $_SESSION['id_utente'] = $id_nuovo_utente;
        $_SESSION['nome'] = $nome;
        $_SESSION['ruolo'] = $ruolo;
        $_SESSION['is_responsabile'] = FALSE; 

        header('Location: ../dashboard.php');
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