<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();

$system = new BorrowingSystem();
[$flashMessage, $flashType] = getFlash();
$search = trim((string) ($_GET['q'] ?? ''));
$selectedStart = $_GET['start_date'] ?? date('Y-m-d\TH:i');
$selectedEnd = $_GET['end_date'] ?? date('Y-m-d\TH:i', strtotime('+2 hours'));

$goods = $system->getGoods($search);
$facilities = $system->getFacilities($search);
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
                    <div class="row"><div class="col-sm-6"><h3 class="mb-0">Catalog and availability</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item active">Catalog</li></ol></div></div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <?php if ($flashMessage): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?> mb-4"><?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <form method="get" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Search</label>
                                    <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search goods or facilities" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">From</label>
                                    <input type="datetime-local" class="form-control" name="start_date" value="<?php echo htmlspecialchars($selectedStart, ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">To</label>
                                    <input type="datetime-local" class="form-control" name="end_date" value="<?php echo htmlspecialchars($selectedEnd, ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div class="col-md-2 d-grid">
                                    <button type="submit" class="btn btn-primary">Check availability</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="card shadow-sm border-0">
                                <div class="card-header"><h3 class="card-title mb-0">Goods availability</h3></div>
                                <div class="card-body p-0">
                                    <table class="table table-striped mb-0 align-middle">
                                        <thead><tr><th>Name</th><th>Available</th><th>Status</th><th>Action</th></tr></thead>
                                        <tbody>
                                            <?php if (empty($goods)): ?>
                                                <tr><td colspan="4" class="text-center py-4">No goods found.</td></tr>
                                            <?php else: foreach ($goods as $good): $availability = $system->getGoodsAvailability((int) $good['id'], $selectedStart, $selectedEnd); ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($good['name'] . ' (' . $good['inventory_code'] . ')', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo (int) $availability['available_quantity']; ?> / <?php echo (int) $good['quantity']; ?></td>
                                                    <td><span class="badge <?php echo $availability['status'] === 'Available' ? 'bg-success' : ($availability['status'] === 'Partially booked' ? 'bg-warning text-dark' : 'bg-secondary'); ?>"><?php echo htmlspecialchars($availability['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td><a href="loan-form.php?good_id=<?php echo (int) $good['id']; ?>" class="btn btn-sm btn-outline-primary">Request</a></td>
                                                </tr>
                                            <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card shadow-sm border-0">
                                <div class="card-header"><h3 class="card-title mb-0">Facility availability</h3></div>
                                <div class="card-body p-0">
                                    <table class="table table-striped mb-0 align-middle">
                                        <thead><tr><th>Name</th><th>Capacity</th><th>Status</th><th>Action</th></tr></thead>
                                        <tbody>
                                            <?php if (empty($facilities)): ?>
                                                <tr><td colspan="4" class="text-center py-4">No facilities found.</td></tr>
                                            <?php else: foreach ($facilities as $facility): $status = $system->getFacilityAvailability((int) $facility['id'], $selectedStart, $selectedEnd); ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($facility['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?php echo (int) $facility['capacity']; ?></td>
                                                    <td><span class="badge <?php echo $status['available'] ? 'bg-success' : 'bg-secondary'; ?>"><?php echo htmlspecialchars($status['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                    <td><a href="loan-form.php?facility_id=<?php echo (int) $facility['id']; ?>" class="btn btn-sm btn-outline-primary">Request</a></td>
                                                </tr>
                                            <?php endforeach; endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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
