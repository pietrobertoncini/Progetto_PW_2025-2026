<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// SICUREZZA: SOLO ADMIN
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// LOGICA MODIFICA
$sala_edit = null;
$is_editing = false;

if (isset($_GET['edit']) && isset($_GET['settore'])) {
    $nome_edit = urldecode($_GET['edit']);
    $settore_edit = (int)$_GET['settore'];

    // Recuperiamo la singola sala
    $sala_edit = getSalaById($cid, $settore_edit, $nome_edit);
    if ($sala_edit) {
        $is_editing = true;
    }
}


// RECUPERO DATI
$sale = getAllSaleGlobal($cid);
$lista_settori = getAllSettoriAdmin($cid);
?>

<!DOCTYPE html>
<html lang="it" class="no-js">
<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column h-100">

    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestione Sale</h2>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success shadow-sm rounded-4 mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger shadow-sm rounded-4 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header border-0 py-3 fw-bold <?php echo $is_editing ? 'bg-warning text-dark' : 'bg-primary text-white'; ?>">
                        <i class="bi <?php echo $is_editing ? 'bi-pencil-square' : 'bi-plus-lg'; ?> me-2"></i>
                        <?php echo $is_editing ? 'Modifica Sala' : 'Nuova Sala'; ?>
                    </div>

                    <div class="card-body p-4">
                        <form action="<?php echo BASE_URL; ?>backend/admin_sale_exe.php" method="POST">
                            <input type="hidden" name="action" value="<?php echo $is_editing ? 'update' : 'create'; ?>">

                            <?php if ($is_editing): ?>
                                <input type="hidden" name="old_nome_sala" value="<?php echo htmlspecialchars($sala_edit['nome_sala']); ?>">
                                <input type="hidden" name="old_id_settore" value="<?php echo $sala_edit['id_settore']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Nome Sala</label>
                                <input type="text" class="form-control rounded-3" name="nome_sala"
                                    placeholder="Es. Aula Magna" required
                                    value="<?php echo $is_editing ? htmlspecialchars($sala_edit['nome_sala']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Capienza</label>
                                <input type="number" class="form-control rounded-3" name="capienza_max"
                                    min="1" required
                                    value="<?php echo $is_editing ? $sala_edit['capienza_max'] : ''; ?>">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted">Settore di Appartenenza</label>
                                <select class="form-select rounded-3" name="id_settore" required>
                                    <option value="" disabled <?php echo !$is_editing ? 'selected' : ''; ?>>-- Seleziona --</option>
                                    <?php foreach ($lista_settori as $sett): ?>
                                        <option value="<?php echo $sett['id_settore']; ?>"
                                            <?php echo ($is_editing && $sala_edit['id_settore'] == $sett['id_settore']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($sett['nome']); ?> (<?php echo ucfirst($sett['tipo']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-lg rounded-pill fw-bold shadow-sm <?php echo $is_editing ? 'btn-warning text-dark' : 'btn-primary'; ?>">
                                <i class="bi <?php echo $is_editing ? 'bi-save' : 'bi-plus-lg'; ?> me-2"></i>    
                                <?php echo $is_editing ? 'Salva Modifiche' : 'Crea Sala'; ?>
                                </button>

                                <?php if ($is_editing): ?>
                                    <a href="<?php echo BASE_URL; ?>frontend/admin_sale.php" class="btn btn-outline-secondary rounded-pill">Annulla</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 fw-bold text-muted border-bottom-0">
                        Elenco Sale Esistenti (<?php echo count($sale); ?>)
                    </div>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="ps-4">Settore</th>
                                    <th>Nome Sala</th>
                                    <th class="text-center">Capienza</th>
                                    <th class="text-end pe-4">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($sale) > 0): ?>
                                    <?php foreach ($sale as $s): ?>
                                        <tr class="<?php echo ($is_editing && $s['nome_sala'] == $nome_edit && $s['id_settore'] == $settore_edit) ? 'table-warning' : ''; ?>">
                                            <td class="ps-4">
                                                <?php
                                                $badge_color = match ($s['tipo']) {
                                                    'musica' => 'primary',
                                                    'teatro' => 'danger',
                                                    'ballo' => 'success',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?php echo $badge_color; ?> bg-opacity-10 text-<?php echo $badge_color; ?> border border-<?php echo $badge_color; ?>">
                                                    <?php echo htmlspecialchars($s['nome_settore']); ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-brand">
                                                <?php echo htmlspecialchars($s['nome_sala']); ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border rounded-pill">
                                                    <?php echo $s['capienza_max']; ?> posti
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="<?php echo BASE_URL; ?>frontend/admin_sale.php?edit=<?php echo urlencode($s['nome_sala']); ?>&settore=<?php echo $s['id_settore']; ?>"
                                                        class="btn btn-outline-warning btn-sm rounded-pill" title="Modifica">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="<?php echo BASE_URL; ?>backend/admin_sale_exe.php" method="POST" onsubmit="return confirm('Eliminare la sala \'<?php echo htmlspecialchars($s['nome_sala']); ?>\'?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="nome_sala" value="<?php echo htmlspecialchars($s['nome_sala']); ?>">
                                                        <input type="hidden" name="id_settore" value="<?php echo $s['id_settore']; ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle" title="Elimina">
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">Nessuna sala trovata.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require ROOT_PATH . "/common/footer.html"; ?>
</body>

</html>