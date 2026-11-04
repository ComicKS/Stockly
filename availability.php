<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireRole('Operator');

$system = new BorrowingSystem();
$currentUser = currentUser();

$selectedDate = $_GET['selected_day'] ?? date('Y-m-d');
$resourceType = $_GET['resource_type'] ?? 'Facility';
$resourceId = isset($_GET['resource_id']) ? (int) $_GET['resource_id'] : 0;

$resourceType = in_array($resourceType, ['Facility', 'Goods'], true) ? $resourceType : 'Facility';
$resources = $resourceType === 'Goods' ? $system->getGoods() : $system->getFacilities();

if ($resourceId <= 0 && !empty($resources)) {
    $resourceId = (int) $resources[0]['id'];
}

$selectedResource = null;
if ($resourceId > 0) {
    $selectedResource = $resourceType === 'Goods' ? $system->getGoodById($resourceId) : $system->getFacilityById($resourceId);
}

$dayStart = $selectedDate . ' 00:00:00';
$dayEnd = date('Y-m-d 23:59:59', strtotime($selectedDate));
$bookings = [];

if ($selectedResource) {
    if ($resourceType === 'Facility') {
        $stmt = $system->conn->prepare('SELECT l.id, b.full_name, l.status, l.start_date, l.end_date, l.purpose FROM tb_loans l LEFT JOIN tb_borrowers b ON b.id = l.borrower_id WHERE l.facility_id = ? AND l.status IN (?, ?) AND l.start_date < ? AND COALESCE(l.end_date, l.start_date) > ? ORDER BY l.start_date ASC');
        $approved = 'Approved';
        $checkedOut = 'Checked Out';
        $stmt->bind_param('issss', $resourceId, $approved, $checkedOut, $dayEnd, $dayStart);
    } else {
        $stmt = $system->conn->prepare('SELECT l.id, b.full_name, l.status, l.start_date, l.end_date, l.purpose, li.quantity FROM tb_loans l INNER JOIN tb_loan_items li ON li.loan_id = l.id LEFT JOIN tb_borrowers b ON b.id = l.borrower_id WHERE li.good_id = ? AND l.status IN (?, ?) AND l.start_date < ? AND COALESCE(l.end_date, l.start_date) > ? ORDER BY l.start_date ASC');
        $approved = 'Approved';
        $checkedOut = 'Checked Out';
        $stmt->bind_param('issss', $resourceId, $approved, $checkedOut, $dayEnd, $dayStart);
    }
    $stmt->execute();
    $bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$slots = [];
if ($selectedResource) {
    $slotStart = strtotime($selectedDate . ' 08:00:00');
    $slotEnd = strtotime($selectedDate . ' 18:00:00');
    $slotLength = 2 * 60 * 60;

    for ($ts = $slotStart; $ts < $slotEnd; $ts += $slotLength) {
        $start = date('Y-m-d H:i:s', $ts);
        $end = date('Y-m-d H:i:s', min($ts + $slotLength, $slotEnd));
        if ($resourceType === 'Goods') {
            $availability = $system->getGoodsAvailability((int) $selectedResource['id'], $start, $end);
            $status = $selectedResource['maintenance_status'] === 'Maintenance' ? 'Maintenance' : ($availability['available_quantity'] > 0 ? 'Available' : 'Booked');
            $slots[] = [
                'label' => date('H:i', $ts) . ' - ' . date('H:i', min($ts + $slotLength, $slotEnd)),
                'status' => $status,
                'available' => $availability['available_quantity'],
                'total' => (int) $selectedResource['quantity'],
                'start' => $start,
                'end' => $end,
            ];
        } else {
            $availability = $system->getFacilityAvailability((int) $selectedResource['id'], $start, $end);
            $status = $selectedResource['maintenance_status'] === 'Maintenance' || $selectedResource['availability_status'] === 'Unavailable' ? 'Maintenance' : ($availability['available'] ? 'Available' : 'Booked');
            $slots[] = [
                'label' => date('H:i', $ts) . ' - ' . date('H:i', min($ts + $slotLength, $slotEnd)),
                'status' => $status,
                'available' => $availability['available'],
                'start' => $start,
                'end' => $end,
            ];
        }
    }
}

function availabilityPill($status, $isAvailable = null) {
    $status = trim((string) $status);
    $classMap = [
        'Available' => 'bg-success',
        'Partially booked' => 'bg-warning text-dark',
        'Booked' => 'bg-secondary',
        'Maintenance' => 'bg-danger',
        'Pending' => 'bg-warning text-dark',
        'Approved' => 'bg-success',
        'Checked Out' => 'bg-info',
        'Returned' => 'bg-secondary',
        'Rejected' => 'bg-danger',
    ];
    $class = $classMap[$status] ?? 'bg-secondary';
    return '<span class="badge ' . $class . '">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>';
}

function formatRange($start, $end) {
    if (empty($start) && empty($end)) {
        return '—';
    }
    $s = !empty($start) ? date('d M Y, H:i', strtotime($start)) : '—';
    $e = !empty($end) ? date('d M Y, H:i', strtotime($end)) : '—';
    return $s . ' to ' . $e;
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
                        <div class="col-sm-6"><h3 class="mb-0">Check availability</h3></div>
                        <div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item active">Availability</li></ol></div>
                    </div>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <form method="get" class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label">Selected day</label>
                                    <input type="date" class="form-control" name="selected_day" value="<?php echo htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Resource type</label>
                                    <select class="form-select" name="resource_type">
                                        <option value="Facility" <?php echo $resourceType === 'Facility' ? 'selected' : ''; ?>>Facilities</option>
                                        <option value="Goods" <?php echo $resourceType === 'Goods' ? 'selected' : ''; ?>>Goods</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Resource</label>
                                    <select class="form-select" name="resource_id">
                                        <?php foreach ($resources as $resource): ?>
                                            <option value="<?php echo (int) $resource['id']; ?>" <?php echo ($resourceId === (int) $resource['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($resourceType === 'Goods' ? $resource['name'] . ' (' . $resource['inventory_code'] . ')' : $resource['name'] . ' - ' . $resource['building'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 d-grid">
                                    <button type="submit" class="btn btn-primary">Apply</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header"><h3 class="card-title mb-0"><?php echo $resourceType === 'Goods' ? 'Goods list' : 'Facilities list'; ?></h3></div>
                                <div class="card-body p-0">
                                    <div class="list-group list-group-flush">
                                        <?php if (empty($resources)): ?>
                                            <div class="p-3 text-muted">No resources available.</div>
                                        <?php else: foreach ($resources as $resource): ?>
                                            <a class="list-group-item list-group-item-action <?php echo ($resourceId === (int) $resource['id']) ? 'active' : ''; ?>" href="availability.php?selected_day=<?php echo urlencode($selectedDate); ?>&resource_type=<?php echo urlencode($resourceType); ?>&resource_id=<?php echo (int) $resource['id']; ?>">
                                                <div class="d-flex justify-content-between align-items-center gap-3">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($resourceType === 'Goods' ? $resource['name'] : $resource['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <div class="small text-muted"><?php echo htmlspecialchars($resourceType === 'Goods' ? $resource['category'] . ' · ' . $resource['location'] : $resource['building'], ENT_QUOTES, 'UTF-8'); ?></div>
                                                    </div>
                                                    <?php if ($resourceType === 'Goods'): ?>
                                                        <?php $availability = $system->getGoodsAvailability((int) $resource['id'], $selectedDate . ' 08:00:00', $selectedDate . ' 18:00:00'); ?>
                                                        <span class="badge <?php echo $availability['status'] === 'Available' ? 'bg-success' : ($availability['status'] === 'Partially booked' ? 'bg-warning text-dark' : 'bg-secondary'); ?>"><?php echo htmlspecialchars($availability['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php else: ?>
                                                        <?php $availability = $system->getFacilityAvailability((int) $resource['id'], $selectedDate . ' 08:00:00', $selectedDate . ' 18:00:00'); ?>
                                                        <span class="badge <?php echo $availability['available'] ? 'bg-success' : 'bg-secondary'; ?>"><?php echo htmlspecialchars($availability['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </a>
                                        <?php endforeach; endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <?php if ($selectedResource): ?>
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h3 class="card-title mb-0"><?php echo htmlspecialchars($selectedResource['name'] ?? 'Selected resource', ENT_QUOTES, 'UTF-8'); ?></h3>
                                        <?php if ($resourceType === 'Goods'): ?>
                                            <span class="badge bg-light text-dark"><?php echo (int) ($selectedResource['quantity'] ?? 0); ?> total</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark"><?php echo (int) ($selectedResource['capacity'] ?? 0); ?> capacity</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="small text-uppercase text-muted fw-semibold">Maintenance</div>
                                                <div class="mt-1"><?php echo availabilityPill($selectedResource['maintenance_status'] ?? 'Operational'); ?></div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="small text-uppercase text-muted fw-semibold">Selected date</div>
                                                <div class="mt-1"><strong><?php echo htmlspecialchars(date('d M Y', strtotime($selectedDate)), ENT_QUOTES, 'UTF-8'); ?></strong></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card shadow-sm border-0">
                                    <div class="card-header"><h3 class="card-title mb-0">Daily schedule</h3></div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0 align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Time</th>
                                                        <th>Status</th>
                                                        <th><?php echo $resourceType === 'Goods' ? 'Available stock' : 'Availability'; ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($slots)): ?>
                                                        <tr><td colspan="3" class="text-center py-4">No availability slots available for this day.</td></tr>
                                                    <?php else: foreach ($slots as $slot): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($slot['label'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?php echo availabilityPill($slot['status']); ?></td>
                                                            <td>
                                                                <?php if ($resourceType === 'Goods'): ?>
                                                                    <?php echo (int) $slot['available']; ?> / <?php echo (int) $slot['total']; ?> available
                                                                <?php else: ?>
                                                                    <?php echo $slot['available'] ? 'Free' : 'Booked'; ?>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card shadow-sm border-0">
                                    <div class="card-body text-center py-5 text-muted">Select a resource to see availability details.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($selectedResource && !empty($bookings)): ?>
                        <div class="card shadow-sm border-0 mt-4">
                            <div class="card-header"><h3 class="card-title mb-0">Existing bookings</h3></div>
                            <div class="card-body p-0">
                                <table class="table table-striped mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>Borrower</th>
                                            <th>Purpose</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($booking['full_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($booking['purpose'] ?? 'No purpose stated', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(formatRange($booking['start_date'], $booking['end_date']), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo availabilityPill($booking['status'] ?? 'Pending'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        <?php include __DIR__ . '/template/footer.php'; ?>
    </div>
    <?php include __DIR__ . '/template/script.php'; ?>
</body>
</html>
