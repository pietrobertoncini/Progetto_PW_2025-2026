<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Controllo di sicurezza
if (!isset($_SESSION['id_utente'])) {
    header('Location: login.php');
    exit;
}

// 2. Setup e Funzioni
require_once __DIR__ . '/common/setup.php';
require_once __DIR__ . '/common/function.php';

// 3. Recupero Dati
$inviti_pendenti = [];
$impegni_futuri = [];

if (isset($cid)) {
    if (function_exists('getInvitiPendenti')) {
        $inviti_pendenti = getInvitiPendenti($cid, $_SESSION['id_utente']);
    }
    if (function_exists('getImpegniFuturi')) {
        $impegni_futuri = getImpegniFuturi($cid, $_SESSION['id_utente']);
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<?php require "common/header.html" ?>

<body>

    <?php include 'common/navbar.php'; ?>

    <div class="container mt-5" style="padding-top: 60px;">
        
        <div class="mb-4">
            <h2>La tua Dashboard</h2>
            <p class="fs-5">
                Ciao, <strong class="text-brand"><?php echo htmlspecialchars(ucfirst($_SESSION['nome'])); ?></strong>!
            </p>
            <p class="text-muted">
                Il tuo ruolo è: 
                <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($_SESSION['ruolo'])); ?></span>
                
                <?php if (isset($_SESSION['is_responsabile']) && $_SESSION['is_responsabile']): ?>
                    <span class="badge bg-primary ms-1">Responsabile</span>
                <?php endif; ?>
            </p>
        </div>

        <?php if (isset($_SESSION['is_responsabile']) && $_SESSION['is_responsabile']): ?>
            <div class="alert alert-info shadow-sm mb-5 border-info" role="alert">
                <h5 class="alert-heading fw-bold">Area Gestione</h5>
                <p class="mb-0">
                    In qualità di Responsabile, puoi gestire le sale e organizzare le prove. 
                    Vai alla <a href="gestione_sale.php" class="alert-link fw-bold">gestione delle sale</a>.
                </p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm mb-5 border-0">
            <div class="card-header bg-warning bg-opacity-10 border-0 py-3">
                <h4 class="mb-0 fs-5 text-dark">📩 Inviti in Attesa (<?php echo count($inviti_pendenti); ?>)</h4>
            </div>
            <div class="card-body">
                <?php if (count($inviti_pendenti) > 0): ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Attività</th>
                                    <th>Luogo e Data</th>
                                    <th>Organizzatore</th>
                                    <th style="min-width: 320px;">La tua Risposta</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inviti_pendenti as $invito): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($invito['attivita']); ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($invito['nome_sala']); ?><br>
                                            <small class="text-muted">
                                                <?php echo date("d/m/Y", strtotime($invito['data'])); ?> ore <?php echo $invito['ora']; ?>:00
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($invito['nome_org'] . " " . $invito['cognome_org']); ?></td>
                                        <td>
                                            <div class="d-flex flex-column gap-2">
                                                <form action="backend/invite_reply.php" method="POST">
                                                    <input type="hidden" name="id_settore" value="<?php echo $invito['id_settore']; ?>">
                                                    <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($invito['nome_sala']); ?>">
                                                    <input type="hidden" name="data" value="<?php echo $invito['data']; ?>">
                                                    <input type="hidden" name="ora" value="<?php echo $invito['ora']; ?>">
                                                    <input type="hidden" name="risposta" value="accettato">
                                                    <button type="submit" class="btn btn-success btn-sm w-100">Accetta Invito</button>
                                                </form>

                                                <form action="backend/invite_reply.php" method="POST" class="d-flex gap-1">
                                                    <input type="hidden" name="id_settore" value="<?php echo $invito['id_settore']; ?>">
                                                    <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($invito['nome_sala']); ?>">
                                                    <input type="hidden" name="data" value="<?php echo $invito['data']; ?>">
                                                    <input type="hidden" name="ora" value="<?php echo $invito['ora']; ?>">
                                                    <input type="hidden" name="risposta" value="rifiutato">
                                                    
                                                    <input type="text" name="motivazione" class="form-control form-control-sm" placeholder="Motivo rifiuto..." required>
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Rifiuta</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0 py-2">Non hai nuovi inviti a cui rispondere al momento.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-success bg-opacity-10 border-0 py-3">
                <h4 class="mb-0 fs-5 text-dark">📅 I Tuoi Prossimi Impegni</h4>
            </div>
            <div class="card-body">
                <?php if (count($impegni_futuri) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Orario</th>
                                    <th>Sala</th>
                                    <th>Attività</th>
                                    <th>Gestione</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($impegni_futuri as $impegno): ?>
                                    <tr>
                                        <td><strong><?php echo date("d/m/Y", strtotime($impegno['data'])); ?></strong></td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo $impegno['ora']; ?>:00 - <?php echo $impegno['ora'] + $impegno['durata']; ?>:00
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($impegno['nome_sala']); ?></td>
                                        <td><?php echo htmlspecialchars($impegno['attivita']); ?></td>
                                        <td>
                                            <form action="backend/invite_reply.php" method="POST">
                                                <input type="hidden" name="id_settore" value="<?php echo $impegno['id_settore']; ?>">
                                                <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($impegno['nome_sala']); ?>">
                                                <input type="hidden" name="data" value="<?php echo $impegno['data']; ?>">
                                                <input type="hidden" name="ora" value="<?php echo $impegno['ora']; ?>">
                                                <input type="hidden" name="risposta" value="rifiutato">
                                                <input type="hidden" name="motivazione" value="Disdetta successiva dall'utente">
                                                
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Sei sicuro di voler disdire?');">Disdici</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="lead text-muted">Non hai impegni confermati in programma.</p>
                        <p class="small text-secondary">Attendi che un responsabile ti inviti a una prova.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <?php require "common/footer.html"; ?>
</body>

</html>