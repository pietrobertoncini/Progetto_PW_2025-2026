<?php

// CONNESSIONE E AUTENTICAZIONE

function connessione($hostname, $username, $password, $dbname)
{
    try {
        $cid = new mysqli($hostname, $username, $password, $dbname);
        return $cid;
    } catch (Exception $e) {
        return null;
    }
}

function controllaUtente($cid, $email, $password)
{
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


// REGISTRAZIONE E MODIFICA UTENTE

function inserisciUtente($cid, $nome, $cognome, $email, $password, $data_nascita, $ruolo, $id_settore, $foto = null)
{
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO UTENTE (nome, cognome, email, password_hash, data_nascita, ruolo, id_settore, is_responsabile, foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, FALSE, ?)";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("ssssssis", $nome, $cognome, $email, $password_hash, $data_nascita, $ruolo, $id_settore, $foto);
    $stmt->execute();
    $id = $cid->insert_id;
    $stmt->close();

    // aggiornamento num_iscritti
    if ($id_settore) {
        $sqlUpdate = "UPDATE SETTORE SET num_iscritti = num_iscritti + 1 WHERE id_settore = ?";
        $stmtUpd = $cid->prepare($sqlUpdate);
        $stmtUpd->bind_param("i", $id_settore);
        $stmtUpd->execute();
        $stmtUpd->close();
    }

    return $id;
}

function modificaUtente($cid, $id_utente, $nome, $cognome, $email, $data_nascita, $foto)
{
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

function eliminaMioProfilo($cid, $id_utente)
{
    // Recupero il settore dell'utente PRIMA di cancellarlo
    $id_settore = null;
    $percorsoFoto = null;

    $stmtGet = $cid->prepare("SELECT id_settore, foto FROM UTENTE WHERE id_utente = ?");
    $stmtGet->bind_param("i", $id_utente);
    $stmtGet->execute();
    $res = $stmtGet->get_result();
    if ($row = $res->fetch_assoc()) {
        $id_settore = $row['id_settore'];
        $percorsoFoto = $row['foto']; // Salviamo il percorso
    }
    $stmtGet->close();

    // Se c'è una foto, la cancelliamo fisicamente dal disco
    if (!empty($percorsoFoto)) {
        rimuoviVecchiaFoto($percorsoFoto);
    }

    $stmt = $cid->prepare("DELETE FROM UTENTE WHERE id_utente = ?");
    $stmt->bind_param("i", $id_utente);

    try {
        $esito = $stmt->execute();
        $stmt->close();

        // 3. Se la cancellazione è riuscita, decremento num_iscritti nel settore
        if ($esito && $id_settore) {
            $stmtUpd = $cid->prepare("UPDATE SETTORE SET num_iscritti = num_iscritti - 1 WHERE id_settore = ?");
            $stmtUpd->bind_param("i", $id_settore);
            $stmtUpd->execute();
            $stmtUpd->close();
        }
        return $esito;
    } catch (Exception $e) {
        return false;
    }
}

function datiUtenteCompleti($cid, $id_utente)
{
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


// NAVBAR

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
            WHERE I.id_utente = ? 
            AND I.stato = 'invitato'
            ORDER BY I.data ASC, I.ora ASC";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getImpegniFuturi($cid, $id_utente)
{
    $sql = "SELECT I.*, S.nome_sala, P.attivita, P.durata, P.id_organizzatore, U.nome as nome_org, U.cognome as cognome_org
            FROM INVITO I
            JOIN PRENOTAZIONE P ON I.id_settore = P.id_settore 
                 AND I.nome_sala = P.nome_sala 
                 AND I.data = P.data 
                 AND I.ora = P.ora
            JOIN SALA S ON P.id_settore = S.id_settore AND P.nome_sala = S.nome_sala
            JOIN UTENTE U ON P.id_organizzatore = U.id_utente
            WHERE I.id_utente = ? 
              AND I.stato = 'accettato'
            ORDER BY P.data ASC, P.ora ASC";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("i", $id_utente);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}


// FUNZIONI PER ADMIN 

// Statistiche
function getTotaleUtenti($cid)
{
    $res = $cid->query("SELECT COUNT(*) as totale FROM UTENTE");
    return $res ? $res->fetch_assoc()['totale'] : 0;
}

function getTotaleSettori($cid)
{
    $res = $cid->query("SELECT COUNT(*) as totale FROM SETTORE");
    return $res ? $res->fetch_assoc()['totale'] : 0;
}

function getPrenotazioni($cid)
{
    $res = $cid->query("SELECT COUNT(*) as totale FROM PRENOTAZIONE");
    return $res ? $res->fetch_assoc()['totale'] : 0;
}

// Settori
function getAllSettoriAdmin($cid)
{
    $sql = "SELECT S.*, U.nome AS nome_resp, U.cognome AS cognome_resp 
            FROM SETTORE S
            LEFT JOIN UTENTE U ON S.id_responsabile = U.id_utente
            ORDER BY S.nome ASC";
    return $cid->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function creaSettore($cid, $nome, $tipo)
{
    $stmt = $cid->prepare("INSERT INTO SETTORE (nome, tipo, num_iscritti) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $nome, $tipo);
    return $stmt->execute();
}

function aggiornaSettore($cid, $id, $nome, $tipo)
{
    $stmt = $cid->prepare("UPDATE SETTORE SET nome = ?, tipo = ? WHERE id_settore = ?");
    $stmt->bind_param("ssi", $nome, $tipo, $id);
    return $stmt->execute();
}

function eliminaSettore($cid, $id)
{
    $stmt = $cid->prepare("DELETE FROM SETTORE WHERE id_settore = ?");
    $stmt->bind_param("i", $id);
    try {
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

// Sale
function creaSala($cid, $id_settore, $nome, $capienza)
{
    $stmt = $cid->prepare("INSERT INTO SALA (id_settore, nome_sala, capienza_max) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $id_settore, $nome, $capienza);
    return $stmt->execute();
}

function aggiornaSala($cid, $old_id_settore, $old_nome, $new_id_settore, $new_nome, $new_capienza)
{
    // Aggiorniamo anche id_settore e nome_sala che sono chiavi primarie
    $sql = "UPDATE SALA 
            SET id_settore = ?, nome_sala = ?, capienza_max = ? 
            WHERE id_settore = ? AND nome_sala = ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("isisis", $new_id_settore, $new_nome, $new_capienza, $old_id_settore, $old_nome);
    return $stmt->execute();
}

function eliminaSala($cid, $id_settore, $nome)
{
    $stmt = $cid->prepare("DELETE FROM SALA WHERE id_settore = ? AND nome_sala = ?");
    $stmt->bind_param("is", $id_settore, $nome);
    return $stmt->execute();
}

// Utenti
function getAllUtentiAdmin($cid)
{
    $sql = "SELECT U.*, S.nome AS nome_settore 
    FROM UTENTE U LEFT JOIN SETTORE S ON U.id_settore = S.id_settore 
    ORDER BY U.is_admin DESC, U.is_responsabile DESC, U.cognome ASC";
    return $cid->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function promuoviAResponsabile($cid, $id_utente, $id_settore)
{
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
    } catch (Exception $e) {
        $cid->rollback();
        return false;
    }
}

function retrocediResponsabile($cid, $id_utente)
{
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
    } catch (Exception $e) {
        $cid->rollback();
        return false;
    }
}

function eliminaUtente($cid, $id_target, $id_self)
{
    if ($id_target == $id_self) return false;

    // Recupero il settore dell'utente PRIMA di cancellarlo
    $id_settore = null;
    $percorsoFoto = null;

    $stmtGet = $cid->prepare("SELECT id_settore, foto FROM UTENTE WHERE id_utente = ?");
    $stmtGet->bind_param("i", $id_target);
    $stmtGet->execute();
    $res = $stmtGet->get_result();
    if ($row = $res->fetch_assoc()) {
        $id_settore = $row['id_settore'];
        $percorsoFoto = $row['foto']; // Salviamo il percorso
    }
    $stmtGet->close();

    // Se c'è una foto, la cancelliamo fisicamente dal disco
    if (!empty($percorsoFoto)) {
        rimuoviVecchiaFoto($percorsoFoto); // Funzione helper già esistente
    }

    $stmt = $cid->prepare("DELETE FROM UTENTE WHERE id_utente = ?");
    $stmt->bind_param("i", $id_target);
    try {
        $esito = $stmt->execute();
        $stmt->close();

        // Se cancellato con successo, decremento num_iscritti (-1)
        if ($esito && $id_settore) {
            $stmtUpd = $cid->prepare("UPDATE SETTORE SET num_iscritti = num_iscritti - 1 WHERE id_settore = ?");
            $stmtUpd->bind_param("i", $id_settore);
            $stmtUpd->execute();
            $stmtUpd->close();
        }

        return $esito;
    } catch (Exception $e) {
        return false;
    }
}

// Prenotazioni Globali
function getAllPrenotazioniAdmin($cid)
{
    $sql = "SELECT P.*, S.nome_sala, SETT.nome as nome_settore, U.nome AS nome_org, U.cognome as cognome_org
            FROM PRENOTAZIONE P
            JOIN SALA S ON P.id_settore = S.id_settore AND P.nome_sala = S.nome_sala
            JOIN SETTORE SETT ON P.id_settore = SETT.id_settore
            JOIN UTENTE U ON P.id_organizzatore = U.id_utente
            ORDER BY P.data DESC, P.ora DESC";
    return $cid->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function eliminaPrenotazioneAdmin($cid, $id_settore, $nome_sala, $data, $ora)
{
    $stmt = $cid->prepare("DELETE FROM PRENOTAZIONE WHERE id_settore = ? AND nome_sala = ? AND data = ? AND ora = ?");
    $stmt->bind_param("isss", $id_settore, $nome_sala, $data, $ora);
    try {
        $stmt->execute();
        return $stmt->affected_rows > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Dotazioni
function getAllDotazioni($cid)
{
    return $cid->query("SELECT * FROM DOTAZIONE_DI_SUPPORTO ORDER BY tipo ASC")->fetch_all(MYSQLI_ASSOC);
}

function creaDotazione($cid, $tipo)
{
    $stmt = $cid->prepare("INSERT INTO DOTAZIONE_DI_SUPPORTO (tipo) VALUES (?)");
    $stmt->bind_param("s", $tipo);
    return $stmt->execute();
}

function aggiornaDotazione($cid, $id, $tipo)
{
    $stmt = $cid->prepare("UPDATE DOTAZIONE_DI_SUPPORTO SET tipo = ? WHERE id_dotazione = ?");
    $stmt->bind_param("si", $tipo, $id);
    return $stmt->execute();
}

function eliminaDotazione($cid, $id)
{
    $stmt = $cid->prepare("DELETE FROM DOTAZIONE_DI_SUPPORTO WHERE id_dotazione = ?");
    $stmt->bind_param("i", $id);
    try {
        return $stmt->execute();
    } catch (Exception $e) {
        return false;
    }
}

function getPrenotazioniGriglia($cid, $id_settore, $nome_sala, $data_inizio, $data_fine)
{
    $prenotazioni_griglia = [];

    $sql = "SELECT P.*, U.nome as nome_org, U.cognome as cognome_org 
            FROM PRENOTAZIONE P
            JOIN UTENTE U ON P.id_organizzatore = U.id_utente
            WHERE P.id_settore = ? AND P.nome_sala = ? 
            AND P.data BETWEEN ? AND ?";

    $stmt = $cid->prepare($sql);
    $stmt->bind_param("isss", $id_settore, $nome_sala, $data_inizio, $data_fine);
    $stmt->execute();
    $res = $stmt->get_result();

    // Mappiamo i risultati in un array [data][ora] => info
    while ($row = $res->fetch_assoc()) {
        for ($i = 0; $i < $row['durata']; $i++) {
            $h = $row['ora'] + $i;

            $prenotazioni_griglia[$row['data']][$h] = [
                'dati' => $row,
                'is_start' => ($i === 0)
            ];
        }
    }

    return $prenotazioni_griglia;
}


// FUNZIONI PER RESPONSABILE E GESTIONE DOTAZIONI

// Recupera TUTTE le sale con il nome del settore 
function getAllSaleGlobal($cid)
{
    $sql = "SELECT S.*, SETT.nome AS nome_settore, SETT.tipo
             FROM SALA S 
             JOIN SETTORE SETT ON S.id_settore = SETT.id_settore 
             ORDER BY SETT.nome ASC, S.nome_sala ASC";
    $result = $cid->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Recupera le dotazioni di una specifica sala (Stringa formattata)
function getDotazioniSala($cid, $id_settore, $nome_sala)
{
    $sql = "SELECT D.tipo 
            FROM SALA_DOTAZIONE SD
            JOIN DOTAZIONE_DI_SUPPORTO D ON SD.id_dotazione = D.id_dotazione
            WHERE SD.id_settore = ? AND SD.nome_sala = ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("is", $id_settore, $nome_sala);
    $stmt->execute();
    $res = $stmt->get_result();

    $lista = [];
    while ($row = $res->fetch_assoc()) {
        $lista[] = $row['tipo'];
    }
    return implode(", ", $lista);
}

function aggiornaDotazioniSala($cid, $id_settore, $nome_sala, $lista_id_dotazioni)
{
    $cid->begin_transaction();
    try {
        // Elimina TUTTE le dotazioni attuali per questa sala
        $sqlDelete = "DELETE FROM SALA_DOTAZIONE WHERE id_settore = ? AND nome_sala = ?";
        $stmtDel = $cid->prepare($sqlDelete);
        $stmtDel->bind_param("is", $id_settore, $nome_sala);
        $stmtDel->execute();

        // Inserisce le NUOVE dotazioni selezionate
        if (!empty($lista_id_dotazioni)) {
            $sqlInsert = "INSERT INTO SALA_DOTAZIONE (id_settore, nome_sala, id_dotazione) VALUES (?, ?, ?)";
            $stmtIns = $cid->prepare($sqlInsert);

            foreach ($lista_id_dotazioni as $id_dot) {
                $stmtIns->bind_param("isi", $id_settore, $nome_sala, $id_dot);
                $stmtIns->execute();
            }
        }

        $cid->commit();
        return true;
    } catch (Exception $e) {
        $cid->rollback();
        return false;
    }
}

// Recupera le prenotazioni fatte dall'organizzatore (Responsabile)
function getPrenotazioniByOrganizzatore($cid, $id_settore, $id_organizzatore)
{
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
function getRisposteInvitiByResponsabile($cid, $id_responsabile)
{
    $sql = "SELECT I.*, U.nome, U.cognome, U.ruolo, P.attivita 
            FROM INVITO I
            JOIN PRENOTAZIONE P ON I.id_settore = P.id_settore 
                 AND I.nome_sala = P.nome_sala 
                 AND I.data = P.data 
                 AND I.ora = P.ora
            JOIN UTENTE U ON I.id_utente = U.id_utente
            WHERE P.id_organizzatore = ?
            AND I.id_utente != P.id_organizzatore
            ORDER BY I.data_risposta DESC, I.data ASC";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("i", $id_responsabile);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getInvitatiPrenotazione($cid, $id_settore, $nome_sala, $data, $ora)
{
    $sql = "SELECT U.nome, U.cognome, U.email, I.stato, I.motivazione, U.ruolo
            FROM INVITO I
            JOIN UTENTE U ON I.id_utente = U.id_utente
            WHERE I.id_settore = ? AND I.nome_sala = ? AND I.data = ? AND I.ora = ?
            -- Escludiamo l'organizzatore dalla lista visuale degli 'invitati'
            AND I.id_utente != (SELECT id_organizzatore FROM PRENOTAZIONE WHERE id_settore=? AND nome_sala=? AND data=? AND ora=?)
            ORDER BY U.cognome";

    $stmt = $cid->prepare($sql);
    $stmt->bind_param("isssisss", $id_settore, $nome_sala, $data, $ora, $id_settore, $nome_sala, $data, $ora);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// FUNZIONI DI GESTIONE

// Recupera dati sala per modifica
function getSalaById($cid, $id_settore, $nome_sala)
{
    $stmt = $cid->prepare("SELECT * FROM SALA WHERE id_settore = ? AND nome_sala = ?");
    $stmt->bind_param("is", $id_settore, $nome_sala);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Recupera ID dotazioni attuali
function getDotazioniIdsBySala($cid, $id_settore, $nome_sala)
{
    $stmt = $cid->prepare("SELECT id_dotazione FROM SALA_DOTAZIONE WHERE id_settore = ? AND nome_sala = ?");
    $stmt->bind_param("is", $id_settore, $nome_sala);
    $stmt->execute();
    $res = $stmt->get_result();
    $ids = [];
    while ($row = $res->fetch_assoc()) {
        $ids[] = $row['id_dotazione'];
    }
    return $ids;
}

// Salva o Aggiorna sala (usata sia in creazione che modifica)
function salvaSalaConDotazioni($cid, $id_settore, $nome_sala, $capienza, $dotazioni, $old_nome = null)
{
    if ($old_nome) {
        // UPDATE
        $stmt = $cid->prepare("UPDATE SALA SET nome_sala = ?, capienza_max = ? WHERE id_settore = ? AND nome_sala = ?");
        $stmt->bind_param("siis", $nome_sala, $capienza, $id_settore, $old_nome);
        $stmt->execute();

        // Rimuovi vecchie dotazioni per reinserirle
        $stmt_del = $cid->prepare("DELETE FROM SALA_DOTAZIONE WHERE id_settore = ? AND nome_sala = ?");
        $stmt_del->bind_param("is", $id_settore, $nome_sala);
        $stmt_del->execute();
    } else {
        // INSERT NUOVA
        $stmt = $cid->prepare("INSERT INTO SALA (id_settore, nome_sala, capienza_max) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $id_settore, $nome_sala, $capienza);
        $stmt->execute();
    }

    // Inserimento Dotazioni
    if (!empty($dotazioni)) {
        $stmt_dot = $cid->prepare("INSERT INTO SALA_DOTAZIONE (id_settore, nome_sala, id_dotazione) VALUES (?, ?, ?)");
        foreach ($dotazioni as $id_dot) {
            $stmt_dot->bind_param("isi", $id_settore, $nome_sala, $id_dot);
            $stmt_dot->execute();
        }
    }
    return true;
}

function eliminaSalaResponsabile($cid, $id_settore, $nome_sala)
{
    $stmt = $cid->prepare("DELETE FROM SALA WHERE id_settore = ? AND nome_sala = ?");
    $stmt->bind_param("is", $id_settore, $nome_sala);
    return $stmt->execute();
}

// Recupera singola prenotazione
function getPrenotazioneSingola($cid, $id_settore, $nome_sala, $data, $ora)
{
    $sql = "SELECT * FROM PRENOTAZIONE WHERE id_settore = ? AND nome_sala = ? AND data = ? AND ora = ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("issi", $id_settore, $nome_sala, $data, $ora);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Check sovrapposizione per NUOVA prenotazione
function checkSovrapposizioneNuova($cid, $id_settore, $nome_sala, $data, $ora_nuova, $durata_nuova)
{
    $sql = "SELECT ora FROM PRENOTAZIONE
            WHERE id_settore = ? AND nome_sala = ? AND data = ?      
              AND (
                   (? >= ora AND ? < (ora + durata))
                   OR
                   (ora >= ? AND ora < (? + ?))
                  )";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("issiiiii", $id_settore, $nome_sala, $data, $ora_nuova, $ora_nuova, $ora_nuova, $ora_nuova, $durata_nuova);
    $stmt->execute();
    return ($stmt->get_result()->num_rows > 0);
}

function checkSovrapposizioneModifica($cid, $id_settore, $nome_sala, $new_data, $new_ora, $new_durata, $old_data, $old_ora)
{
    // Cerchiamo prenotazioni nello stesso giorno che si sovrappongono
    // MA escludiamo quella che stiamo modificando
    $sql = "SELECT ora FROM PRENOTAZIONE
            WHERE id_settore = ? 
              AND nome_sala = ? 
              AND data = ? 
              AND (? < (ora + durata) AND (? + ?) > ora)
              AND NOT (data = ? AND ora = ?)";

    $stmt = $cid->prepare($sql);

    $stmt->bind_param(
        "issiiisi",
        $id_settore,
        $nome_sala,
        $new_data,
        $new_ora,
        $new_ora,
        $new_durata,
        $old_data,
        $old_ora
    );
    $stmt->execute();
    return ($stmt->get_result()->num_rows > 0);
}

// Crea prenotazione e invita utenti
function creaPrenotazioneConInviti($cid, $id_settore, $nome_sala, $data, $ora, $durata, $attivita, $id_organizzatore, $invitati)
{
    $stmt = $cid->prepare("INSERT INTO PRENOTAZIONE (id_settore, nome_sala, data, ora, durata, attivita, id_organizzatore) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississi", $id_settore, $nome_sala, $data, $ora, $durata, $attivita, $id_organizzatore);
    $stmt->execute();

    $stmt_inv = $cid->prepare("INSERT INTO INVITO (id_utente, id_settore, nome_sala, data, ora, stato) VALUES (?, ?, ?, ?, ?, ?)");

    // Inseriamo l'organizzatore come 'accettato'
    $stato_org = 'accettato';
    $stmt_inv->bind_param("iissis", $id_organizzatore, $id_settore, $nome_sala, $data, $ora, $stato_org);
    $stmt_inv->execute();

    if (!empty($invitati)) {
        $stato_inv = 'invitato';
        foreach ($invitati as $id_invitato) {
            if ($id_invitato == $id_organizzatore) continue;
            $stmt_inv->bind_param("iissis", $id_invitato, $id_settore, $nome_sala, $data, $ora, $stato_inv);
            $stmt_inv->execute();
        }
    }
    return true;
}

// Funzione per AGGIORNARE una prenotazione esistente
function aggiornaPrenotazione($cid, $new_data, $new_ora, $new_durata, $new_attivita, $id_settore, $old_nome_sala, $old_data, $old_ora)
{
    $sql = "UPDATE PRENOTAZIONE 
            SET data = ?, ora = ?, durata = ?, attivita = ? 
            WHERE id_settore = ? AND nome_sala = ? AND data = ? AND ora = ?";
    $stmt = $cid->prepare($sql);

    $stmt->bind_param("siisissi", $new_data, $new_ora, $new_durata, $new_attivita, $id_settore, $old_nome_sala, $old_data, $old_ora);
    return $stmt->execute();
}

function eliminaPrenotazioneResponsabile($cid, $id_settore, $nome_sala, $data, $ora)
{
    $stmt = $cid->prepare("DELETE FROM PRENOTAZIONE WHERE id_settore = ? AND nome_sala = ? AND data = ? AND ora = ?");
    $stmt->bind_param("issi", $id_settore, $nome_sala, $data, $ora);
    return $stmt->execute();
}

// Per prenota.php
function getSaleBySettore($cid, $id_settore)
{
    $stmt = $cid->prepare("SELECT * FROM SALA WHERE id_settore = ? ORDER BY nome_sala");
    $stmt->bind_param("i", $id_settore);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getOccupazioniSettimana($cid, $nome_sala, $id_settore, $lunedi, $domenica)
{
    $sql = "SELECT P.data, P.ora, P.durata, P.attivita, U.nome, U.cognome 
            FROM PRENOTAZIONE P
            JOIN UTENTE U ON P.id_organizzatore = U.id_utente
            WHERE P.nome_sala = ? AND P.id_settore = ? 
            AND P.data BETWEEN ? AND ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("siss", $nome_sala, $id_settore, $lunedi, $domenica);
    $stmt->execute();
    return $stmt->get_result();
}

function getUtentiInvitabili($cid, $id_escluso, $data, $ora)
{
    $sql = "SELECT U.id_utente, U.nome, U.cognome, U.ruolo, U.is_responsabile, 
                   U.id_settore, S.nome as nome_settore, S.tipo as tipo_settore
            FROM UTENTE U
            LEFT JOIN SETTORE S ON U.id_settore = S.id_settore
            WHERE U.id_utente != ?
            AND U.is_admin = 0
            AND U.id_utente NOT IN (SELECT I.id_utente FROM INVITO I WHERE I.data = ? AND I.ora = ? AND I.stato = 'accettato')
            AND U.id_utente NOT IN (SELECT P.id_organizzatore FROM PRENOTAZIONE P WHERE P.data = ? AND P.ora = ?)
            ORDER BY U.cognome";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("isisi", $id_escluso, $data, $ora, $data, $ora);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Check sovrapposizione Utente (Inviti)
function checkSovrapposizioneUtente($cid, $id_utente_target, $data_target, $ora_target, $durata_target, $id_settore_target, $nome_sala_target)
{
    $sql = "SELECT P.ora FROM PRENOTAZIONE P 
            JOIN INVITO I ON P.id_settore = I.id_settore AND P.nome_sala = I.nome_sala AND P.data = I.data AND P.ora = I.ora
            WHERE I.id_utente = ? AND I.stato = 'accettato' AND P.data = ?
            AND NOT (P.id_settore = ? AND P.nome_sala = ? AND P.data = ? AND P.ora = ?)     
            AND ((? >= P.ora AND ? < (P.ora + P.durata)) 
            OR (P.ora >= ? AND P.ora < (? + ?)))";

    $stmt = $cid->prepare($sql);
    $stmt->bind_param("isissiiiiii", $id_utente_target, $data_target, $id_settore_target, $nome_sala_target, $data_target, $ora_target, $ora_target, $ora_target, $ora_target, $ora_target, $durata_target);
    $stmt->execute();
    return ($stmt->get_result()->num_rows > 0);
}

function rispondiInvito($cid, $id_utente, $id_settore, $nome_sala, $data, $ora, $risposta, $motivazione)
{
    $stmt = $cid->prepare("UPDATE INVITO SET stato = ?, motivazione = ?, data_risposta = NOW() WHERE id_utente = ? AND id_settore = ? AND nome_sala = ? AND data = ? AND ora = ?");
    $stmt->bind_param("sssisss", $risposta, $motivazione, $id_utente, $id_settore, $nome_sala, $data, $ora);
    return $stmt->execute();
}

// Recupera lista semplice settori per i dropdown dei filtri
function getListaSettori($cid)
{
    // Ordiniamo per nome per facilitare la ricerca visiva
    $res = $cid->query("SELECT id_settore, nome FROM SETTORE ORDER BY nome ASC");
    $result = [];
    if ($res) {
        $result = $res->fetch_all(MYSQLI_ASSOC);
    }
    return $result;
}

// Elabora slot selezionati
function elaboraSlotSelezionati($slots)
{
    if (empty($slots)) {
        return ['error' => "Seleziona almeno un orario."];
    }

    $ore_selezionate = [];
    $giorno_riferimento = null;

    // Parsing e controllo Giorno Unico
    foreach ($slots as $slot) {
        $parts = explode('|', $slot);
        $d = $parts[0];
        $h = (int)$parts[1];

        if ($giorno_riferimento === null) {
            $giorno_riferimento = $d;
        }

        if ($d !== $giorno_riferimento) {
            return ['error' => "Puoi selezionare orari solo per un singolo giorno alla volta."];
        }

        $ore_selezionate[] = $h;
    }

    // Ordinamento e controllo Consecutività
    sort($ore_selezionate, SORT_NUMERIC);

    for ($i = 0; $i < count($ore_selezionate) - 1; $i++) {
        if ($ore_selezionate[$i + 1] !== ($ore_selezionate[$i] + 1)) {
            return ['error' => "Errore: Hai selezionato orari non consecutivi. Seleziona solo ore di fila."];
        }
    }

    // Calcolo dati finali
    $ora_inizio = $ore_selezionate[0];
    $ultima_ora = end($ore_selezionate);
    $durata = ($ultima_ora - $ora_inizio) + 1;

    return [
        'error' => null,
        'data' => $giorno_riferimento,
        'ora' => $ora_inizio,
        'durata' => $durata
    ];
}


// GESTIONE FOTO (REGISTRA E MODIFICA PROFILO

function uploadFotoProfilo($fileInput)
{
    // Controlli base
    if (!isset($fileInput) || $fileInput['error'] != 0) {
        return null; // Nessuna foto caricata o errore
    }

    // Impostazione percorsi
    $cartellaDestinazione = "../uploads/propic/";

    // Generazione nome univoco (timestamp + nome originale)
    $nomeFileUnivoco = time() . "_" . basename($fileInput["name"]);
    $targetFilePath = $cartellaDestinazione . $nomeFileUnivoco;

    // Spostamento file
    if (move_uploaded_file($fileInput["tmp_name"], $targetFilePath)) {
        // Ritorna il percorso stringa da salvare nel DB
        return "uploads/propic/" . $nomeFileUnivoco;
    }

    return null;
}

function rimuoviVecchiaFoto($pathRelativoDalDb)
{

    if (empty($pathRelativoDalDb)) {
        return;
    }

    $percorsoFisico = dirname(__DIR__) . '/' . $pathRelativoDalDb;

    // Sostituiamo gli slash per compatibilità
    $percorsoFisico = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $percorsoFisico);

    if (!empty($pathRelativoDalDb) && file_exists($percorsoFisico)) {
        unlink($percorsoFisico); // cancella il file
    }
}

// FUNZIONI PER HOMEPAGE
// conta il numero di settori per tipo ('musica', 'teatro', 'ballo')
function getNumeroSettoriPerTipo($cid, $tipo)
{
    $stmt = $cid->prepare("SELECT COUNT(*) as num FROM SETTORE WHERE tipo = ?");
    $stmt->bind_param("s", $tipo);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res['num'];
}

// conta il numero di sale che appartengono a un settore di un determinato tipo
function getNumeroSalePerTipo($cid, $tipo)
{
    $sql = "SELECT COUNT(*) as num 
            FROM SALA S
            JOIN SETTORE SETT ON S.id_settore = SETT.id_settore
            WHERE SETT.tipo = ?";
    $stmt = $cid->prepare($sql);
    $stmt->bind_param("s", $tipo);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return $res['num'];
}


function renderCalendarGrid($lunedi_settimana, $occupied, $is_admin = false, $is_read_only = false)
{
    ob_start();
?>
    <div class="shadow-sm mb-4 rounded-4 overflow-hidden border">
        <div class="table-responsive">
            <table class="table table-sm calendar-table mb-0 text-center align-middle">
                <thead>
                    <tr>
                        <th class="align-middle" style="position: sticky; left: 0; z-index: 2; background-color: #d2b48c;">Ora</th>
                        <?php
                        $giorni = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
                        for ($i = 0; $i < 7; $i++) {
                            $d = date('Y-m-d', strtotime($lunedi_settimana . " +$i days"));
                            $giorno_str = date('d/m', strtotime($d));
                            $nome_giorno = $giorni[$i];

                            // Se oggi usa bg-warning, altrimenti il colore standard
                            $class_th = ($d == date('Y-m-d')) ? 'bg-warning bg-opacity-25 text-dark' : '';

                            echo "<th class='$class_th' style='width: 12%'>$nome_giorno<br><small class='fw-normal'>$giorno_str</small></th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($ora = 9; $ora < 23; $ora++): ?>
                        <tr>
                            <td class="fw-bold align-middle" style="position: sticky; left: 0; z-index: 1;"><?php echo $ora; ?>:00</td>
                            <?php for ($i = 0; $i < 7; $i++):
                                $data_curr = date('Y-m-d', strtotime($lunedi_settimana . " +$i days"));
                                $is_occupied = isset($occupied[$data_curr][$ora]);
                                $is_past = (strtotime($data_curr . " " . $ora . ":00") < time());
                                $value = $data_curr . "|" . $ora;

                                if ($is_occupied) {
                                    $cell = $occupied[$data_curr][$ora];
                                    $info = $cell['info'] ?? $cell['dati'];

                                    // Definiamo variabili comuni (Nome e Cognome)
                                    $nome = $info['nome'] ?? $info['nome_org'] ?? 'N/D';
                                    $cognome = $info['cognome'] ?? $info['cognome_org'] ?? '';
                                    $nominativo_completo = htmlspecialchars($nome . " " . $cognome);
                                    $nominativo_corto = htmlspecialchars($nome . " " . substr($cognome, 0, 1) . ".");

                                    // Stili colori testo
                                    $text_class = $is_past ? "text-muted" : "text-dark";

                                    if ($cell['is_start']) {
                                        $durata = $info['durata'];
                                        $bg_class = "bg-danger bg-opacity-10 border-danger";
                                        if ($is_read_only) $bg_class = "bg-info bg-opacity-10 border-info";
                                        if ($is_past) $bg_class = "bg-secondary bg-opacity-10 border-secondary";
                            ?>
                                        <td rowspan="<?php echo $durata; ?>" class="p-1 <?php echo $bg_class; ?> border border-opacity-25 align-middle">
                                            <div class="d-flex flex-column justify-content-center align-items-center" style="min-height: <?php echo ($durata * 30); ?>px;">

                                                <?php if ($durata == 1): ?>
                                                    <div class="dropdown w-100 h-100 d-flex align-items-center justify-content-between px-2">

                                                        <span class="fw-bold small text-truncate text-start <?php echo $text_class; ?>" style="max-width: 85%;">
                                                            <?php echo htmlspecialchars($info['attivita']); ?>
                                                        </span>

                                                        <button class="btn btn-sm btn-link p-0 text-decoration-none <?php echo $text_class; ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>

                                                        <ul class="dropdown-menu shadow border-0 z-3">
                                                            <li class="px-3 py-2">
                                                                <h6 class="dropdown-header p-0 fw-bold text-dark">
                                                                    Dettagli <?php echo $is_past ? '(Concluso)' : ''; ?>
                                                                </h6>
                                                                <div class="small text-muted" style="min-width: 200px;">
                                                                    <strong><?php echo htmlspecialchars($info['attivita']); ?></strong><br>
                                                                    <i class="bi bi-person-fill"></i> Org: <?php echo $nominativo_completo; ?><br>
                                                                    <i class="bi bi-clock"></i> <?php echo $ora; ?>:00 - <?php echo $ora + $durata; ?>:00
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>

                                                <?php else: ?>
                                                    <div class="w-100 px-1 text-center overflow-hidden">
                                                        <small class="fw-bold text-uppercase mb-1 d-block" style="font-size: 0.6rem; letter-spacing: 1px; opacity: 0.7;">
                                                            <?php echo $is_read_only ? 'Prenotato' : 'Occupato'; ?>
                                                        </small>

                                                        <div class="fw-bold text-truncate <?php echo $text_class; ?> lh-sm mb-1" style="font-size: 0.85rem;" title="<?php echo htmlspecialchars($info['attivita']); ?>">
                                                            <?php echo htmlspecialchars($info['attivita']); ?>
                                                        </div>

                                                        <div class="small <?php echo $text_class; ?> opacity-75" style="font-size: 0.75rem;">
                                                            <i class="bi bi-person-fill"></i> <?php echo $nominativo_corto; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php
                                    }
                                } elseif ($is_past) {
                                    ?>
                                    <td class="bg-light text-muted small align-middle">-</td>
                                    <?php
                                } else {
                                    if (!$is_read_only && !$is_admin) {
                                    ?>
                                        <td class="cell-free p-0 align-middle">
                                            <label class="check-container d-flex justify-content-center align-items-center w-100 h-100">
                                                <input type="checkbox" name="slots[]" value="<?php echo $value; ?>">
                                            </label>
                                        </td>
                            <?php
                                    } else {
                                        echo "<td>-</td>";
                                    }
                                }
                            endfor; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php
    return ob_get_clean();
}
?>