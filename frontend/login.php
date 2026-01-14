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

    <script>
        // Funzione standard dalle slide per gestire lo stato HTTP
        function checkStatus(response) {
            if (!response.ok) {
                throw Error("Errore nella richiesta: " + response.statusText);
            }
            return response;
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault(); // 1. Blocca il ricaricamento della pagina

            const msgDiv = document.getElementById('messaggioAjax');
            const formData = new FormData(this); // Raccoglie automaticamente i dati del form

            // 2. Chiamata FETCH
            fetch('../backend/api_login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(checkStatus) // Controlla se il server risponde 200 OK
                .then(resp => resp.json()) // Decodifica il JSON
                .then(data => {
                    // 3. Gestione della risposta logica
                    if (data.status === 'ok') {
                        // Successo: mostriamo verde e reindirizziamo
                        msgDiv.className = 'alert alert-success';
                        msgDiv.textContent = data.msg;
                        msgDiv.classList.remove('d-none');

                        // Aspettiamo un secondo e andiamo alla home
                        setTimeout(() => {
                            window.location.href = '../index.php';
                        }, 1000);
                    } else {
                        // Errore: mostriamo rosso e restiamo qui
                        msgDiv.className = 'alert alert-danger';
                        msgDiv.textContent = data.msg;
                        msgDiv.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    msgDiv.className = 'alert alert-danger';
                    msgDiv.textContent = "Errore di comunicazione con il server.";
                    msgDiv.classList.remove('d-none');
                });
        });
    </script>
    <?php
    require ROOT_PATH . "/common/footer.html";
    ?>

</body>

</html>