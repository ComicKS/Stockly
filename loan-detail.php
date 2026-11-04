<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();

$system = new BorrowingSystem();
$loan = null;
if (!empty($_GET['id'])) {
    $loan = $system->getLoanById((int) $_GET['id']);
}
if (!$loan) {
    header('Location: loans.php');
    exit;
}

$currentUser = currentUser();
if (($currentUser['role'] ?? 'User') === 'User') {
    $borrowerId = isset($currentUser['borrower_id']) ? (int) $currentUser['borrower_id'] : 0;
    if ($borrowerId !== (int) $loan['borrower_id']) {
        header('Location: my-requests.php');
        exit;
    }
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
                        <div class="col-sm-6"><h3 class="mb-0">Loan details</h3></div>
                        <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item"><a href="loans.php">Loans</a></li><li class="breadcrumb-item active">View</li></ol></div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header"><h3 class="card-title mb-0">Request overview</h3></div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3"><strong>Borrower:</strong><br><?php echo htmlspecialchars($loan['full_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="col-md-3"><strong>Campus ID:</strong><br><?php echo htmlspecialchars($loan['campus_id'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="col-md-3"><strong>Request type:</strong><br><?php echo htmlspecialchars($loan['request_type'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="col-md-3"><strong>Status:</strong><br><?php echo htmlspecialchars($loan['status'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="col-md-6"><strong>Purpose:</strong><br><?php echo htmlspecialchars($loan['purpose'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="col-md-6"><strong>Notes:</strong><br><?php echo htmlspecialchars($loan['notes'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="col-md-3"><strong>Start:</strong><br><?php echo htmlspecialchars($loan['start_date'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="col-md-3"><strong>End:</strong><br><?php echo htmlspecialchars($loan['end_date'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="col-md-3"><strong>Due date:</strong><br><?php echo htmlspecialchars($loan['due_date'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="col-md-3"><strong>Issue / return:</strong><br><?php echo htmlspecialchars(($loan['issue_date'] ?: '—') . ' / ' . ($loan['return_date'] ?: '—'), ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                    </div>

                    <?php if (($currentUser['role'] ?? 'User') === 'Operator'): ?>
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header"><h3 class="card-title mb-0">Workflow actions</h3></div>
                        <div class="card-body">
                            <?php $validActions = $system->getStatusTransitions($loan['status']); ?>
                            <?php if (empty($validActions)): ?>
                                <p class="text-muted mb-0">No further status changes are available for this request.</p>
                            <?php else: ?>
                                <form action="loan-process.php" method="post">
                                    <input type="hidden" name="action" value="update_status" />
                                    <input type="hidden" name="loan_id" value="<?php echo (int) $loan['id']; ?>" />
                                    <div class="row g-3 align-items-end">
                                        <div class="col-md-3">
                                            <label class="form-label">Issue date / time</label>
                                            <input type="datetime-local" class="form-control" name="issue_date" value="<?php echo htmlspecialchars($loan['issue_date'] ? date('Y-m-d\TH:i', strtotime($loan['issue_date'])) : '', ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Return date / time</label>
                                            <input type="datetime-local" class="form-control" name="return_date" value="<?php echo htmlspecialchars($loan['return_date'] ? date('Y-m-d\TH:i', strtotime($loan['return_date'])) : '', ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Condition on return</label>
                                            <input type="text" class="form-control" name="condition_on_return" value="<?php echo htmlspecialchars($loan['condition_on_return'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                        </div>
                                        <div class="col-md-3 d-flex gap-2 flex-wrap">
                                            <?php foreach ($validActions as $nextStatus): ?>
                                                <button type="submit" name="status" value="<?php echo htmlspecialchars($nextStatus, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary"><?php echo htmlspecialchars($nextStatus, ENT_QUOTES, 'UTF-8'); ?></button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="card shadow-sm border-0">
                        <div class="card-header"><h3 class="card-title mb-0">Requested items</h3></div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-striped mb-0">
                                <thead><tr><th>Item</th><th>Quantity</th></tr></thead>
                                <tbody>
                                    <?php if (empty($loan['items'])): ?>
                                        <tr><td colspan="2" class="text-center py-3">No item data available.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($loan['items'] as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['good_name'] ?: ($item['facility_name'] ?? '—'), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo (int) ($item['quantity'] ?: 1); ?></td>
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
