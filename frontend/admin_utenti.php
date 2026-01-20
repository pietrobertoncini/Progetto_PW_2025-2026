<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// SICUREZZA: SOLO ADMIN
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// RECUPERO DATI 
$elenco_utenti = getAllUtentiAdmin($cid);
?>

<!DOCTYPE html>
<html lang="it" class="no-js">
<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2>Gestione Utenti</h2>
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
            <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light small text-muted text-uppercase" style="position: sticky; top: 0; z-index: 1;">
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
                                            <div class="d-flex justify-content-end align-items-center gap-2">

                                                <?php if ($utente['is_responsabile']): ?>
                                                    <button type="button"
                                                        class="btn btn-outline-warning btn-sm rounded-pill fw-bold px-2"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalAzione"
                                                        data-bs-action="demote"
                                                        data-bs-id="<?php echo $utente['id_utente']; ?>"
                                                        data-bs-nome="<?php echo htmlspecialchars($utente['nome'] . ' ' . $utente['cognome'], ENT_QUOTES); ?>">
                                                        <i class="bi bi-arrow-down-circle"></i>
                                                        <span class="d-none d-lg-inline ms-1">Retrocedi</span>
                                                    </button>
                                                <?php else: ?>
                                                    <a href="<?php echo BASE_URL; ?>frontend/admin_promuovi.php?id=<?php echo $utente['id_utente']; ?>"
                                                        class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-2">
                                                        <i class="bi bi-arrow-up-circle"></i>
                                                        <span class="d-none d-lg-inline ms-1">Promuovi</span>
                                                    </a>
                                                <?php endif; ?>

                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm rounded-circle px-2"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalAzione"
                                                    data-bs-action="delete"
                                                    data-bs-id="<?php echo $utente['id_utente']; ?>"
                                                    data-bs-nome="<?php echo htmlspecialchars($utente['nome'] . ' ' . $utente['cognome'], ENT_QUOTES); ?>">
                                                    <i class="bi bi-trash3"></i>
                                                </button>

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

    </div>

    <div class="modal fade" id="modalAzione" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Conferma Azione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-0">
                    <p class="text-muted" id="modalMessage">Sei sicuro di voler procedere?</p>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Annulla</button>

                    <form action="<?php echo BASE_URL; ?>backend/admin_utenti_exe.php" method="POST">
                        <input type="hidden" name="action" id="inputAction" value="">
                        <input type="hidden" name="id_utente" id="inputId" value="">
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold" id="btnConfirm">Conferma</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include ROOT_PATH . '/common/footer.html'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestione Modale
            var modalAzione = document.getElementById('modalAzione');

            modalAzione.addEventListener('show.bs.modal', function(event) {
                // Bottone che ha attivato il modale
                var button = event.relatedTarget;

                // Estrai info dagli attributi data-bs-*
                var action = button.getAttribute('data-bs-action');
                var id = button.getAttribute('data-bs-id');
                var nome = button.getAttribute('data-bs-nome');

                // Elementi del modale da aggiornare
                var modalTitle = modalAzione.querySelector('.modal-title');
                var modalMessage = modalAzione.querySelector('#modalMessage');
                var btnConfirm = modalAzione.querySelector('#btnConfirm');
                var inputAction = modalAzione.querySelector('#inputAction');
                var inputId = modalAzione.querySelector('#inputId');

                // Aggiorna i campi hidden del form
                inputAction.value = action;
                inputId.value = id;

                // Personalizza interfaccia in base all'azione
                if (action === 'delete') {
                    
                    modalTitle.textContent = 'Elimina Utente';
                    modalTitle.className = 'modal-title fw-bold text-danger';
                    modalMessage.innerHTML = 'Stai per eliminare definitivamente <strong>' + nome + '</strong>.<br>Questa azione è irreversibile.';

                    btnConfirm.className = 'btn btn-danger rounded-pill px-4 fw-bold';
                    btnConfirm.textContent = 'Elimina';

                } else if (action === 'demote') {
                    
                    modalTitle.textContent = 'Retrocedi Responsabile';
                    modalTitle.className = 'modal-title fw-bold';
                    modalTitle.style.color = '#d68c00'; // Un giallo/arancio più scuro e leggibile

                    modalMessage.innerHTML = 'Vuoi togliere i privilegi di Responsabile a <strong>' + nome + '</strong>?';

                    // Bottone giallo con testo scuro per contrasto
                    btnConfirm.className = 'btn btn-warning text-dark rounded-pill px-4 fw-bold';
                    btnConfirm.textContent = 'Retrocedi';
                }
            });
        });
    </script>
</body>

</html>