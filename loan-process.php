<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();

$system = new BorrowingSystem();
$currentUser = currentUser();

if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $loanId = (int) $_GET['id'];
    $loan = $system->getLoanById($loanId);
    $canDelete = (($currentUser['role'] ?? 'User') === 'Operator') || (($loan && (int) $loan['borrower_id'] === (int) ($currentUser['borrower_id'] ?? 0)) && in_array($loan['status'], ['Pending', 'Cancelled'], true));
    if ($canDelete) {
        $system->deleteLoan($loanId);
        setFlash('Loan record deleted.', 'success');
    } else {
        setFlash('You are not allowed to delete this request.', 'danger');
    }
    header('Location: loans.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: loans.php');
    exit;
}

if (($_POST['action'] ?? '') === 'update_status') {
    if (($currentUser['role'] ?? 'User') !== 'Operator') {
        setFlash('Only operators can update loan status.', 'danger');
        header('Location: my-requests.php');
        exit;
    }

    $loanId = !empty($_POST['loan_id']) ? (int) $_POST['loan_id'] : 0;
    $status = $_POST['status'] ?? 'Pending';
    $extra = [
        'issue_date' => $_POST['issue_date'] ?? null,
        'return_date' => $_POST['return_date'] ?? null,
        'condition_on_return' => $_POST['condition_on_return'] ?? null,
    ];
    $success = $system->updateLoanStatus($loanId, $status, $extra);
    if ($success) {
        setFlash('Loan status updated successfully.', 'success');
    } else {
        setFlash('The selected status is not valid for this request or the item is no longer available.', 'danger');
    }
    header('Location: loan-detail.php?id=' . $loanId);
    exit;
}

$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
if ($startDate !== '' && $endDate !== '' && strtotime($endDate) <= strtotime($startDate)) {
    setFlash('The end date and time must be later than the start date and time.', 'danger');
    header('Location: loan-form.php');
    exit;
}

$data = [
    'borrower_id' => !empty($_POST['borrower_id']) ? (int) $_POST['borrower_id'] : 0,
    'request_type' => $_POST['request_type'] ?? 'Facility',
    'facility_id' => !empty($_POST['facility_id']) ? (int) $_POST['facility_id'] : null,
    'purpose' => $_POST['purpose'] ?? '',
    'notes' => $_POST['notes'] ?? '',
    'start_date' => $startDate,
    'end_date' => $endDate,
    'due_date' => $endDate,
    'status' => 'Pending',
    'request_date' => date('Y-m-d H:i:s'),
];

if (($currentUser['role'] ?? 'User') === 'User') {
    $defaultBorrowerId = (int) ($currentUser['borrower_id'] ?? 0);
    if ($defaultBorrowerId <= 0 || $data['borrower_id'] !== $defaultBorrowerId) {
        setFlash('Users can only submit requests for their own borrower record.', 'danger');
        header('Location: loan-form.php');
        exit;
    }
}

if (($data['request_type'] ?? '') === 'Goods') {
    $goodId = !empty($_POST['good_id']) ? (int) $_POST['good_id'] : 0;
    $quantity = !empty($_POST['good_quantity']) ? (int) $_POST['good_quantity'] : 0;
    $data['goods'] = [[
        'good_id' => $goodId,
        'quantity' => $quantity,
    ]];
}

$loanId = $system->saveLoan($data);
if ($loanId) {
    setFlash('Your request has been submitted and is now pending operator review.', 'success');
    $redirect = (($currentUser['role'] ?? 'User') === 'Operator') ? 'loans.php' : 'my-requests.php';
    header('Location: ' . $redirect);
} else {
    setFlash('The selected item is not available for the chosen time range, or the request is invalid. Please review the booking details and try again.', 'danger');
    header('Location: loan-form.php');
}
exit;
