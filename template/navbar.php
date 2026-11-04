<?php
$currentUser = currentUser();
?>
<nav class="app-header navbar navbar-expand">
    <div class="container-fluid px-3">
        <ul class="navbar-nav me-auto align-items-center">
            <li class="nav-item">
                <a class="nav-link nav-toggle" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center">
            <?php if ($currentUser): ?>
                <li class="nav-item d-none d-md-flex align-items-center me-2">
                    <span class="navbar-user text-white-50">
                        <i class="bi bi-person-circle"></i>
                        <span class="ms-1"><?php echo htmlspecialchars(($currentUser['full_name'] ?? 'User'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="badge bg-white text-primary ms-2"><?php echo htmlspecialchars(($currentUser['role'] ?? 'User'), ENT_QUOTES, 'UTF-8'); ?></span>
                    </span>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="#" data-lte-toggle="fullscreen" aria-label="Fullscreen">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                </a>
            </li>
            <?php if ($currentUser): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="logout.php" role="button" aria-label="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="d-none d-md-inline ms-1">Logout</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>