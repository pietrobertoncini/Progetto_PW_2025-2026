<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../common/setup.php';
require_once '../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {
        $id_nuovo_utente = inserisciUtente($cid, $_POST['nome'], $_POST['cognome'], 
                                                 $_POST['email'], $_POST['password'], $_POST['data_nascita'],
                                                 $_POST['ruolo'], (int)$_POST['id_settore']);

        $_SESSION['id_utente'] = $id_nuovo_utente;
        $_SESSION['nome'] = $_POST["nome"];
        $_SESSION['ruolo'] = $_POST["ruolo"];
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