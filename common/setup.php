<?php
// common/setup.php

$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "play_room_planner";

// Nota: require_once assicura che function.php sia caricato PRIMA di chiamare connessione()
require_once __DIR__ . '/function.php';

// Creiamo la connessione usando la funzione definita in function.php
$cid = connessione($hostname, $username, $password, $dbname);

// Se la connessione fallisce, $cid sarà null o gestito dalla funzione
if ($cid && $cid->connect_error) {
    die("Errore di connessione al db $dbname: " . $cid->connect_error);
}
?>