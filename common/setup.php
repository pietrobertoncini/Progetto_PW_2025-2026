<?php

$hostname = "localhost";
$username = "root";
$password = "";
$dbname = "play_room_planner";

function connessione($hostname, $username, $password, $dbname) {
    try {
        $cid = new mysqli($hostname, $username, $password, $dbname);
    } catch (Exception $e) {
        $cid = null;
    }

    if ($cid && $cid->connect_error) {
        echo ("Errore di connessione al db $dbname: " . $cid->connect_error);
        $cid = null;
    }

    return $cid;
}

$cid = connessione($hostname,$username, $password, $dbname);

?>