<nav class="navbar navbar-expand-md navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
      <img src="img/logo.png" alt="Logo ToDoApp" class="me-2" style="max-height:40px; width:auto;">
      ToDoApp
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav align-items-center">
        <?php if ($isLogged): ?>
          <li class="nav-item me-2">
            <span class="navbar-text text-white">👋 Ciao, <?php echo htmlspecialchars($username); ?></span>
          </li>
          <li class="nav-item me-2">
            <a class="btn btn-outline-warning btn-sm" href="editpw.php">🔑 Cambia Password</a>
          </li>
          <li class="nav-item me-2">
            <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item me-2">
            <a class="btn btn-outline-light btn-sm" href="login.php">Accedi</a>
          </li>
          <li class="nav-item me-2">
            <a class="btn btn-outline-light btn-sm" href="register.php">Registrati</a>
          </li>
        <?php endif; ?>

        <!-- Toggle modalità notte/giorno -->
        <li class="nav-item">
          <button id="theme-toggle" class="btn btn-outline-light btn-sm">
            <i class="bi bi-moon-stars"></i> Dark Mode
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>