<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Logica per contare gli inviti (utile per il badge rosso sulla Dashboard)
$num_inviti = 0;
if (isset($_SESSION['id_utente'])) {
    require_once __DIR__ . '/setup.php'; 
    require_once __DIR__ . '/function.php'; 
    if (function_exists('getInvitiPendenti') && isset($cid)) {
        $inviti = getInvitiPendenti($cid, $_SESSION['id_utente']);
        $num_inviti = count($inviti);
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-light fixed-top bg-transparent py-3">
  <div class="container-xl bg-light shadow-sm rounded-3 px-4 py-2">
    <a class="navbar-brand d-flex" href="index.php">
      <img src="images/logo.png" alt="Logo" width="50px" class="rounded-pill">
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center gap-2">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>

        <?php if (isset($_SESSION['id_utente'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                Dashboard
                <?php if ($num_inviti > 0): ?>
                    <span class="badge bg-danger rounded-pill ms-1"><?php echo $num_inviti; ?></span>
                <?php endif; ?>
            </a>
          </li>
          
          <li class="nav-item">
            <a class="nav-link" href="profilo.php">Il mio Profilo</a>
          </li>
          
          <!-- NAVBAR RESPONSABILE -->
          <?php if (isset($_SESSION['is_responsabile']) && $_SESSION['is_responsabile']): ?>
            <li class="nav-item">
              <a class="nav-link" href="gestione_sale.php">Gestione Sale</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="gestione_prenotazioni.php">Gestione Prenotazioni</a>
            </li>
          <?php endif; ?>
          
          <!-- NAVBAR ADMIN -->
          <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <li class="nav-item">
              <a class="nav-link" href="admin_settori.php">Gestione Settori</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_utenti.php">Gestione Utenti</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_prenotazioni.php">Prenotazioni</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_dotazioni.php">Dotazioni</a>
            </li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
      
      <div class="d-block d-lg-none my-2" style="width: 100%; height: 1px; background-color: #ddd;"></div>


      <ul class="navbar-nav align-items-center justify-content-center flex-column flex-lg-row gap-3 mt-3 mt-lg-0">
        <?php if (isset($_SESSION['id_utente'])): ?>
          <li class="nav-item">
              <span class="navbar-text me-2">Ciao, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong></span>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-danger btn-sm mb-2 mb-lg-0" href="backend/logout.php">Esci</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-outline-secondary btn-sm" href="login.php">Accedi</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-primary btn-sm mb-2 mb-lg-0" href="register.php">Registrati</a>
          </li>
        <?php endif; ?>
      </ul>
      
    </div>
  </div>
</nav>