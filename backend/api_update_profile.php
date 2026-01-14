<?php
header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

$risposta = ["status" => "ko", "msg" => "Errore sconosciuto"];

if (!isset($_SESSION['id_utente'])) {
    $risposta["msg"] = "Sessione scaduta. Effettua il login.";
    echo json_encode($risposta);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_utente = $_SESSION['id_utente'];
    
    // Recupero dati
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $data_nascita = $_POST['data_nascita'] ?? '';

    // Logica FOTO
    $queryOld = "SELECT foto FROM UTENTE WHERE id_utente = ?";
    $stmtOld = $cid->prepare($queryOld);
    $stmtOld->bind_param("i", $id_utente);
    $stmtOld->execute();
    $resOld = $stmtOld->get_result();
    $rowOld = $resOld->fetch_assoc();
    $vecchiaFoto = $rowOld['foto'] ?? null;

    // Provo upload nuova foto
    $nuovaFoto = uploadFotoProfilo($_FILES['foto'] ?? null);

    // Decido quale tenere
    if ($nuovaFoto != null) {
        $percorsoFinale = $nuovaFoto;
        rimuoviVecchiaFoto($vecchiaFoto); // Cancello la vecchia
    } else {
        $percorsoFinale = $vecchiaFoto; // Tengo la vecchia
    }

    try {
        modificaUtente($cid, $id_utente, $nome, $cognome, $email, $data_nascita, $percorsoFinale);
        
        // Aggiorno sessione
        $_SESSION['nome'] = $nome;
        
        $risposta["status"] = "ok";
        $risposta["msg"] = "Profilo aggiornato con successo!";
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $risposta["msg"] = "Email già utilizzata da un altro utente.";
        } else {
            $risposta["msg"] = "Errore DB: " . $e->getMessage();
        }
    } catch (Exception $e) {
        $risposta["msg"] = "Errore: " . $e->getMessage();
    }
}

echo json_encode($risposta);
exit;
?>