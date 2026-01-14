<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id_utente'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

require_once __DIR__ . '/../common/setup.php';
require_once __DIR__ . '/../common/function.php';
?>

<!DOCTYPE html>
<html lang="it">
<?php require ROOT_PATH . "/common/header.php" ?>

<body>

    <?php include ROOT_PATH . '/common/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Accedi</h2>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($_GET['error']); ?>
                            </div>
                        <?php endif; ?>

                        <div id="messaggioAjax" class="alert d-none"></div>

                        <form action="<?php echo BASE_URL; ?>backend/login_exe.php" method="POST" id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill">Accedi</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <p>Non hai un account? <a href="<?php echo BASE_URL; ?>frontend/register.php">Registrati ora</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>js/login.js"></script>
    <?php
    require ROOT_PATH . "/common/footer.html";
    ?>

</body>

</html>