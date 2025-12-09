<?php

// definiamo la root
define('ROOT_PATH', dirname(__DIR__));

// definiamo il path
define('BASE_URL', '/my_dir/project/');

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