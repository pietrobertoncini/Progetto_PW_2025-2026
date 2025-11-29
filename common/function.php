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

// STATISTICHE per ADMIN
/* conta tutti gli utenti */
function getTotaleUtenti($cid)
{
    $sql = "SELECT COUNT(*) as totale FROM UTENTE";

    if ($result = $cid->query($sql)) {
        $row = $result->fetch_assoc();
        return $row['totale'];
    }
    return 0; // In caso di errore o tabella vuota
}

/* conta tutti i settori */
function getTotaleSettori($cid)
{
    $sql = "SELECT COUNT(*) as totale FROM SETTORE";

    if ($result = $cid->query($sql)) {
        $row = $result->fetch_assoc();
        return $row['totale'];
    }
    return 0;
}

/* conta tutte le prenotazioni */
function getPrenotazioni($cid)
{
    $sql = "SELECT COUNT(*) as totale FROM PRENOTAZIONE";
    if ($result = $cid->query($sql)) {
        $row = $result->fetch_assoc();
        return $row['totale'];
    }
    return 0;
}

// ADMIN_SETTORI
/* recupera tutti i settori includendo nome e cognome del responsabile */
function getAllSettoriAdmin($cid)
{
    $sql = "SELECT S.*, U.nome AS nome_resp, U.cognome AS cognome_resp 
            FROM SETTORE S
            LEFT JOIN UTENTE U ON S.id_responsabile = U.id_utente
            ORDER BY S.nome ASC";
    $result = $cid->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/* crea un nuovo settore */
function creaSettore($cid, $nome, $tipo)
{
    $sql = "INSERT INTO SETTORE (nome, tipo, num_iscritti) VALUES (?, ?, 0)";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("ss", $nome, $tipo);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/* aggiorna un settore */
function aggiornaSettore($cid, $id_settore, $nome, $tipo)
{
    $sql = "UPDATE SETTORE SET nome = ?, tipo = ? WHERE id_settore = ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("ssi", $nome, $tipo, $id_settore);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

/* elimina un settore */
function eliminaSettore($cid, $id_settore)
{
    $sql = "DELETE FROM SETTORE WHERE id_settore = ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("i", $id_settore);

    try {
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (mysqli_sql_exception $e) {
        // Catturiamo l'errore (es. violazione foreign key) per gestirlo nel backend
        return false;
    }
}

// ADMIN_UTENTI
/* recupera tutti gli utenti, con possibilità di filtro per ruolo */
function getAllUtentiAdmin($cid, $filtro_ruolo = null)
{
    $sql = "SELECT U.*, S.nome AS nome_settore
            FROM UTENTE U
            LEFT JOIN SETTORE S ON U.id_settore = S.id_settore
            ORDER BY U.is_admin DESC, U.is_responsabile DESC, U.cognome ASC, U.nome ASC";

    $result = $cid->query($sql);
    $utenti = $result->fetch_all(MYSQLI_ASSOC);
    return $utenti;
}

/* promuove un utente a responsabile */
function promuoviAResponsabile($cid, $id_utente, $id_settore)
{
    // Usiamo una transazione perché dobbiamo aggiornare due tabelle
    $cid->begin_transaction();

    try {
        // Rimuovi l'eventuale vecchio responsabile da quel settore
        $sql_reset = "UPDATE SETTORE SET id_responsabile = NULL WHERE id_settore = ?";
        $stmt_reset = $cid->prepare($sql_reset);
        $stmt_reset->bind_param("i", $id_settore);
        $stmt_reset->execute();
        $stmt_reset->close();

        // Aggiorna l'utente: diventa responsabile e gli assegniamo il settore
        // (Solo se non è già admin)
        $sql_u = "UPDATE UTENTE SET is_responsabile = 1, id_settore = ? WHERE id_utente = ? AND is_admin = 0";
        $stmt_u = $cid->prepare($sql_u);
        $stmt_u->bind_param("ii", $id_settore, $id_utente);
        $stmt_u->execute();
        if ($stmt_u->affected_rows === 0) {
            throw new Exception("Utente non valido.");
        }
        $stmt_u->close();

        // Aggiorna il settore: imposta il nuovo id_responsabile
        $sql_s = "UPDATE SETTORE SET id_responsabile = ? WHERE id_settore = ?";
        $stmt_s = $cid->prepare($sql_s);
        $stmt_s->bind_param("ii", $id_utente, $id_settore);
        $stmt_s->execute();
        $stmt_s->close();

        $cid->commit();
        return true;
    } catch (Exception $e) {
        $cid->rollback();
        return false;
    }
}

/* retrocede un responsabile a utente */
function retrocediResponsabile($cid, $id_utente)
{
    $cid->begin_transaction();

    try {
        // Aggiorna l'utente: non è più responsabile
        // Nota: Manteniamo l'id_settore, così rimane un membro "semplice" di quel settore.
        $sql_u = "UPDATE UTENTE SET is_responsabile = 0 WHERE id_utente = ?";
        $stmt_u = $cid->prepare($sql_u);
        $stmt_u->bind_param("i", $id_utente);
        $stmt_u->execute();
        $stmt_u->close();

        // Aggiorna il settore: rimuove il collegamento al responsabile
        $sql_s = "UPDATE SETTORE SET id_responsabile = NULL WHERE id_responsabile = ?";
        $stmt_s = $cid->prepare($sql_s);
        $stmt_s->bind_param("i", $id_utente);
        $stmt_s->execute();
        $stmt_s->close();

        $cid->commit();
        return true;
    } catch (Exception $e) {
        $cid->rollback();
        return false;
    }
}

/* elimina utente */
function eliminaUtente($cid, $id_utente_da_eliminare, $id_mio_utente)
{
    // Impedisce di eliminare se stessi o un altro admin
    $sql_check = "SELECT is_admin FROM UTENTE WHERE id_utente = ?";
    $stmt_check = $cid->prepare($sql_check);
    $stmt_check->bind_param("i", $id_utente_da_eliminare);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $utente = $res_check->fetch_assoc();
    $stmt_check->close();

    if (!$utente || $utente['is_admin'] || $id_utente_da_eliminare == $id_mio_utente) {
        return false; // Non si può eliminare
    }

    $sql = "DELETE FROM UTENTE WHERE id_utente = ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("i", $id_utente_da_eliminare);

    try {
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    } catch (mysqli_sql_exception $e) {
        return false; // Errore DB (es. vincoli FK)
    }
}
