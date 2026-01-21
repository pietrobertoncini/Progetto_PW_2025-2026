<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
// Cancellazione di tutti i dati memorizzati nella sessione corrente
$_SESSION = array();
// Distruzione fisica della sessione sul server per garantire l'uscita sicura
session_destroy();

header('Location: ' . BASE_URL . 'index.php');
exit;

?>