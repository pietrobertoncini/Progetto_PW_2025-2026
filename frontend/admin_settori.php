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

// LOGICA MODIFICA
// Se c'è il parametro 'edit' nell'URL, stiamo modificando un settore esistente
$settore_da_modificare = null;
$is_editing = false;

if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    // Cerchiamo i dati del settore da modificare
    $stmt = $cid->prepare("SELECT * FROM SETTORE WHERE id_settore = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($settore_da_modificare = $result->fetch_assoc()) {
        $is_editing = true;
    }
    $stmt->close();
}
// Recuperiamo tutti i settori per la tabella
$elenco_settori = getAllSettoriAdmin($cid);
?>

<!DOCTYPE html>
<html lang="it" class="no-js">

<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column">

    <?php include ROOT_PATH . '/common/navbar.php'; ?>
    <div class="flex-shrink-0 container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestione Settori</h2>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header border-0 py-3 fw-bold <?php echo $is_editing ? 'bg-warning text-dark' : 'bg-primary text-white'; ?>">
                        <i class="bi <?php echo $is_editing ? 'bi-pencil-square' : 'bi-plus-lg'; ?> me-2"></i>
                        <?php echo $is_editing ? 'Modifica Settore' : 'Nuovo Settore'; ?>
                    </div>

                    <div class="card-body p-4">

                        <form action="<?php echo BASE_URL; ?>backend/admin_settori_exe.php" method="POST">
                            <input type="hidden" name="action" value="<?php echo $is_editing ? 'update' : 'create'; ?>">

                            <?php if ($is_editing): ?>
                                <input type="hidden" name="id_settore" value="<?php echo $settore_da_modificare['id_settore']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="nome" class="form-label fw-bold small text-muted">Nome Settore</label>
                                <input type="text" class="form-control rounded-3" id="nome" name="nome"
                                    placeholder="Es. Dipartimento Jazz" required
                                    value="<?php echo $is_editing ? htmlspecialchars($settore_da_modificare['nome']) : ''; ?>">
                            </div>

                            <div class="mb-4">
                                <label for="tipo" class="form-label fw-bold small text-muted">Tipo (Musica, Teatro, Ballo)</label>
                                <select class="form-select rounded-3 cursor-pointer" id="tipo" name="tipo" required>
                                    <option value="" disabled <?php echo !$is_editing ? 'selected' : ''; ?>>-- Seleziona Tipo --</option>
                                    <option value="musica" <?php echo ($is_editing && $settore_da_modificare['tipo'] == 'musica') ? 'selected' : ''; ?>>Musica</option>
                                    <option value="teatro" <?php echo ($is_editing && $settore_da_modificare['tipo'] == 'teatro') ? 'selected' : ''; ?>>Teatro</option>
                                    <option value="ballo" <?php echo ($is_editing && $settore_da_modificare['tipo'] == 'ballo') ? 'selected' : ''; ?>>Ballo</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-lg rounded-pill fw-bold shadow-sm <?php echo $is_editing ? 'btn-warning text-dark' : 'btn-primary'; ?>">
                                    <i class="bi <?php echo $is_editing ? 'bi-save' : 'bi-plus-lg'; ?> me-2"></i>
                                    <?php echo $is_editing ? 'Salva Modifiche' : 'Crea Settore'; ?>
                                </button>

                                <?php if ($is_editing): ?>
                                    <a href="<?php echo BASE_URL; ?>frontend/admin_settori.php" class="btn btn-outline-secondary rounded-pill">Annulla</a>
                                <?php endif; ?>
                            </div>


                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 fw-bold text-muted border-bottom-0">
                        Elenco Settori Esistenti (<?php echo count($elenco_settori); ?>)
                    </div>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="ps-4">Nome Settore</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Iscritti</th>
                                    <th>Responsabile</th>
                                    <th class="text-end pe-4">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php if (count($elenco_settori) > 0): ?>
                                    <?php foreach ($elenco_settori as $settore): ?>
                                        <tr class="<?php echo ($is_editing && $settore['id_settore'] == $id_edit) ? 'table-warning' : ''; ?>">
                                            <td class="ps-4 fw-bold text-brand">
                                                <?php echo htmlspecialchars($settore['nome']); ?>
                                            </td>
                                            <td>
                                                <?php
                                                $badge_color = match ($settore['tipo']) {
                                                    'musica' => 'primary',
                                                    'teatro' => 'danger',
                                                    'ballo' => 'success',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?php echo $badge_color; ?> bg-opacity-10 text-<?php echo $badge_color; ?> border border-<?php echo $badge_color; ?>">
                                                    <?php echo ucfirst($settore['tipo']); ?>
                                                </span>
                                            </td>
                                            <td class="text-center fw-bold text-muted">
                                                <?php echo $settore['num_iscritti']; ?>
                                            </td>
                                            <td class="small text-muted">
                                                <?php if ($settore['id_responsabile']): ?>
                                                    <span class="fw-bold text-dark">
                                                        <?php echo htmlspecialchars($settore['nome_resp'] . ' ' . htmlspecialchars(substr($settore['cognome_resp'], 0, 1)) . '.'); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="fst-italic text-secondary">Non assegnato</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="<?php echo BASE_URL; ?>frontend/admin_settori.php?edit=<?php echo $settore['id_settore']; ?>"
                                                        class="btn btn-outline-warning btn-sm rounded-pill"
                                                        title="Modifica">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="<?php echo BASE_URL; ?>backend/admin_settori_exe.php" method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare il settore \'<?php echo htmlspecialchars($settore['nome']); ?>\'? Questa azione è irreversibile.');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id_settore" value="<?php echo $settore['id_settore']; ?>">
                                                        <button type="submit"
                                                            class="btn btn-outline-danger btn-sm rounded-pill"
                                                            title="Elimina"
                                                            <?php echo ($settore['num_iscritti'] > 0) ? 'disabled' : ''; ?>>
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Nessun settore trovato.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php include ROOT_PATH . '/common/footer.html' ?>
</body>

</html>