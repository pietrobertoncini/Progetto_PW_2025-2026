<?php
// backend/nuova_sala_exe.php
if (session_status() == PHP_SESSION_NONE) session_start();
require_once '../common/setup.php';
require_once '../common/function.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['is_responsabile'])) {
    
    $dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
    $id_settore = $dati_utente['id_settore'];
    
    $nome_sala = trim($_POST['nome_sala']);
    $capienza = (int)$_POST['capienza_max'];
    $dotazioni = isset($_POST['dotazioni']) ? $_POST['dotazioni'] : [];

    try {
        $cid->begin_transaction();

        // 1. Inserimento Sala
        $sql = "INSERT INTO SALA (id_settore, nome_sala, capienza_max) VALUES (?, ?, ?)";
        $stmt = $cid->prepare($sql);
        $stmt->bind_param("isi", $id_settore, $nome_sala, $capienza);
        $stmt->execute();

        // 2. Inserimento Dotazioni (se selezionate)
        if (!empty($dotazioni)) {
            $sql_dot = "INSERT INTO SALA_DOTAZIONE (id_settore, nome_sala, id_dotazione) VALUES (?, ?, ?)";
            $stmt_dot = $cid->prepare($sql_dot);
            foreach ($dotazioni as $id_dot) {
                $stmt_dot->bind_param("isi", $id_settore, $nome_sala, $id_dot);
                $stmt_dot->execute();
            }
        }

        $cid->commit();
        header("Location: ../gestione_sale.php?msg=Sala creata con successo!");

    } catch (mysqli_sql_exception $e) {
        $cid->rollback();
        if ($e->getCode() == 1062) { // Duplicate entry
            header("Location: ../nuova_sala.php?error=Esiste già una sala con questo nome nel tuo settore.");
        } else {
            header("Location: ../nuova_sala.php?error=Errore DB: " . $e->getMessage());
        }
    }
} else {
    header("Location: ../dashboard.php");
}
?>