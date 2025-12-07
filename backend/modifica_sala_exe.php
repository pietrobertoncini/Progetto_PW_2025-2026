<?php

if (session_status() == PHP_SESSION_NONE) session_start();

require_once '../common/setup.php';
require_once '../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['is_responsabile'])) {
    
    $dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
    $id_settore = $dati_utente['id_settore'];
    
    $old_nome = $_POST['old_nome_sala'];
    $new_nome = trim($_POST['nome_sala']);
    $capienza = (int)$_POST['capienza_max'];
    $dotazioni = isset($_POST['dotazioni']) ? $_POST['dotazioni'] : [];

    try {
        $cid->begin_transaction();

        // Aggiorna Sala (Nome e Capienza)
        // Grazie al CASCADE nel DB, se cambia il nome si aggiornano anche prenotazioni e dotazioni
        $sql = "UPDATE SALA SET nome_sala = ?, capienza_max = ? WHERE id_settore = ? AND nome_sala = ?";
        $stmt = $cid->prepare($sql);
        $stmt->bind_param("siis", $new_nome, $capienza, $id_settore, $old_nome);
        $stmt->execute();

        // Aggiorna Dotazioni
        // Cancelliamo le vecchie associazioni (usando il NUOVO nome sala, perché è appena stato aggiornato)
        $sql_del = "DELETE FROM SALA_DOTAZIONE WHERE id_settore = ? AND nome_sala = ?";
        $stmt_del = $cid->prepare($sql_del);
        $stmt_del->bind_param("is", $id_settore, $new_nome);
        $stmt_del->execute();
        
        // Reinseriamo quelle selezionate
        if (!empty($dotazioni)) {
            $sql_dot = "INSERT INTO SALA_DOTAZIONE (id_settore, nome_sala, id_dotazione) VALUES (?, ?, ?)";
            $stmt_dot = $cid->prepare($sql_dot);
            foreach ($dotazioni as $id_dot) {
                $stmt_dot->bind_param("isi", $id_settore, $new_nome, $id_dot);
                $stmt_dot->execute();
            }
        }

        $cid->commit();
        header("Location: ../gestione_sale.php?msg=Sala aggiornata con successo!");

    } catch (Exception $e) {
        $cid->rollback();
        header("Location: ../modifica_sala.php?nome=".urlencode($old_nome)."&error=Errore: " . $e->getMessage());
    }
} else {
    header("Location: ../dashboard.php");
}
?>