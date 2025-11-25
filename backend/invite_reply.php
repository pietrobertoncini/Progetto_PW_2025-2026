<?php
// backend/invite_reply.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../common/setup.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['id_utente'])) {
    
    $id_utente = $_SESSION['id_utente'];
    $id_settore = $_POST['id_settore'];
    $nome_sala = $_POST['nome_sala'];
    $data = $_POST['data'];
    $ora = $_POST['ora'];
    
    // Accettato o Rifiutato
    $risposta = $_POST['risposta']; 
    
    // Se c'è una motivazione (per rifiuto/disdetta), la prendiamo, altrimenti NULL
    $motivazione = !empty($_POST['motivazione']) ? $_POST['motivazione'] : null;

    try {
        // Aggiorniamo lo stato dell'invito
        $sql = "UPDATE INVITO 
                SET stato = ?, motivazione = ?, data_risposta = NOW() 
                WHERE id_utente = ? AND id_settore = ? AND nome_sala = ? AND data = ? AND ora = ?";
        
        $stmt = $cid->prepare($sql);
        $stmt->bind_param("sssisss", $risposta, $motivazione, $id_utente, $id_settore, $nome_sala, $data, $ora);
        
        if ($stmt->execute()) {
             header("Location: ../dashboard.php?msg=Operazione completata");
        } else {
             header("Location: ../dashboard.php?error=Errore aggiornamento");
        }
        exit;

    } catch (Exception $e) {
        header("Location: ../dashboard.php?error=" . $e->getMessage());
        exit;
    }
} else {
    header("Location: ../dashboard.php");
    exit;
}
?>