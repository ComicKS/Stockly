<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();

$system = new BorrowingSystem();
$currentUser = currentUser();
[$flashMessage, $flashType] = getFlash();
$borrowerId = isset($currentUser['borrower_id']) ? (int) $currentUser['borrower_id'] : 0;
$requests = $borrowerId > 0 ? $system->getMyRequests($borrowerId) : [];

function loanBadge($status) {
    $map = [
        'Pending' => 'bg-warning text-dark',
        'Approved' => 'bg-success',
        'Rejected' => 'bg-danger',
        'Checked Out' => 'bg-info',
        'Returned' => 'bg-secondary',
        'Cancelled' => 'bg-dark',
    ];
    $class = $map[$status] ?? 'bg-secondary';
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
                <div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">My requests</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item active">My requests</li></ol></div></div></div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <?php if ($flashMessage): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?> mb-4"><?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Your borrowing record</h3><a href="loan-form.php" class="btn btn-success">New request</a></div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead><tr><th>#</th><th>Purpose</th><th>Type</th><th>Period</th><th>Status</th><th>Actions</th></tr></thead>
                                <tbody>
                                    <?php if (empty($requests)): ?>
                                        <tr><td colspan="6" class="text-center py-4">No requests found for this account.</td></tr>
                                    <?php else: foreach ($requests as $index => $request): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($request['purpose'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($request['request_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($request['start_date'], ENT_QUOTES, 'UTF-8'); ?><?php echo !empty($request['end_date']) ? ' to ' . htmlspecialchars($request['end_date'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                            <td><?php echo loanBadge($request['status']); ?></td>
                                            <td><a href="loan-detail.php?id=<?php echo (int) $request['id']; ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
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
