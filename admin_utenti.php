<?php
// admin_utenti.php (Versione Semplificata Finale)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'common/setup.php';
require_once 'common/function.php';

// --- SICUREZZA: SOLO ADMIN ---
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) {
    header("Location: index.php");
    exit;
}

// --- RECUPERO DATI ---
// Recupera TUTTI gli utenti
$elenco_utenti = getAllUtentiAdmin($cid);
?>

<!DOCTYPE html>
<html lang="it" class="h-100">
<?php require "common/header.html" ?>

<body class="d-flex flex-column h-100">
    <?php include 'common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Gestione Utenti</h2>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success shadow-sm rounded-4 mb-4"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger shadow-sm rounded-4 mb-4"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-3">
                <span class="fw-bold text-muted me-2"><i class="bi bi-list-ul"></i> Totale Utenti Registrati:</span>
                <span class="badge bg-dark rounded-pill px-3"><?php echo count($elenco_utenti); ?></span>
            </div>
        </div>


        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-muted text-uppercase">
                        <tr>
                            <th class="ps-4">Utente</th>
                            <th>Email</th>
                            <th>Ruolo & Stato</th>
                            <th>Settore</th>
                            <th class="text-end pe-4">Gestione</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php if (count($elenco_utenti) > 0): ?>
                            <?php foreach ($elenco_utenti as $utente): ?>
                                <tr class="<?php echo ($utente['id_utente'] == $_SESSION['id_utente']) ? 'table-info' : ''; ?>">
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark"><?php echo htmlspecialchars($utente['nome'] . ' ' . $utente['cognome']); ?></span>
                                        <?php if ($utente['id_utente'] == $_SESSION['id_utente']): ?><span class="badge bg-info text-dark small ms-1">Tu</span><?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($utente['email']); ?></td>
                                    <td>
                                        <?php if ($utente['is_admin']): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php elseif ($utente['is_responsabile']): ?>
                                            <span class="badge bg-primary">Responsabile</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-25 text-secondary"><?php echo ucfirst($utente['ruolo']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?php echo ($utente['is_admin']) ? '<span class="fst-italic text-danger">Globale</span>' : htmlspecialchars(ucfirst($utente['nome_settore'] ?? 'N/D')); ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php if (!$utente['is_admin'] && $utente['id_utente'] != $_SESSION['id_utente']): ?>
                                            <div class="d-flex justify-content-end gap-2">
                                                <?php if ($utente['is_responsabile']): ?>
                                                    <form action="backend/admin_utenti_exe.php" method="POST">
                                                        <input type="hidden" name="action" value="demote"><input type="hidden" name="id_utente" value="<?php echo $utente['id_utente']; ?>">
                                                        <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill fw-bold" onclick="return confirm('Retrocedere <?php echo htmlspecialchars($utente['nome']); ?>?');"><i class="bi bi-arrow-down-circle"></i> Retrocedi</button>
                                                    </form>
                                                <?php else: ?>
                                                    <a href="admin_promuovi.php?id=<?php echo $utente['id_utente']; ?>" class="btn btn-outline-primary btn-sm rounded-pill fw-bold"><i class="bi bi-arrow-up-circle"></i> Promuovi</a>
                                                <?php endif; ?>
                                                <form action="backend/admin_utenti_exe.php" method="POST">
                                                    <input type="hidden" name="action" value="delete"><input type="hidden" name="id_utente" value="<?php echo $utente['id_utente']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" onclick="return confirm('Eliminare <?php echo htmlspecialchars($utente['nome']); ?>?');"><i class="bi bi-trash3"></i></button>
                                                </form>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">Nessun utente trovato.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                &larr; Torna alla Dashboard
            </a>
        </div>
    </div>
    <?php include 'common/footer.html'; ?>
</body>

</html>