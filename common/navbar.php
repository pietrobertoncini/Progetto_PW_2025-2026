<?php

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

$num_inviti = 0;

if (isset($_SESSION['id_utente'])) {
  require_once __DIR__ . '/setup.php';
  require_once __DIR__ . '/function.php';

  if (isset($cid)) {
    try {
      // Contiamo solo gli Inviti in attesa (Per tutti)
      if (function_exists('getInvitiPendenti')) {
        $inviti = getInvitiPendenti($cid, $_SESSION['id_utente']);
        $num_inviti = count($inviti);
      }
    } catch (Exception $e) {
      $num_inviti = 0;
    }
  }
}
?>

<nav class="navbar navbar-expand-lg navbar-light fixed-top bg-transparent py-3">
  <div class="container-xl bg-light shadow-sm rounded-4 px-4 py-2">
    <a class="navbar-brand d-flex" href="<?php echo BASE_URL; ?>index.php">
      <img src="<?php echo BASE_URL; ?>images/logo.png" alt="Logo" width="50px" class="rounded-pill">
    </a>

    <div class="d-flex align-items-center d-lg-none">
      <!-- Pallino rosso che lampeggia -->
      <?php if ($num_inviti > 0): ?>
        <a href="<?php echo BASE_URL; ?>frontend/inviti.php" class="me-2 d-flex align-items-center text-decoration-none">
          <span class="bg-danger rounded-circle" style="width: 12px; height: 12px; border: 2px solid white; display: block; box-shadow: 0 0 4px rgba(220,53,69,0.5);"></span>
        </a>
      <?php endif; ?>

      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
    </div>

    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">

      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center gap-2">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Home</a>
        </li>

        <?php if (isset($_SESSION['id_utente'])): ?>

          <?php if (!empty($_SESSION['is_responsabile']) || !empty($_SESSION['is_admin'])): ?>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle position-relative d-inline-flex align-items-center justify-content-center" href="#" id="dropVisualizza" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Visualizza
                <!-- RESPONSABILE -->
                <?php if (!empty($_SESSION['is_responsabile']) && $num_inviti > 0): ?>
                  <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle d-none d-lg-block"></span>
                  <span class="bg-danger rounded-circle ms-2 d-inline-block d-lg-none" style="width: 9px; height: 9px; border: 1px solid white;"></span>
                <?php endif; ?>
              </a>
              <ul class="dropdown-menu border-0 shadow" aria-labelledby="dropVisualizza">

                <?php if (!empty($_SESSION['is_responsabile'])): ?>
                  <li>
                    <a class="dropdown-item d-flex justify-content-between align-items-center" href="<?php echo BASE_URL; ?>frontend/inviti.php">
                      Inviti
                      <?php if ($num_inviti > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?php echo $num_inviti; ?></span>
                      <?php endif; ?>
                    </a>
                  </li>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/impegni.php">I Miei Impegni</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/visualizza_prenotazioni.php">Prenotazioni</a></li>
                <?php endif; ?>
                <!-- ADMIN -->
                <?php if (!empty($_SESSION['is_admin'])): ?>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/admin_prenotazioni.php">Prenotazioni</a></li>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/admin_utenti.php">Utenti</a></li>
                <?php endif; ?>
              </ul>
            </li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="dropGestisci" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Gestisci
              </a>
              <ul class="dropdown-menu border-0 shadow" aria-labelledby="dropGestisci">
                <!-- RESPONSABILE -->
                <?php if (!empty($_SESSION['is_responsabile'])): ?>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/gestione_prenotazioni.php">Le Mie Prenotazioni</a></li>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/resp_dotazioni.php">Dotazioni Sale</a></li>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/risposte_inviti.php">Stato Inviti</a></li>
                <?php endif; ?>
                <!-- ADMIN -->
                <?php if (!empty($_SESSION['is_admin'])): ?>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/admin_settori.php">Settori</a></li>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/admin_sale.php">Sale</a></li>
                  <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>frontend/admin_dotazioni.php">Dotazioni</a></li>
                <?php endif; ?>
              </ul>
            </li>
            <!-- UTENTE BASE -->
          <?php else: ?>
            <li class="nav-item">
              <a class="nav-link position-relative d-inline-flex align-items-center" href="<?php echo BASE_URL; ?>frontend/inviti.php">
                Inviti
                <?php if ($num_inviti > 0): ?>
                  <span class="badge bg-danger rounded-pill ms-1"><?php echo $num_inviti; ?></span>
                <?php endif; ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo BASE_URL; ?>frontend/impegni.php">Impegni</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo BASE_URL; ?>frontend/visualizza_prenotazioni.php">Prenotazioni</a>
            </li>
          <?php endif; ?>

          <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>frontend/profilo.php">Profilo</a>
          </li>
        <?php endif; ?>
      </ul>

      <div class="d-block d-lg-none my-2" style="width: 100%; height: 1px; background-color: #ddd;"></div>

      <ul class="navbar-nav align-items-center justify-content-center flex-column flex-lg-row gap-3 mt-3 mt-lg-0">
        <?php if (isset($_SESSION['id_utente'])): ?>
          <li class="nav-item">
            <span class="navbar-text me-2 small">Ciao, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong></span>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-danger btn-sm mb-2 mb-lg-0 rounded-pill px-3" href="<?php echo BASE_URL; ?>backend/logout.php">Esci</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="<?php echo BASE_URL; ?>frontend/login.php">Accedi</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-primary btn-sm mb-2 mb-lg-0 rounded-pill px-3" href="<?php echo BASE_URL; ?>frontend/register.php">Registrati</a>
          </li>
        <?php endif; ?>
      </ul>

    </div>
  </div>
</nav>