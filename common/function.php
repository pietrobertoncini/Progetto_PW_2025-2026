<?php
// common/function.php

/* -------------------------------------------------------------------------- */
/* 1. CONNESSIONE E AUTENTICAZIONE                                            */
/* -------------------------------------------------------------------------- */

function connessione($hostname, $username, $password, $dbname) {
    try {
        $cid = new mysqli($hostname, $username, $password, $dbname);
        return $cid;
    } catch (Exception $e) {
        return null;
    }
}

function controllaUtente($cid, $email, $password) {
    $sql = "SELECT id_utente, nome, ruolo, password_hash, is_responsabile, is_admin  
            FROM UTENTE WHERE email = ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $utente = $result->fetch_assoc();
    $stmt->close();

    if ($utente && password_verify($password, $utente['password_hash'])) {
        unset($utente['password_hash']);
        return $utente;
    }
    return null;
}

function inserisciUtente($cid, $nome, $cognome, $email, $password, $data_nascita, $ruolo, $id_settore, $foto = null) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO UTENTE (nome, cognome, email, password_hash, data_nascita, ruolo, id_settore, is_responsabile, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, FALSE, ?)";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("ssssssis", $nome, $cognome, $email, $password_hash, $data_nascita, $ruolo, $id_settore, $foto);
    $stmt->execute();
    $id = $cid->insert_id;
    $stmt->close();
    return $id;
}

function modificaUtente($cid, $id_utente, $nome, $cognome, $email, $data_nascita, $foto) {
    if (!empty($foto)) {
        $sql = "UPDATE UTENTE SET nome = ?, cognome = ?, email = ?, data_nascita = ?, foto = ? WHERE id_utente = ?";
        $stmt = $cid->prepare($sql);
        $stmt->bind_param("sssssi", $nome, $cognome, $email, $data_nascita, $foto, $id_utente);
    } else {
        $sql = "UPDATE UTENTE SET nome = ?, cognome = ?, email = ?, data_nascita = ? WHERE id_utente = ?";
        $stmt = $cid->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $cognome, $email, $data_nascita, $id_utente);
    }
    $stmt->execute();
    $stmt->close();
}

