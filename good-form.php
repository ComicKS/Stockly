<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();
requireRole('Operator');

$system = new BorrowingSystem();
$good = null;
$isView = !empty($_GET['view']);
if (!empty($_GET['id'])) {
    $good = $system->getGoodById((int) $_GET['id']);
}
$modeTitle = $good ? ($isView ? 'View goods item' : 'Edit goods item') : 'Add goods item';
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
                <div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0"><?php echo htmlspecialchars($modeTitle, ENT_QUOTES, 'UTF-8'); ?></h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item"><a href="goods.php">Goods</a></li><li class="breadcrumb-item active">Form</li></ol></div></div></div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <form action="good-process.php" method="post">
                                <?php if ($good): ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $good['id']; ?>" />
                                <?php endif; ?>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($good['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Inventory code</label>
                                        <input type="text" class="form-control" name="inventory_code" value="<?php echo htmlspecialchars($good['inventory_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Category</label>
                                        <input type="text" class="form-control" name="category" value="<?php echo htmlspecialchars($good['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Location</label>
                                        <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($good['location'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="quantity" min="0" value="<?php echo htmlspecialchars((string) ($good['quantity'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : 'required'; ?>>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Condition</label>
                                        <select class="form-select" name="condition_status" <?php echo $isView ? 'disabled' : ''; ?>>
                                            <?php foreach (['Good', 'Fair', 'Needs Repair', 'Unavailable'] as $status): ?>
                                                <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($good['condition_status'] ?? 'Good') === $status) ? 'selected' : ''; ?>><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Maintenance status</label>
                                        <select class="form-select" name="maintenance_status" <?php echo $isView ? 'disabled' : ''; ?>>
                                            <?php foreach (['Available', 'Maintenance'] as $status): ?>
                                                <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo (($good['maintenance_status'] ?? 'Available') === $status) ? 'selected' : ''; ?>><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="4" <?php echo $isView ? 'readonly' : ''; ?>><?php echo htmlspecialchars($good['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Maintenance notes</label>
                                        <input type="text" class="form-control" name="maintenance_notes" value="<?php echo htmlspecialchars($good['maintenance_notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" <?php echo $isView ? 'readonly' : ''; ?>>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="goods.php" class="btn btn-outline-secondary">Back</a>
                                    <?php if (!$isView): ?>
                                        <button type="submit" class="btn btn-primary"><?php echo $good ? 'Update item' : 'Create item'; ?></button>
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
