<?php

function controllaUtente($cid, $email, $password)
{
    $sql = "SELECT id_utente, nome, ruolo, password_hash, is_responsabile, is_admin  
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

function inserisciUtente($cid, $nome, $cognome, $email, $password, $data_nascita, $ruolo, $id_settore, $foto = null)
{
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO UTENTE 
                (nome, cognome, email, password_hash, data_nascita, ruolo, id_settore, is_responsabile, foto) 
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, FALSE, ?)";

    $stmt = $cid->prepare($sql);

    $stmt->bind_param("ssssssis", $nome, $cognome, $email, $password_hash, $data_nascita, $ruolo, $id_settore, $foto);

    $stmt->execute();
    $id_nuovo_utente = $cid->insert_id;
    $stmt->close();

    return $id_nuovo_utente;
}

function modificaUtente($cid, $id_utente, $nome, $cognome, $email, $data_nascita, $foto)
{
    if (!empty($foto)) {
        $sql = "UPDATE UTENTE
                SET nome = ?, cognome = ?, email = ?, data_nascita = ?, foto = ?
                WHERE id_utente = ?";

        $stmt = $cid->prepare($sql);
        $stmt->bind_param("sssssi", $nome, $cognome, $email, $data_nascita, $foto, $id_utente);
    } else {
        $sql = "UPDATE UTENTE
                SET nome = ?, cognome = ?, email = ?, data_nascita = ?
                WHERE id_utente = ?";

        $stmt = $cid->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $cognome, $email, $data_nascita, $id_utente);
    }
    $stmt->execute();
    $stmt->close();
}

function datiUtenteCompleti($cid, $id_utente)
{

    $sql = "SELECT U.*, S.nome AS nome_settore
            FROM utente U
            LEFT JOIN SETTORE S ON U.id_settore = S.id_settore
            WHERE U.id_utente = ?";

    $stmt = $cid->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $result = $stmt->get_result();

    $dati = $result->fetch_assoc();
    $stmt->close();

    return $dati;
}

// common/function.php

function getInvitiPendenti($cid, $id_utente)
{
    $sql = "SELECT I.*, S.nome_sala, P.attivita, U.nome as nome_org, U.cognome as cognome_org
            FROM INVITO I
            JOIN PRENOTAZIONE P ON I.id_settore = P.id_settore 
                 AND I.nome_sala = P.nome_sala 
                 AND I.data = P.data 
                 AND I.ora = P.ora
            JOIN SALA S ON P.id_settore = S.id_settore AND P.nome_sala = S.nome_sala
            JOIN UTENTE U ON P.id_organizzatore = U.id_utente
            WHERE I.id_utente = ? AND I.stato = 'invitato'
            ORDER BY I.data ASC, I.ora ASC";

    $stmt = $cid->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getImpegniFuturi($cid, $id_utente)
{
    // Seleziona gli inviti ACCETTATI con data odierna o futura
    $sql = "SELECT I.*, S.nome_sala, P.attivita, P.durata, U.nome as nome_org, U.cognome as cognome_org
            FROM INVITO I
            JOIN PRENOTAZIONE P ON I.id_settore = P.id_settore 
                 AND I.nome_sala = P.nome_sala 
                 AND I.data = P.data 
                 AND I.ora = P.ora
            JOIN SALA S ON P.id_settore = S.id_settore AND P.nome_sala = S.nome_sala
            JOIN UTENTE U ON P.id_organizzatore = U.id_utente
            WHERE I.id_utente = ? 
              AND I.stato = 'accettato'
              AND P.data >= CURDATE()
            ORDER BY P.data ASC, P.ora ASC";

    $stmt = $cid->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTotaleUtenti($cid) {
    $sql = "SELECT COUNT(*) as totale FROM UTENTE";
    
    if ($result = $cid->query($sql)) {
        $row = $result->fetch_assoc();
        return $row['totale'];
    }
    return 0; // In caso di errore o tabella vuota
}

function getTotaleSettori($cid) {
    $sql = "SELECT COUNT(*) as totale FROM SETTORE";

    if ($result = $cid->query($sql)) {
        $row = $result->fetch_assoc();
        return $row['totale'];
    }
    return 0;
}

function getPrenotazioniOggi($cid) {
    
    $sql = "SELECT COUNT(*) as totale FROM PRENOTAZIONE";
    if ($result = $cid->query($sql)) {
        $row = $result->fetch_assoc();
        return $row['totale'];
    }
    return 0;
}



?>