<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();

$system = new BorrowingSystem();
$currentUser = currentUser();
$currentRole = $currentUser['role'] ?? 'User';
[$flashMessage, $flashType] = getFlash();
$stats = $currentRole === 'Operator'
    ? $system->getDashboardStats()
    : $system->getUserDashboardStats((int) ($currentUser['borrower_id'] ?? 0));

function displayStatusBadge($status) {
    $mapping = [
        'Pending' => 'bg-warning text-dark',
        'Approved' => 'bg-success',
        'Rejected' => 'bg-danger',
        'Checked Out' => 'bg-info',
        'Returned' => 'bg-secondary',
        'Cancelled' => 'bg-dark'
    ];
    $class = $mapping[$status] ?? 'bg-secondary';
    return '<span class="badge ' . $class . '">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>';
}

function formatDate($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '—';
    }
    return date('d M Y', strtotime($date));
}
?>
<!doctype html>
<html lang="en">
	<head>
		<?php include 'template/header.php'; ?>
	</head>
	<body class="layout-fixed fixed-header fixed-footer sidebar-expand-lg sidebar-open bg-body-tertiary">
		<div class="app-wrapper">
			<?php include 'template/navbar.php'; ?>
			<?php include 'template/sidebar.php'; ?>
			<main class="app-main">
				<div class="app-content-header">
					<div class="container-fluid">
						<div class="row">
							<div class="col-sm-6">
								<h3 class="mb-0"><?php echo $currentRole === 'Operator' ? 'IDB Bali Borrowing Dashboard' : 'Welcome to Stockly'; ?></h3>
							</div>
							<div class="col-sm-6 text-sm-end">
								<small class="text-muted">Signed in as <?php echo htmlspecialchars(($currentUser['full_name'] ?? 'Staff'), ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($currentRole, ENT_QUOTES, 'UTF-8'); ?>)</small>
							</div>
							<div class="col-sm-6">
								<ol class="breadcrumb float-sm-end">
									<li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
									<li class="breadcrumb-item active" aria-current="page"><?php echo $currentRole === 'Operator' ? 'Overview' : 'My dashboard'; ?></li>
								</ol>
							</div>
						</div>
					</div>
				</div>

				<div class="app-content">
					<div class="container-fluid">
						<?php if ($flashMessage): ?>
						<div class="alert alert-<?php echo htmlspecialchars($flashType, ENT_QUOTES, 'UTF-8'); ?> mb-4"><?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?></div>
						<?php endif; ?>
						<div class="row g-3 mb-4">
							<?php if ($currentRole === 'Operator'): ?>
								<div class="col-md-3">
									<div class="small-box bg-primary-subtle border border-primary-subtle shadow-sm">
										<div class="inner p-3" style="min-height: 80px">
											<h3><?php echo $stats['pending_requests']; ?></h3>
											<p>Pending requests</p>
										</div>
										<div class="icon"><i class="bi bi-hourglass-split"></i></div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="small-box bg-success-subtle border border-success-subtle shadow-sm">
										<div class="inner p-3">
											<h3><?php echo $stats['active_loans']; ?></h3>
											<p>Active loans</p>
										</div>
										<div class="icon"><i class="bi bi-box-seam"></i></div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="small-box bg-warning-subtle border border-warning-subtle shadow-sm">
										<div class="inner p-3">
											<h3><?php echo $stats['overdue_loans']; ?></h3>
											<p>Overdue loans</p>
										</div>
										<div class="icon"><i class="bi bi-exclamation-triangle"></i></div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="small-box bg-info-subtle border border-info-subtle shadow-sm">
										<div class="inner p-3">
											<h3><?php echo $stats['today_bookings']; ?></h3>
											<p>Today's bookings</p>
										</div>
										<div class="icon"><i class="bi bi-calendar-date"></i></div>
									</div>
								</div>
							<?php else: ?>
								<div class="col-md-3">
									<div class="small-box bg-primary-subtle border border-primary-subtle shadow-sm">
										<div class="inner p-3">
											<h3><?php echo $stats['pending_requests']; ?></h3>
											<p>Pending</p>
										</div>
										<div class="icon"><i class="bi bi-hourglass-split"></i></div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="small-box bg-success-subtle border border-success-subtle shadow-sm">
										<div class="inner p-3">
											<h3><?php echo $stats['approved_requests']; ?></h3>
											<p>Approved</p>
										</div>
										<div class="icon"><i class="bi bi-check-circle"></i></div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="small-box bg-info-subtle border border-info-subtle shadow-sm">
										<div class="inner p-3">
											<h3><?php echo $stats['checked_out_requests']; ?></h3>
											<p>Checked out</p>
										</div>
										<div class="icon"><i class="bi bi-box-arrow-right"></i></div>
									</div>
								</div>
								<div class="col-md-3">
									<div class="small-box bg-secondary-subtle border border-secondary-subtle shadow-sm">
										<div class="inner p-3">
											<h3><?php echo $stats['returned_requests']; ?></h3>
											<p>Returned</p>
										</div>
										<div class="icon"><i class="bi bi-clipboard-check"></i></div>
									</div>
								</div>
							<?php endif; ?>
						</div>

						<div class="row g-3">
							<?php if ($currentRole === 'Operator'): ?>
								<div class="col-lg-8">
									<div class="card shadow-sm border-0 h-100">
										<div class="card-header d-flex justify-content-between align-items-center">
											<h3 class="card-title mb-0">Recent loan requests</h3>
											<a href="loans.php" class="btn btn-sm btn-outline-primary">View all</a>
										</div>
										<div class="card-body p-0">
											<table class="table table-striped mb-0 align-middle">
												<thead>
													<tr>
														<th>Borrower</th>
														<th>Type</th>
														<th>Status</th>
														<th>Request date</th>
													</tr>
												</thead>
												<tbody>
													<?php if (empty($stats['recent_loans'])): ?>
													<tr><td colspan="4" class="text-center py-4">No recent borrowing records found.</td></tr>
													<?php else: ?>
													<?php foreach ($stats['recent_loans'] as $loan): ?>
													<tr>
														<td><?php echo htmlspecialchars($loan['full_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
														<td><?php echo htmlspecialchars($loan['request_type'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
														<td><?php echo displayStatusBadge($loan['status'] ?? 'Pending'); ?></td>
														<td><?php echo formatDate($loan['request_date'] ?? null); ?></td>
													</tr>
													<?php endforeach; ?>
													<?php endif; ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="card shadow-sm border-0 h-100">
										<div class="card-header">
											<h3 class="card-title mb-0">Quick actions</h3>
										</div>
										<div class="card-body d-grid gap-2">
											<a href="borrowers.php" class="btn btn-primary"><i class="bi bi-people"></i> Manage borrowers</a>
											<a href="facilities.php" class="btn btn-success"><i class="bi bi-building"></i> Manage facilities</a>
											<a href="goods.php" class="btn btn-info"><i class="bi bi-boxes"></i> Manage goods</a>
											<a href="loan-form.php" class="btn btn-warning text-dark"><i class="bi bi-plus-circle"></i> Create loan request</a>
										</div>
									</div>
								</div>
							<?php else: ?>
								<div class="col-lg-8">
									<div class="card shadow-sm border-0 h-100">
										<div class="card-header">
											<h3 class="card-title mb-0">Your recent requests</h3>
										</div>
										<div class="card-body p-0">
											<table class="table table-striped mb-0 align-middle">
												<thead>
													<tr>
														<th>Purpose</th>
														<th>Type</th>
														<th>Dates</th>
														<th>Status</th>
													</tr>
												</thead>
												<tbody>
													<?php if (empty($stats['recent_loans'])): ?>
													<tr><td colspan="4" class="text-center py-4">No requests yet. Start by checking availability.</td></tr>
													<?php else: ?>
													<?php foreach ($stats['recent_loans'] as $loan): ?>
													<tr>
														<td><?php echo htmlspecialchars($loan['purpose'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
														<td><?php echo htmlspecialchars($loan['request_type'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></td>
														<td><?php echo htmlspecialchars($loan['start_date'] ?? '—', ENT_QUOTES, 'UTF-8'); ?><?php echo !empty($loan['end_date']) ? ' to ' . htmlspecialchars($loan['end_date'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
														<td><?php echo displayStatusBadge($loan['status'] ?? 'Pending'); ?></td>
													</tr>
													<?php endforeach; ?>
													<?php endif; ?>
												</tbody>
											</table>
										</div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="card shadow-sm border-0 h-100">
										<div class="card-header">
											<h3 class="card-title mb-0">Quick actions</h3>
										</div>
										<div class="card-body d-grid gap-2">
											<a href="catalog.php" class="btn btn-primary"><i class="bi bi-search"></i> Check availability</a>
											<a href="loan-form.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> Make a request</a>
											<a href="my-requests.php" class="btn btn-info"><i class="bi bi-clipboard-data"></i> View my requests</a>
										</div>
									</div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</main>
			<?php include 'template/footer.php'; ?>
		</div>
		<?php include 'template/script.php'; ?>
	</body>
</html>