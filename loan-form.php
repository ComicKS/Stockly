<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();

$system = new BorrowingSystem();
$currentUser = currentUser();
$borrowers = $system->getBorrowers();
$facilities = $system->getFacilities();
$goods = $system->getGoods();
$defaultBorrowerId = (int) ($currentUser['borrower_id'] ?? 0);
if (($currentUser['role'] ?? 'User') === 'User') {
    $borrowers = array_values(array_filter($borrowers, function ($borrower) use ($defaultBorrowerId) {
        return (int) $borrower['id'] === $defaultBorrowerId;
    }));
}
$prefilledGoodId = !empty($_GET['good_id']) ? (int) $_GET['good_id'] : null;
$prefilledFacilityId = !empty($_GET['facility_id']) ? (int) $_GET['facility_id'] : null;
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
                <div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Create loan request</h3></div><div class="col-sm-6"><ol class="breadcrumb float-sm-end"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item"><a href="loans.php">Loans</a></li><li class="breadcrumb-item active">New request</li></ol></div></div></div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <form action="loan-process.php" method="post">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Borrower</label>
                                        <select class="form-select" name="borrower_id" required <?php echo (($currentUser['role'] ?? 'User') === 'User') ? 'disabled' : ''; ?>>
                                            <option value="">Select borrower</option>
                                            <?php foreach ($borrowers as $borrower): ?>
                                                <option value="<?php echo (int) $borrower['id']; ?>" <?php echo ((int) ($borrower['id'] ?? 0) === $defaultBorrowerId) ? 'selected' : ''; ?>><?php echo htmlspecialchars($borrower['full_name'] . ' (' . $borrower['campus_id'] . ')', ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (($currentUser['role'] ?? 'User') === 'User'): ?><input type="hidden" name="borrower_id" value="<?php echo (int) $defaultBorrowerId; ?>" /><?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Request type</label>
                                        <select class="form-select" name="request_type" id="request_type" required>
                                            <option value="Facility">Facility</option>
                                            <option value="Goods">Goods / equipment</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 field-facility">
                                        <label class="form-label">Facility</label>
                                        <select class="form-select" name="facility_id">
                                            <option value="">Select facility</option>
                                            <?php foreach ($facilities as $facility): ?>
                                                <option value="<?php echo (int) $facility['id']; ?>" <?php echo $prefilledFacilityId === (int) $facility['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($facility['name'] . ' - ' . $facility['building'], ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 field-goods" style="display:none;">
                                        <label class="form-label">Goods item</label>
                                        <select class="form-select" name="good_id">
                                            <option value="">Select goods item</option>
                                            <?php foreach ($goods as $good): ?>
                                                <option value="<?php echo (int) $good['id']; ?>" <?php echo $prefilledGoodId === (int) $good['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($good['name'] . ' (' . $good['inventory_code'] . ')', ENT_QUOTES, 'UTF-8'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 field-goods" style="display:none;">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="good_quantity" min="1" value="1" />
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Purpose <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="purpose" required placeholder="Example: Design workshop and equipment use" />
                                        <small class="text-muted">Briefly explain why the item or room is needed.</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Start date and time <span class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control" name="start_date" required />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">End date and time <span class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control" name="end_date" required />
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Notes</label>
                                        <textarea class="form-control" name="notes" rows="4" placeholder="Optional notes, room setup, maintenance details, or support requirements"></textarea>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="loans.php" class="btn btn-outline-secondary">Back</a>
                                    <button type="submit" class="btn btn-primary">Save request</button>
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
    <script>
        const requestType = document.getElementById('request_type');
        const facilityFields = document.querySelectorAll('.field-facility');
        const goodsFields = document.querySelectorAll('.field-goods');

        function updateRequestForm() {
            const isGoods = requestType.value === 'Goods';
            facilityFields.forEach(el => el.style.display = isGoods ? 'none' : 'block');
            goodsFields.forEach(el => el.style.display = isGoods ? 'block' : 'none');
        }

        requestType.addEventListener('change', updateRequestForm);
        updateRequestForm();
    </script>
</body>
</html>
