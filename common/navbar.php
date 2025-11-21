<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-sm navbar-light bg-light border-bottom">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
      <img src="images/logo.png" alt="Logo" width="50px" class="rounded-pill">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>
        <?php if (isset($_SESSION['id_utente'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="profilo.php">Profilo</a>
          </li>
          <?php if ($_SESSION['is_responsabile'] == true): ?>
            <li class="nav-item">
              <a class="nav-link fw-bold text-primary" href="gestione_prenotazioni.php">Gestione Sale</a>
            </li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav">
        <?php if (isset($_SESSION['id_utente'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Ciao, <?php echo htmlspecialchars($_SESSION['nome']); ?>!
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="profilo.php">Mio Profilo</a></li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li><a class="dropdown-item" href="backend/logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-outline-secondary btn-md me-1" href="login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-primary btn-md" href="register.php">Registrati</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>