function datiUtenteCompleti($cid, $id_utente) {
    $sql = "SELECT U.*, S.nome AS nome_settore
            FROM UTENTE U
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

/* -------------------------------------------------------------------------- */
/* 2. NAVBAR E DASHBOARD (Inviti/Impegni)                                     */
/* -------------------------------------------------------------------------- */

function getInvitiPendenti($cid, $id_utente) {
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
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getImpegniFuturi($cid, $id_utente) {
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
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/* -------------------------------------------------------------------------- */
/* 3. FUNZIONI PER ADMIN                                                      */
/* -------------------------------------------------------------------------- */

// Statistiche
function getTotaleUtenti($cid) {
    $res = $cid->query("SELECT COUNT(*) as totale FROM UTENTE");
    return $res ? $res->fetch_assoc()['totale'] : 0;
}
function getTotaleSettori($cid) {
    $res = $cid->query("SELECT COUNT(*) as totale FROM SETTORE");
    return $res ? $res->fetch_assoc()['totale'] : 0;
}
function getPrenotazioni($cid) {
    $res = $cid->query("SELECT COUNT(*) as totale FROM PRENOTAZIONE");
    return $res ? $res->fetch_assoc()['totale'] : 0;
}

// Settori
function getAllSettoriAdmin($cid) {
    $sql = "SELECT S.*, U.nome AS nome_resp, U.cognome AS cognome_resp 
            FROM SETTORE S
            LEFT JOIN UTENTE U ON S.id_responsabile = U.id_utente
            ORDER BY S.nome ASC";
    return $cid->query($sql)->fetch_all(MYSQLI_ASSOC);
}
function creaSettore($cid, $nome, $tipo) {
    $stmt = $cid->prepare("INSERT INTO SETTORE (nome, tipo, num_iscritti) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $nome, $tipo);
    return $stmt->execute();
}
function aggiornaSettore($cid, $id, $nome, $tipo) {
    $stmt = $cid->prepare("UPDATE SETTORE SET nome = ?, tipo = ? WHERE id_settore = ?");
    $stmt->bind_param("ssi", $nome, $tipo, $id);
    return $stmt->execute();
}
function eliminaSettore($cid, $id) {
    $stmt = $cid->prepare("DELETE FROM SETTORE WHERE id_settore = ?");
    $stmt->bind_param("i", $id);
    try { return $stmt->execute(); } catch (Exception $e) { return false; }
}

// Utenti
function getAllUtentiAdmin($cid) {
    $sql = "SELECT U.*, S.nome AS nome_settore FROM UTENTE U LEFT JOIN SETTORE S ON U.id_settore = S.id_settore ORDER BY U.is_admin DESC, U.is_responsabile DESC, U.cognome ASC";
    return $cid->query($sql)->fetch_all(MYSQLI_ASSOC);
}
function promuoviAResponsabile($cid, $id_utente, $id_settore) {
    $cid->begin_transaction();
    try {
        $cid->query("UPDATE SETTORE SET id_responsabile = NULL WHERE id_settore = $id_settore"); 
        $stmt = $cid->prepare("UPDATE UTENTE SET is_responsabile = 1, id_settore = ? WHERE id_utente = ? AND is_admin = 0");
        $stmt->bind_param("ii", $id_settore, $id_utente);
        $stmt->execute();
        if ($stmt->affected_rows === 0) throw new Exception();
        $stmt = $cid->prepare("UPDATE SETTORE SET id_responsabile = ? WHERE id_settore = ?");
        $stmt->bind_param("ii", $id_utente, $id_settore);
        $stmt->execute();
        $cid->commit();
        return true;
    } catch (Exception $e) { $cid->rollback(); return false; }
}
function retrocediResponsabile($cid, $id_utente) {
    $cid->begin_transaction();
    try {
        $stmt = $cid->prepare("UPDATE UTENTE SET is_responsabile = 0 WHERE id_utente = ?");
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $stmt = $cid->prepare("UPDATE SETTORE SET id_responsabile = NULL WHERE id_responsabile = ?");
        $stmt->bind_param("i", $id_utente);
        $stmt->execute();
        $cid->commit();
        return true;
    } catch (Exception $e) { $cid->rollback(); return false; }
}
function eliminaUtente($cid, $id_target, $id_self) {
    if ($id_target == $id_self) return false;
    $stmt = $cid->prepare("DELETE FROM UTENTE WHERE id_utente = ?");
    $stmt->bind_param("i", $id_target);
    try { return $stmt->execute(); } catch (Exception $e) { return false; }
}

// Prenotazioni Globali
function getAllPrenotazioniAdmin($cid) {
    $sql = "SELECT P.*, S.nome_sala, SETT.nome as nome_settore, U.nome AS nome_org, U.cognome as cognome_org
            FROM PRENOTAZIONE P
            JOIN SALA S ON P.id_settore = S.id_settore AND P.nome_sala = S.nome_sala
            JOIN SETTORE SETT ON P.id_settore = SETT.id_settore
            JOIN UTENTE U ON P.id_organizzatore = U.id_utente
            ORDER BY P.data DESC, P.ora DESC";
    return $cid->query($sql)->fetch_all(MYSQLI_ASSOC);
}
function eliminaPrenotazioneAdmin($cid, $id_settore, $nome_sala, $data, $ora) {
    $stmt = $cid->prepare("DELETE FROM PRENOTAZIONE WHERE id_settore = ? AND nome_sala = ? AND data = ? AND ora = ?");
    $stmt->bind_param("isss", $id_settore, $nome_sala, $data, $ora);
    try { $stmt->execute(); return $stmt->affected_rows > 0; } catch (Exception $e) { return false; }
}

// Dotazioni
function getAllDotazioni($cid) {
    return $cid->query("SELECT * FROM DOTAZIONE_DI_SUPPORTO ORDER BY tipo ASC")->fetch_all(MYSQLI_ASSOC);
}
function creaDotazione($cid, $tipo) {
    $stmt = $cid->prepare("INSERT INTO DOTAZIONE_DI_SUPPORTO (tipo) VALUES (?)");
    $stmt->bind_param("s", $tipo);
    return $stmt->execute();
}
function aggiornaDotazione($cid, $id, $tipo) {
    $stmt = $cid->prepare("UPDATE DOTAZIONE_DI_SUPPORTO SET tipo = ? WHERE id_dotazione = ?");
    $stmt->bind_param("si", $tipo, $id);
    return $stmt->execute();
}
function eliminaDotazione($cid, $id) {
    $stmt = $cid->prepare("DELETE FROM DOTAZIONE_DI_SUPPORTO WHERE id_dotazione = ?");
    $stmt->bind_param("i", $id);
    try { return $stmt->execute(); } catch (Exception $e) { return false; }
}


/* -------------------------------------------------------------------------- */
/* 4. FUNZIONI PER RESPONSABILE E GESTIONE SALE                               */
/* -------------------------------------------------------------------------- */

// Recupera TUTTE le sale con il nome del settore (Per gestione_sale.php Admin)
function getAllSaleGlobal($cid) {
    $sql = "SELECT S.*, SETT.nome AS nome_settore 
             FROM SALA S 
             JOIN SETTORE SETT ON S.id_settore = SETT.id_settore 
             ORDER BY SETT.nome ASC, S.nome_sala ASC";
    $result = $cid->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Recupera le dotazioni di una specifica sala (Stringa formattata)
function getDotazioniSala($cid, $id_settore, $nome_sala) {
    $sql = "SELECT D.tipo 
            FROM SALA_DOTAZIONE SD
            JOIN DOTAZIONE_DI_SUPPORTO D ON SD.id_dotazione = D.id_dotazione
            WHERE SD.id_settore = ? AND SD.nome_sala = ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("is", $id_settore, $nome_sala);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $lista = [];
    while($row = $res->fetch_assoc()) {
        $lista[] = $row['tipo'];
    }
    return implode(", ", $lista);
}

// Recupera le prenotazioni fatte dall'organizzatore (Responsabile)
function getPrenotazioniByOrganizzatore($cid, $id_settore, $id_organizzatore) {
    $sql = "SELECT * FROM PRENOTAZIONE 
            WHERE id_settore = ? 
            AND id_organizzatore = ? 
            AND data >= CURDATE() 
            ORDER BY data ASC, ora ASC";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("ii", $id_settore, $id_organizzatore);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Recupera le risposte agli inviti inviati dal responsabile
function getRisposteInvitiByResponsabile($cid, $id_responsabile) {
    $sql = "SELECT I.*, U.nome, U.cognome, U.ruolo, P.attivita 
            FROM INVITO I
            JOIN PRENOTAZIONE P ON I.id_settore = P.id_settore 
                 AND I.nome_sala = P.nome_sala 
                 AND I.data = P.data 
                 AND I.ora = P.ora
            JOIN UTENTE U ON I.id_utente = U.id_utente
            WHERE P.id_organizzatore = ?
            ORDER BY I.data_risposta DESC, I.data ASC";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("i", $id_responsabile);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>