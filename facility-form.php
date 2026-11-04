<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();
requireRole('Operator');

$system = new BorrowingSystem();
$facility = null;
$isView = !empty($_GET['view']);
if (!empty($_GET['id'])) {
    $facility = $system->getFacilityById((int) $_GET['id']);
}
$modeTitle = $facility ? ($isView ? 'View facility' : 'Edit facility') : 'Add facility';
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
                <div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0"><?php echo htmlspecialchars($modeTitle, ENT_QUOTES, 'UTF-8'); ?></h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item"><a href="facilities.php">Facilities</a></li><li class="breadcrumb-item active">Form</li></ol></div></div></div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <form action="facility-process.php" method="post">
                                <?php if ($facility): ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $facility['id']; ?>" />
                                <?php endif; ?>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Facility name</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($facility['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Building / location</label>
                                        <input type="text" class="form-control" name="building" value="<?php echo htmlspecialchars($facility['building'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Capacity</label>
                                        <input type="number" class="form-control" name="capacity" min="1" value="<?php echo htmlspecialchars((string) ($facility['capacity'] ?? 1), ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Availability</label>
                                        <select class="form-select" name="availability_status" <?php echo $isView ? 'disabled' : ''; ?>>
                                            <?php foreach (['Available', 'Unavailable'] as $status): ?>
                                                <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($facility['availability_status'] ?? 'Available') === $status) ? 'selected' : ''; ?>><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Maintenance status</label>
                                        <select class="form-select" name="maintenance_status" <?php echo $isView ? 'disabled' : ''; ?>>
                                            <?php foreach (['Operational', 'Maintenance'] as $status): ?>
                                                <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($facility['maintenance_status'] ?? 'Operational') === $status) ? 'selected' : ''; ?>><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Maintenance notes</label>
                                        <input type="text" class="form-control" name="maintenance_notes" value="<?php echo htmlspecialchars($facility['maintenance_notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : ''; ?>>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="4" <?php echo $isView ? 'readonly' : ''; ?>><?php echo htmlspecialchars($facility['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="facilities.php" class="btn btn-outline-secondary">Back</a>
                                    <?php if (!$isView): ?>
                                        <button type="submit" class="btn btn-primary"><?php echo $facility ? 'Update facility' : 'Create facility'; ?></button>
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
