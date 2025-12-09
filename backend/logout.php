<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';

$_SESSION = array();

session_destroy();

header('Location: ' . BASE_URL . 'index.php');
exit;

?>