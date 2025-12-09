<?php

// definiamo la root
define('ROOT_PATH', dirname(__DIR__));

// otteniamo il percorso della root del server
$docRoot = $_SERVER['DOCUMENT_ROOT'];

$docRoot = str_replace('\\', '/', $docRoot);
$projectRoot = str_replace('\\', '/', ROOT_PATH);

// sottraiamo la docRoot dal percorso del progetto
$folder = str_replace($docRoot, '', $projectRoot);

// definiamo URL base
define('BASE_URL', $folder . '/');

$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "play_room_planner";

require_once __DIR__ . '/function.php';

// Creiamo la connessione usando la funzione definita in function.php
$cid = connessione($hostname, $username, $password, $dbname);

// Se la connessione fallisce, $cid sarà null o gestito dalla funzione
if ($cid && $cid->connect_error) {
    die("Errore di connessione al db $dbname: " . $cid->connect_error);
}
?>