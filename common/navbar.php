<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-light fixed-top bg-transparent py-3">
  <div class="container-xl bg-light shadow-sm rounded-3 px-4 py-2">
    <a class="navbar-brand " href="index.php">
      <img src="images/logo.png" alt="Logo" width="50px" class="rounded-pill">
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
        <li class="nav-item">
          <a class="nav-link px-3" href="index.php">Home</a>
        </li>
        <?php if (isset($_SESSION['id_utente'])): ?>
          <li class="nav-item">
            <a class="nav-link px-3" href="dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link px-3" href="profilo.php">Profilo</a>
          </li>
          <?php if ($_SESSION['is_responsabile'] == true): ?>
            <li class="nav-item">
              <a class="nav-link px-3" href="gestione_prenotazioni.php">Gestione Sale</a>
            </li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav text-center">
        <?php if (isset($_SESSION['id_utente'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle px-3" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Ciao, <?php echo htmlspecialchars($_SESSION['nome']); ?>!
            </a>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="profilo.php">Mio Profilo</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item text-danger" href="backend/logout.php">Esci</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item d-flex gap-2 justify-content-center mt-3 mt-lg-0">
            <a class="btn btn-outline-secondary rounded px-4" href="login.php">Accedi</a>
            <a class="btn btn-primary rounded px-4" href="register.php">Registrati</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
