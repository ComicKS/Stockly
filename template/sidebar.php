<?php
$currentRole = currentUserRole();
?>
<aside class="app-sidebar">
    <div class="sidebar-brand">
        <a href="index.php" class="brand-link">
            <img src="assets/img/logo.png" alt="IDB Bali logo" class="brand-image" />
            <span class="brand-text">IDB Bali</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false" id="navigation">
                <li class="nav-header">Main</li>
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="nav-icon bi bi-house-door-fill"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <?php if ($currentRole === 'User'): ?>
                    <li class="nav-item">
                        <a href="catalog.php" class="nav-link">
                            <i class="nav-icon bi bi-shop-window"></i>
                            <p>Browse &amp; Availability</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="loan-form.php" class="nav-link">
                            <i class="nav-icon bi bi-journal-check"></i>
                            <p>New Request</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="my-requests.php" class="nav-link">
                            <i class="nav-icon bi bi-clipboard-data"></i>
                            <p>My Requests</p>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="borrowers.php" class="nav-link">
                            <i class="nav-icon bi bi-people-fill"></i>
                            <p>Borrowers</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="facilities.php" class="nav-link">
                            <i class="nav-icon bi bi-building"></i>
                            <p>Facilities</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="goods.php" class="nav-link">
                            <i class="nav-icon bi bi-box-seam"></i>
                            <p>Goods &amp; Equipment</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="loans.php" class="nav-link">
                            <i class="nav-icon bi bi-clipboard-check"></i>
                            <p>Loan Requests</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="availability.php" class="nav-link">
                            <i class="nav-icon bi bi-calendar3-range"></i>
                            <p>Check Availability</p>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if ($currentRole !== 'User'): ?>
                    <li class="nav-header">Actions</li>
                    <li class="nav-item">
                        <a href="borrower-form.php" class="nav-link">
                            <i class="nav-icon bi bi-person-plus-fill"></i>
                            <p>Add borrower</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="facility-form.php" class="nav-link">
                            <i class="nav-icon bi bi-plus-square-fill"></i>
                            <p>Add facility</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="good-form.php" class="nav-link">
                            <i class="nav-icon bi bi-box2-heart"></i>
                            <p>Add goods</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="loan-form.php" class="nav-link">
                            <i class="nav-icon bi bi-journal-check"></i>
                            <p>New request</p>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</aside>