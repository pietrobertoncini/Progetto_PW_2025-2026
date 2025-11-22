<?php

function controllaUtente($cid, $email, $password)
{
    $sql = "SELECT id_utente, nome, ruolo, password_hash, is_responsabile 
            FROM UTENTE 
            WHERE email = ?";

    $stmt = $cid->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $utente = $result->fetch_assoc();
    $stmt->close();

    if ($utente && password_verify($password, $utente['password_hash'])) {
        // Login riuscito!
        // Per sicurezza, rimuoviamo l'hash dall'array prima di restituirlo, non serve più.
        unset($utente['password_hash']);
        return $utente;
    } else {
        // Login fallito (utente non trovato o password errata)
        return null;
    }
}

function inserisciUtente($cid, $nome, $cognome, $email, $password, $data_nascita, $ruolo, $id_settore) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO UTENTE 
                (nome, cognome, email, password_hash, data_nascita, ruolo, id_settore, is_responsabile) 
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, FALSE)";
                
    $stmt = $cid->prepare($sql);
    
    $stmt->bind_param("ssssssi", $nome, $cognome, $email, $password_hash, $data_nascita, $ruolo, $id_settore);
    
    $stmt->execute();
    $id_nuovo_utente = $cid->insert_id;
    $stmt->close();
    
    return $id_nuovo_utente;
}
