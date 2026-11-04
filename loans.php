<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();

$system = new BorrowingSystem();
$currentUser = currentUser();
[$flashMessage, $flashType] = getFlash();
$search = trim((string) ($_GET['q'] ?? ''));
$statusFilter = trim((string) ($_GET['status'] ?? ''));
if (($currentUser['role'] ?? 'User') === 'Operator') {
    $loans = $system->getLoanRecords($search, $statusFilter);
    $pageTitle = 'Loan requests';
} else {
    $borrowerId = isset($currentUser['borrower_id']) ? (int) $currentUser['borrower_id'] : 0;
    $loans = $borrowerId > 0 ? $system->getMyRequests($borrowerId) : [];
    $pageTitle = 'My requests';
}
$statusOptions = $system->getStatusOptions();

function loanStatusBadge($status) {
    $status = trim((string) $status);
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
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6"><h3 class="mb-0"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3></div>
                        <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item active"><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></li></ol></div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <?php if ($flashMessage): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?> mb-4"><?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <div class="card shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h3 class="card-title mb-0"><?php echo (($currentUser['role'] ?? 'User') === 'Operator') ? 'Borrowing records' : 'Your requests'; ?></h3>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <?php if (($currentUser['role'] ?? 'User') === 'Operator'): ?>
                                    <form method="get" class="d-flex align-items-center gap-2 mb-0">
                                        <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search loans" />
                                        <select class="form-select" name="status">
                                            <option value="">All statuses</option>
                                            <?php foreach ($statusOptions as $option): ?>
                                                <option value="<?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $statusFilter === $option ? 'selected' : ''; ?>><?php echo htmlspecialchars($option, ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary">Filter</button>
                                    </form>
                                <?php endif; ?>
                                <a href="loan-form.php" class="btn btn-success">New request</a>
                            </div>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-striped mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Borrower</th>
                                        <th>Purpose</th>
                                        <th>Request type</th>
                                        <th>Items</th>
                                        <th>Booking</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($loans)): ?>
                                        <tr><td colspan="8" class="text-center py-4">No loan records found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($loans as $index => $loan): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($loan['full_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($loan['purpose'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($loan['request_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($loan['items'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php
                                                    $startValue = !empty($loan['start_date']) ? date('d M Y, H:i', strtotime($loan['start_date'])) : '—';
                                                    $endValue = !empty($loan['end_date']) ? date('d M Y, H:i', strtotime($loan['end_date'])) : '—';
                                                    echo htmlspecialchars($startValue, ENT_QUOTES, 'UTF-8') . ' to ' . htmlspecialchars($endValue, ENT_QUOTES, 'UTF-8');
                                                ?></td>
                                                <td><?php echo loanStatusBadge($loan['status']); ?></td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <a href="loan-detail.php?id=<?php echo (int) $loan['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                        <a href="loan-process.php?action=delete&id=<?php echo (int) $loan['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this loan record?');">Delete</a>
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
