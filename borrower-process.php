<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();
requireRole('Operator');

$system = new BorrowingSystem();

if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $system->deleteBorrower((int) $_GET['id']);
    header('Location: borrowers.php?status=deleted');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: borrowers.php');
    exit;
}

$data = [
    'id' => isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null,
    'campus_id' => $_POST['campus_id'] ?? '',
    'full_name' => $_POST['full_name'] ?? '',
    'borrower_type' => $_POST['borrower_type'] ?? 'Student',
    'department' => $_POST['department'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'email' => $_POST['email'] ?? '',
    'active_status' => $_POST['active_status'] ?? 'Active',
];

$success = $system->saveBorrower($data);
header('Location: borrowers.php?status=' . ($success ? 'saved' : 'error'));
exit;
