<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["id_utente"])) {
    header("Location: login.php");
    exit;
}

require_once 'common/setup.php';
require_once 'common/function.php';

$id_utente_loggato = $_SESSION['id_utente'];

$dati_utente = datiUtenteCompleti($cid, $id_utente_loggato);

if (!$dati_utente) {
    die("Errore critico: Impossibile recuperare i dati del profilo. Contattare l'amministratore.");
}
?>

<!DOCTYPE html>
<html lang="it" class="h-100">
<?php require "common/header.html" ?>

<body>

    <?php include 'common/navbar.php'; ?>

    <div class="container pt-4 mt-4 mb-5">

        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div class="card shadow-sm border-0 p-4 rounded-3">
                    <div class="card-header bg-white border-0 text-center">
                        <h3 class="fw-bold" style="color: #7A5E4E;">Il Mio Profilo</h3>
                    </div>

                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <tbody>
                                    <tr class="border-bottom">
                                        <th scope="row" class="text-muted py-3" style="width: 35%;">Nome Completo</th>
                                        <td class="fw-bold py-3">
                                            <?php echo htmlspecialchars($dati_utente['nome'] . ' ' . $dati_utente['cognome']); ?>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th scope="row" class="text-muted py-3">Email</th>
                                        <td class="py-3">
                                            <?php echo htmlspecialchars($dati_utente['email']); ?>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th scope="row" class="text-muted py-3">Ruolo</th>
                                        <td class="py-3">
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars(ucfirst($dati_utente['ruolo'])); ?>
                                            </span>
                                            <?php if ($dati_utente['is_responsabile']): ?>
                                                <span class="badge bg-primary ms-1">Responsabile</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <th scope="row" class="text-muted py-3">Settore</th>
                                        <td class="fw-bold py-3" style="color: #7A5E4E;">
                                            <?php echo htmlspecialchars(ucfirst($dati_utente['nome_settore'])); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-muted py-3">Data di Nascita</th>
                                        <td class="py-3">
                                            <?php echo $dati_utente['data_nascita']; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="modifica_profilo.php" class="btn btn-outline-secondary px-4">
                                 Modifica Dati
                             </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'common/footer.html'; ?>
</body>

</html>