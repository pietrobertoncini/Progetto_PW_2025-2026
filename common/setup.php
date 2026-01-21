<?php

// Individuazione della cartella principale del progetto per gestire i percorsi relativi
define('ROOT_PATH', dirname(__DIR__));

// Otteniamo il percorso della root del server per la corretta gestione degli url
$docRoot = $_SERVER['DOCUMENT_ROOT'];

// Uniforma i separatori di percorso per garantire la compatibilità tra sistemi operativi diversi
$docRoot = str_replace('\\', '/', $docRoot);
$projectRoot = str_replace('\\', '/', ROOT_PATH);

// Estrae la sottocartella del progetto rispetto alla cartella pubblica del server
$folder = str_replace($docRoot, '', $projectRoot);

// Definizione della costante URL da utilizzare per tutti i collegamenti del sito
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