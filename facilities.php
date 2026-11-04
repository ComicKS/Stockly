<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();
requireRole('Operator');

$system = new BorrowingSystem();
$search = trim((string) ($_GET['q'] ?? ''));
$facilities = $system->getFacilities($search);

function facilityStatusBadge($status) {
    $status = trim((string) $status);
    $class = $status === 'Available' ? 'bg-success' : 'bg-secondary';
    return '<span class="badge ' . $class . '">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>';
}
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
                        <div class="col-sm-6"><h3 class="mb-0">Facilities</h3></div>
                        <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item active">Facilities</li></ol></div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Campus facilities</h3>
                            <div class="d-flex gap-2 align-items-center">
                                <form method="get" class="d-flex align-items-center gap-2 mb-0">
                                    <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search facilities" />
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </form>
                                <a href="facility-form.php" class="btn btn-success">Add facility</a>
                            </div>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Building</th>
                                        <th>Capacity</th>
                                        <th>Availability</th>
                                        <th>Maintenance</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($facilities)): ?>
                                        <tr><td colspan="7" class="text-center py-4">No facility records available.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($facilities as $index => $facility): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($facility['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($facility['building'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo (int) $facility['capacity']; ?></td>
                                                <td><?php echo facilityStatusBadge($facility['availability_status']); ?></td>
                                                <td><?php echo htmlspecialchars($facility['maintenance_status'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="facility-form.php?id=<?php echo (int) $facility['id']; ?>&view=1" class="btn btn-sm btn-outline-primary">View</a>
                                                        <a href="facility-form.php?id=<?php echo (int) $facility['id']; ?>" class="btn btn-sm btn-warning text-dark">Edit</a>
                                                        <a href="facility-process.php?action=delete&id=<?php echo (int) $facility['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this facility record?');">Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
