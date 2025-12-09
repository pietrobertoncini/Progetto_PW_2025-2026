<?php

if (session_status() == PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';


if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_responsabile'])) {
    header('Location: index.php'); exit;
}

$nome_sala_url = isset($_GET['nome']) ? urldecode($_GET['nome']) : null;
if (!$nome_sala_url) die("Sala non specificata");

$dati_utente = datiUtenteCompleti($cid, $_SESSION['id_utente']);
$id_settore = $dati_utente['id_settore'];

// Recupero dati Sala
$sala = getSalaById($cid, $id_settore, $nome_sala_url);
if (!$sala) die("Sala non trovata.");

// Recupero dotazioni attuali della sala (per checkare i box)
$dotazioni_attuali = getDotazioniIdsBySala($cid, $id_settore, $nome_sala_url);
// Recupero tutte le dotazioni possibili
$tutte_dotazioni = getAllDotazioni($cid);

?>

<!DOCTYPE html>
<html lang="it">
<?php require ROOT_PATH . "/common/header.html" ?>
<body>
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-secondary bg-opacity-10 border-0 py-3 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 fw-bold text-dark">Modifica Sala</h4>
                        <a href="<?php echo BASE_URL; ?>frontend/gestione_sale.php" class="btn btn-sm btn-outline-secondary">Annulla</a>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                        <?php endif; ?>

                        <form action="<?php echo BASE_URL; ?>backend/modifica_sala_exe.php" method="POST">
                            <input type="hidden" name="old_nome_sala" value="<?php echo htmlspecialchars($sala['nome_sala']); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nome Sala</label>
                                <input type="text" class="form-control" name="nome_sala" value="<?php echo htmlspecialchars($sala['nome_sala']); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Capienza</label>
                                <input type="number" class="form-control" name="capienza_max" value="<?php echo $sala['capienza_max']; ?>" required>
                            </div>

                            <h5 class="fw-bold mb-3 fs-6 text-uppercase text-muted">Dotazioni</h5>
                            <div class="row g-2 mb-4">
                                <?php foreach ($tutte_dotazioni as $d): 
                                    $checked = in_array($d['id_dotazione'], $dotazioni_attuali) ? 'checked' : '';
                                ?>
                                    <div class="col-md-6">
                                        <div class="form-check border rounded p-2 bg-light">
                                            <input class="form-check-input ms-1" type="checkbox" name="dotazioni[]" value="<?php echo $d['id_dotazione']; ?>" id="d_<?php echo $d['id_dotazione']; ?>" <?php echo $checked; ?>>
                                            <label class="form-check-label ms-2" for="d_<?php echo $d['id_dotazione']; ?>">
                                                <?php echo htmlspecialchars($d['tipo']); ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">Salva Modifiche</button>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="bg-danger bg-opacity-10 p-3 rounded border border-danger border-opacity-25">
                            <h5 class="text-danger fw-bold fs-6">Zona Pericolo</h5>
                            <p class="small text-muted mb-3">Attenzione: se elimini la sala, verranno cancellate anche tutte le prenotazioni associate.</p>
                            <form action="<?php echo BASE_URL; ?>backend/elimina_sala.php" method="POST" onsubmit="return confirm('Sei DAVVERO sicuro? Questa azione eliminerà anche tutte le prenotazioni future per questa sala.');">
                                <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($sala['nome_sala']); ?>">
                                <button type="submit" class="btn btn-danger w-100">Elimina Sala</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>
</html>