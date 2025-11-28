<?php
// backend/prenota_exe.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../common/setup.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_utente'])) {
    
    $id_utente = $_SESSION['id_utente'];
    $id_settore = $_POST['id_settore'];
    $nome_sala = $_POST['nome_sala'];
    $data = $_POST['data'];
    $ora_inizio = (int)$_POST['ora'];
    $durata = (int)$_POST['durata'];
    $attivita = trim($_POST['attivita']);
    
    // Recupera array invitati (potrebbe non esistere se non ne seleziona nessuno)
    $invitati = isset($_POST['invitati']) ? $_POST['invitati'] : [];

    $ora_fine = $ora_inizio + $durata;

    // VALIDAZIONI BASE
    if ($ora_inizio < 9 || $ora_fine > 24) {
        header("Location: ../prenota.php?error=Orario non valido (9-24).&sala=$nome_sala");
        exit;
    }

    // 1. CONTROLLO SOVRAPPOSIZIONI SALA
    $sql_check = "SELECT * FROM PRENOTAZIONE 
                  WHERE id_settore = ? AND nome_sala = ? AND data = ? 
                  AND ora < ? AND (ora + durata) > ?";
    
    $stmt = $cid->prepare($sql_check);
    $stmt->bind_param("issii", $id_settore, $nome_sala, $data, $ora_fine, $ora_inizio);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: ../prenota.php?error=Sala già occupata in quell'orario.&sala=$nome_sala");
        exit;
    }

    // 2. INSERIMENTO PRENOTAZIONE
    try {
        $cid->begin_transaction(); // Iniziamo una transazione per sicurezza

        $sql_insert = "INSERT INTO PRENOTAZIONE (id_settore, nome_sala, data, ora, durata, attivita, id_organizzatore) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_ins = $cid->prepare($sql_insert);
        $stmt_ins->bind_param("ississi", $id_settore, $nome_sala, $data, $ora_inizio, $durata, $attivita, $id_utente);
        $stmt_ins->execute();

        // 3. INSERIMENTO INVITI (Se ci sono utenti selezionati)
        if (!empty($invitati)) {
            $sql_invito = "INSERT INTO INVITO (id_utente, id_settore, nome_sala, data, ora, stato) VALUES (?, ?, ?, ?, ?, 'invitato')";
            $stmt_inv = $cid->prepare($sql_invito);

            foreach ($invitati as $id_invitato) {
                // Non puoi invitare te stesso (controllo extra)
                if ($id_invitato == $id_utente) continue;

                $stmt_inv->bind_param("iissi", $id_invitato, $id_settore, $nome_sala, $data, $ora_inizio);
                $stmt_inv->execute();
            }
        }

        $cid->commit(); // Conferma tutto

        header("Location: ../dashboard.php?msg=Prenotazione confermata e inviti spediti!");
        exit;

    } catch (Exception $e) {
        $cid->rollback(); // Annulla se qualcosa va storto
        header("Location: ../prenota.php?error=Errore: " . $e->getMessage());
        exit;
    }

} else {
    header("Location: ../login.php");
    exit;
}
?>