<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

// Logica per i contatori (Inviti e Impegni)
$num_inviti = 0;
$num_impegni = 0;

if (isset($_SESSION['id_utente'])) {
    require_once __DIR__ . '/setup.php'; 
    require_once __DIR__ . '/function.php'; 
    
    // Recuperiamo i conteggi se la connessione e le funzioni esistono
    if (isset($cid)) {
        try {
            if (function_exists('getInvitiPendenti')) {
                $inviti = getInvitiPendenti($cid, $_SESSION['id_utente']);
                $num_inviti = count($inviti);
            }
            if (function_exists('getImpegniFuturi')) {
                $impegni = getImpegniFuturi($cid, $_SESSION['id_utente']);
                $num_impegni = count($impegni);
            }
        } catch (Exception $e) {
            $num_inviti = 0;
            $num_impegni = 0;
        }
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-light fixed-top bg-transparent py-3">
  <div class="container-xl bg-light shadow-sm rounded-3 px-4 py-2">
    <a class="navbar-brand d-flex" href="index.php">
      <img src="images/logo.png" alt="Logo" width="50px" class="rounded-pill">
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
      
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center gap-2">
        <li class="nav-item">
          <a class="nav-link" href="index.php">Home</a>
        </li>

        <?php if (isset($_SESSION['id_utente'])): ?>
          
          <?php if (empty($_SESSION['is_admin'])): ?>
              
              <li class="nav-item">
                <a class="nav-link position-relative" href="inviti.php">
                    Inviti
                    <?php if ($num_inviti > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?php echo $num_inviti; ?></span>
                    <?php endif; ?>
                </a>
              </li>

              <li class="nav-item">
                <a class="nav-link position-relative" href="impegni.php">
                    Impegni
                    <?php if ($num_impegni > 0): ?>
                        <span class="badge bg-success rounded-pill ms-1"><?php echo $num_impegni; ?></span>
                    <?php endif; ?>
                </a>
              </li>

          <?php endif; ?>
          <li class="nav-item">
            <a class="nav-link" href="profilo.php">Profilo</a>
          </li>
          
          <?php if (isset($_SESSION['is_responsabile']) && $_SESSION['is_responsabile']): ?>
            <li class="nav-item">
              <a class="nav-link" href="gestione_prenotazioni.php">Gestione Prenotazioni</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="risposte_inviti.php">Esito Inviti</a>
            </li>
          <?php endif; ?>
          
          <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <li class="nav-item">
              <a class="nav-link" href="admin_settori.php">Settori</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_utenti.php">Utenti</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="gestione_sale.php">Sale</a>
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
              <span class="navbar-text me-2 small">Ciao, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong></span>
          </li>
          <li class="nav-item">
            <a class="btn btn-outline-danger btn-sm mb-2 mb-lg-0 rounded-pill px-3" href="backend/logout.php">Esci</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="login.php">Accedi</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-primary btn-sm mb-2 mb-lg-0 rounded-pill px-3" href="register.php">Registrati</a>
          </li>
        <?php endif; ?>
      </ul>
      
    </div>
  </div>
</nav>