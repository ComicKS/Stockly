<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();
requireRole('Operator');

$system = new BorrowingSystem();
$search = trim((string) ($_GET['q'] ?? ''));
$goods = $system->getGoods($search);

function goodStatusBadge($status) {
    $status = trim((string) $status);
    $class = $status === 'Available' ? 'bg-success' : 'bg-warning text-dark';
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
                        <div class="col-sm-6"><h3 class="mb-0">Goods & Equipment</h3></div>
                        <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item active">Goods</li></ol></div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Inventory items</h3>
                            <div class="d-flex gap-2 align-items-center">
                                <form method="get" class="d-flex align-items-center gap-2 mb-0">
                                    <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search goods" />
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </form>
                                <a href="good-form.php" class="btn btn-success">Add item</a>
                            </div>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Inventory code</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Qty</th>
                                        <th>Condition</th>
                                        <th>Maintenance</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($goods)): ?>
                                        <tr><td colspan="9" class="text-center py-4">No goods available.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($goods as $index => $good): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($good['inventory_code'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($good['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($good['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($good['location'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo (int) $good['quantity']; ?></td>
                                                <td><?php echo htmlspecialchars($good['condition_status'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo goodStatusBadge($good['maintenance_status']); ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="good-form.php?id=<?php echo (int) $good['id']; ?>&view=1" class="btn btn-sm btn-outline-primary">View</a>
                                                        <a href="good-form.php?id=<?php echo (int) $good['id']; ?>" class="btn btn-sm btn-warning text-dark">Edit</a>
                                                        <a href="good-process.php?action=delete&id=<?php echo (int) $good['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this goods record?');">Delete</a>
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
