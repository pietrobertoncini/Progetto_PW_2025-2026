<?php
// backend/modifica_prenotazione_exe.php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../common/setup.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['is_responsabile'])) {
    
    // --- DATI VECCHI (IDENTIFICATIVI) ---
    $old_nome_sala = $_POST['old_nome_sala'];
    $old_data = $_POST['old_data'];
    $old_ora = (int)$_POST['old_ora'];
    $id_settore = (int)$_POST['id_settore'];

    // --- DATI NUOVI (DA SALVARE) ---
    $new_data = $_POST['new_data'];
    $new_ora = (int)$_POST['new_ora'];
    $new_durata = (int)$_POST['new_durata'];
    $new_attivita = trim($_POST['new_attivita']);

    $new_ora_fine = $new_ora + $new_durata;

    // VALIDAZIONE ORARIO
    if ($new_ora < 9 || $new_ora_fine > 24) {
        header("Location: ../modifica_prenotazione.php?error=Orario non valido (9-24)&sala=".urlencode($old_nome_sala)."&data=$old_data&ora=$old_ora");
        exit;
    }

    // --- 1. CONTROLLO CONFLITTI "INTELLIGENTE" ---
    // Cerchiamo prenotazioni che si sovrappongono.
    // IMPORTANTE: Dobbiamo ignorare la prenotazione che stiamo modificando.
    // Invece di "AND NOT", usiamo "AND (data diversa OR ora diversa)".
    // Se data e ora sono uguali a quelle vecchie, la riga viene ignorata.
    
    $sql_check = "SELECT * FROM PRENOTAZIONE 
                  WHERE id_settore = ? 
                  AND nome_sala = ? 
                  AND data = ? 
                  AND ora < ? 
                  AND (ora + durata) > ?
                  AND (data != ? OR ora != ?)"; // <--- LOGICA PIÙ ROBUSTA

    $stmt = $cid->prepare($sql_check);
    
    // Mappa parametri:
    // 1. id_settore (i)
    // 2. nome_sala (s) - La sala dove stiamo cercando spazio
    // 3. new_data (s) - La data dove stiamo cercando spazio
    // 4. new_ora_fine (i) - Serve per l'overlap
    // 5. new_ora (i) - Serve per l'overlap
    // 6. old_data (s) - Per l'esclusione (diverso da vecchio)
    // 7. old_ora (i) - Per l'esclusione (diverso da vecchio)
    
    $stmt->bind_param("issisii", $id_settore, $old_nome_sala, $new_data, $new_ora_fine, $new_ora, $old_data, $old_ora);
    
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // Trovato un conflitto REALE (non noi stessi)
        header("Location: ../modifica_prenotazione.php?error=Conflitto! Sala già occupata nel nuovo orario.&sala=".urlencode($old_nome_sala)."&data=$old_data&ora=$old_ora");
        exit;
    }

    // --- 2. AGGIORNAMENTO DB ---
    try {
        // Aggiorniamo la prenotazione. 
        // Nota: Gli inviti collegati si aggiornano da soli grazie alle Foreign Key (ON UPDATE CASCADE) nel DB.
        $sql = "UPDATE PRENOTAZIONE 
                SET data = ?, ora = ?, durata = ?, attivita = ? 
                WHERE id_settore = ? AND nome_sala = ? AND data = ? AND ora = ?";
        
        $stmt_up = $cid->prepare($sql);
        
        // Parametri Update: "siisissi"
        $stmt_up->bind_param("siisissi", $new_data, $new_ora, $new_durata, $new_attivita, $id_settore, $old_nome_sala, $old_data, $old_ora);
        
        if ($stmt_up->execute()) {
            header("Location: ../gestione_prenotazioni.php?msg=Prenotazione aggiornata con successo!");
        } else {
            throw new Exception("Errore durante l'aggiornamento.");
        }

    } catch (Exception $e) {
        // Se c'è un errore (es. si tenta di spostare su una chiave primaria già esistente, cosa rara qui grazie al check), torniamo indietro
        header("Location: ../modifica_prenotazione.php?error=".$e->getMessage()."&sala=".urlencode($old_nome_sala)."&data=$old_data&ora=$old_ora");
    }

} else {
    header("Location: ../index.php");
}
?>