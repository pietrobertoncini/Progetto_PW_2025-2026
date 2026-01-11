<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';

// SICUREZZA 
if (!isset($_SESSION['id_utente']) || empty($_SESSION['is_admin'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// LOGICA MODIFICA (Recupero dati se ?edit=ID)
$dotazione_da_modificare = null;
$is_editing = false;

if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $stmt = $cid->prepare("SELECT * FROM DOTAZIONE_DI_SUPPORTO WHERE id_dotazione = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($dotazione_da_modificare = $result->fetch_assoc()) {
        $is_editing = true;
    }
    $stmt->close();
}

// RECUPERO LISTA 
$elenco_dotazioni = getAllDotazioni($cid);
?>

<!DOCTYPE html>
<html lang="it" class="h-100">
<?php require ROOT_PATH . "/common/header.php" ?>

<body class="d-flex flex-column h-100">
    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="flex-shrink-0 container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Catalogo Dotazioni</h2>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success shadow-sm rounded-4 mb-4"><i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger shadow-sm rounded-4 mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <div class="row g-4">

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header border-0 py-3 fw-bold <?php echo $is_editing ? 'bg-warning text-dark' : 'bg-primary text-white'; ?>">
                        <i class="bi <?php echo $is_editing ? 'bi-pencil-square' : 'bi-plus-lg'; ?> me-2"></i>
                        <?php echo $is_editing ? 'Modifica Dotazione' : 'Nuova Dotazione'; ?>
                    </div>
                    <div class="card-body p-4">

                        <form action="<?php echo BASE_URL; ?>backend/admin_dotazioni_exe.php" method="POST">
                            <input type="hidden" name="action" value="<?php echo $is_editing ? 'update' : 'create'; ?>">

                            <?php if ($is_editing): ?>
                                <input type="hidden" name="id_dotazione" value="<?php echo $dotazione_da_modificare['id_dotazione']; ?>">
                            <?php endif; ?>

                            <div class="mb-4">
                                <label for="tipo" class="form-label fw-bold small text-muted">Nome Dotazione</label>
                                <input type="text" class="form-control rounded-3" id="tipo" name="tipo"
                                    placeholder="Es. Pianoforte, Proiettore..." required
                                    value="<?php echo $is_editing ? htmlspecialchars($dotazione_da_modificare['tipo']) : ''; ?>">
                                <div class="form-text small">
                                    Questo nome apparirà nella lista di selezione per i responsabili delle sale.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn <?php echo $is_editing ? 'btn-warning text-dark' : 'btn-primary'; ?> btn-lg rounded-pill fw-bold shadow-sm">
                                    <i class="bi <?php echo $is_editing ? 'bi-save' : 'bi-plus-lg'; ?> me-2"></i>
                                    <?php echo $is_editing ? 'Salva Modifiche' : 'Aggiungi al Catalogo'; ?>
                                </button>

                                <?php if ($is_editing): ?>
                                    <a href="<?php echo BASE_URL; ?>frontend/admin_dotazioni.php" class="btn btn-outline-secondary rounded-pill">Annulla</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 fw-bold text-muted border-bottom-0">
                        Dotazioni Disponibili (<?php echo count($elenco_dotazioni); ?>)
                    </div>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-muted text-uppercase" style="position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th class="ps-4" style="width: 70%;">Tipologia</th>
                                    <th class="text-end pe-4">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php if (count($elenco_dotazioni) > 0): ?>
                                    <?php foreach ($elenco_dotazioni as $d): ?>
                                        <tr class="<?php echo ($is_editing && $d['id_dotazione'] == $id_edit) ? 'table-warning' : ''; ?>">
                                            <td class="ps-4 fw-bold text-dark">
                                                <?php echo htmlspecialchars($d['tipo']); ?>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="<?php echo BASE_URL; ?>frontend/admin_dotazioni.php?edit=<?php echo $d['id_dotazione']; ?>"
                                                        class="btn btn-outline-warning btn-sm rounded-pill" title="Modifica">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>

                                                    <form action="<?php echo BASE_URL; ?>backend/admin_dotazioni_exe.php" method="POST" onsubmit="return confirm('Vuoi eliminare \'<?php echo htmlspecialchars($d['tipo']); ?>\' dal catalogo?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id_dotazione" value="<?php echo $d['id_dotazione']; ?>">
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
                                        <td colspan="2" class="text-center py-4 text-muted">Nessuna dotazione definita.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include ROOT_PATH . '/common/footer.html'; ?>
</body>

</html>