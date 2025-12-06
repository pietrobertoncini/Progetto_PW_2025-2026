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
    $invitati = isset($_POST['invitati']) ? $_POST['invitati'] : [];

    $ora_fine = $ora_inizio + $durata;

    // 1. CONTROLLO SOVRAPPOSIZIONI
    $sql_check = "SELECT * FROM PRENOTAZIONE 
                  WHERE id_settore = ? AND nome_sala = ? AND data = ? 
                  AND ora < ? AND (ora + durata) > ?";
    
    $stmt = $cid->prepare($sql_check);
    $stmt->bind_param("issii", $id_settore, $nome_sala, $data, $ora_fine, $ora_inizio);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: ../prenota.php?error=Sala già occupata in quell'orario.&sala=" . urlencode($nome_sala) . "&week=" . $data);
        exit;
    }

    // 2. INSERIMENTO
    try {
        $cid->begin_transaction();

        $sql_insert = "INSERT INTO PRENOTAZIONE (id_settore, nome_sala, data, ora, durata, attivita, id_organizzatore) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_ins = $cid->prepare($sql_insert);
        $stmt_ins->bind_param("ississi", $id_settore, $nome_sala, $data, $ora_inizio, $durata, $attivita, $id_utente);
        $stmt_ins->execute();

        // 3. INVITI
        if (!empty($invitati)) {
            $sql_invito = "INSERT INTO INVITO (id_utente, id_settore, nome_sala, data, ora, stato) VALUES (?, ?, ?, ?, ?, 'invitato')";
            $stmt_inv = $cid->prepare($sql_invito);

            foreach ($invitati as $id_invitato) {
                if ($id_invitato == $id_utente) continue;
                $stmt_inv->bind_param("iissi", $id_invitato, $id_settore, $nome_sala, $data, $ora_inizio);
                $stmt_inv->execute();
            }
        }

        $cid->commit();
        
        // --- MODIFICA FONDAMENTALE ---
        // Prima puntava a dashboard.php, ora punta a gestione_prenotazioni.php
        header("Location: ../gestione_prenotazioni.php?msg=Prenotazione confermata!");
        exit;

    } catch (Exception $e) {
        $cid->rollback();
        header("Location: ../prenota.php?error=Errore: " . $e->getMessage());
        exit;
    }

} else {
    header("Location: ../index.php");
    exit;
}
?>