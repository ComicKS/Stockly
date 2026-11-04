<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();
requireRole('Operator');

$system = new BorrowingSystem();
$borrower = null;
$isView = !empty($_GET['view']);
if (!empty($_GET['id'])) {
    $borrower = $system->getBorrowerById((int) $_GET['id']);
}
$modeTitle = $borrower ? ($isView ? 'View borrower' : 'Edit borrower') : 'Add borrower';
?>
<!doctype html>
<html lang="en">
<head>
    <?php include __DIR__ . '/template/header.php'; ?>
</head>
<body class="layout-fixed fixed-header fixed-footer sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        <?php include __DIR__ . '/template/navbar.php'; ?>
        <?php include __DIR__ . '/template/sidebar.php'; ?>
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6"><h3 class="mb-0"><?php echo htmlspecialchars($modeTitle, ENT_QUOTES, 'UTF-8'); ?></h3></div>
                        <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item"><a href="borrowers.php">Borrowers</a></li><li class="breadcrumb-item active">Form</li></ol></div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <form action="borrower-process.php" method="post">
                                <?php if ($borrower): ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $borrower['id']; ?>" />
                                <?php endif; ?>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Campus ID</label>
                                        <input type="text" class="form-control" name="campus_id" value="<?php echo htmlspecialchars($borrower['campus_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Full name</label>
                                        <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($borrower['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Type</label>
                                        <select class="form-select" name="borrower_type" <?php echo $isView ? 'disabled' : ''; ?>>
                                            <?php foreach (['Student', 'Staff'] as $type): ?>
                                                <option value="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($borrower['borrower_type'] ?? 'Student') === $type) ? 'selected' : ''; ?>><?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Department / Program</label>
                                        <input type="text" class="form-control" name="department" value="<?php echo htmlspecialchars($borrower['department'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Active status</label>
                                        <select class="form-select" name="active_status" <?php echo $isView ? 'disabled' : ''; ?>>
                                            <?php foreach (['Active', 'Inactive'] as $status): ?>
                                                <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($borrower['active_status'] ?? 'Active') === $status) ? 'selected' : ''; ?>><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone</label>
                                        <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($borrower['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : ''; ?>>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($borrower['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : ''; ?>>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="borrowers.php" class="btn btn-outline-secondary">Back</a>
                                    <?php if (!$isView): ?>
                                        <button type="submit" class="btn btn-primary"><?php echo $borrower ? 'Update borrower' : 'Create borrower'; ?></button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include __DIR__ . '/template/footer.php'; ?>
    </div>
    <?php include __DIR__ . '/template/script.php'; ?>
</body>
</html>
