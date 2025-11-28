<?php
// backend/modifica_prenotazione_exe.php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../common/setup.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['is_responsabile'])) {
    
    // Dati Vecchi (WHERE clause)
    $old_nome_sala = $_POST['old_nome_sala'];
    $old_data = $_POST['old_data'];
    $old_ora = (int)$_POST['old_ora'];
    $id_settore = (int)$_POST['id_settore'];

    // Dati Nuovi (SET clause)
    $new_data = $_POST['new_data'];
    $new_ora = (int)$_POST['new_ora'];
    $new_durata = (int)$_POST['new_durata'];
    $new_attivita = trim($_POST['new_attivita']);

    $new_ora_fine = $new_ora + $new_durata;

    // VALIDAZIONI
    if ($new_ora < 9 || $new_ora_fine > 24) {
        header("Location: ../modifica_prenotazione.php?error=Orario non valido (9-24)&sala=".urlencode($old_nome_sala)."&data=$old_data&ora=$old_ora");
        exit;
    }

    // CONTROLLO SOVRAPPOSIZIONI (Collision Detection)
    // Dobbiamo escludere noi stessi dalla ricerca! (NOT (nome_sala = old_nome...))
    $sql_check = "SELECT * FROM PRENOTAZIONE 
                  WHERE id_settore = ? AND nome_sala = ? AND data = ? 
                  AND ora < ? AND (ora + durata) > ?
                  AND NOT (data = ? AND ora = ?)"; // Escludiamo la prenotazione attuale

    $stmt = $cid->prepare($sql_check);
    // Parametri: settore, sala, new_data, new_fine, new_inizio, old_data, old_ora
    $stmt->bind_param("issiisii", $id_settore, $old_nome_sala, $new_data, $new_ora_fine, $new_ora, $old_data, $old_ora);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: ../modifica_prenotazione.php?error=Conflitto! Sala già occupata nel nuovo orario.&sala=".urlencode($old_nome_sala)."&data=$old_data&ora=$old_ora");
        exit;
    }

    // UPDATE
    try {
        // Grazie a ON UPDATE CASCADE nel DB, gli inviti si aggiorneranno da soli se cambio data/ora
        $sql = "UPDATE PRENOTAZIONE 
                SET data = ?, ora = ?, durata = ?, attivita = ? 
                WHERE id_settore = ? AND nome_sala = ? AND data = ? AND ora = ?";
        
        $stmt_up = $cid->prepare($sql);
        $stmt_up->bind_param("siisisss", $new_data, $new_ora, $new_durata, $new_attivita, $id_settore, $old_nome_sala, $old_data, $old_ora);
        
        if ($stmt_up->execute()) {
            header("Location: ../gestione_prenotazioni.php?msg=Prenotazione aggiornata con successo!");
        } else {
            throw new Exception("Errore durante l'aggiornamento.");
        }

    } catch (Exception $e) {
        header("Location: ../modifica_prenotazione.php?error=".$e->getMessage()."&sala=".urlencode($old_nome_sala)."&data=$old_data&ora=$old_ora");
    }

} else {
    header("Location: ../dashboard.php");
}
?>