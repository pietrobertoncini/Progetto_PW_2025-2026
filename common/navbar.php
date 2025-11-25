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
    <a class="navbar-brand " href="index.php">
      <img src="images/logo.png" alt="Logo" width="50px" class="rounded-pill">
    </a>
    
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav" style="display: flex !important;">
      
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center flex-row gap-3">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>

        <?php if (isset($_SESSION['id_utente'])): ?>
          <li class="nav-item">
            <a class="nav-link position-relative" href="dashboard.php">
                Dashboard
                <?php if ($num_inviti > 0): ?>
                    <span class="badge bg-danger rounded-pill ms-1"><?php echo $num_inviti; ?></span>
                <?php endif; ?>
            </a>
          </li>
          
          <li class="nav-item">
            <a class="nav-link" href="profilo.php">Il mio Profilo</a>
          </li>

          <?php if (isset($_SESSION['is_responsabile']) && $_SESSION['is_responsabile'] == true): ?>
            <li class="nav-item">
              <a class="nav-link" href="gestione_prenotazioni.php">Gestione Sale</a>
            </li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav align-items-center flex-row gap-2">
        <?php if (isset($_SESSION['id_utente'])): ?>
          <li class="nav-item">
              <span class="navbar-text me-2">Ciao, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong></span>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-danger btn-sm" href="backend/logout.php">Esci</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-outline-secondary btn-sm" href="login.php">Accedi</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-primary btn-sm" href="register.php">Registrati</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>