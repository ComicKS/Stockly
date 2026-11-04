<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();
requireRole('Operator');

$system = new BorrowingSystem();

if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $system->deleteGood((int) $_GET['id']);
    header('Location: goods.php?status=deleted');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: goods.php');
    exit;
}

$data = [
    'id' => isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null,
    'name' => $_POST['name'] ?? '',
    'inventory_code' => $_POST['inventory_code'] ?? '',
    'category' => $_POST['category'] ?? '',
    'location' => $_POST['location'] ?? '',
    'quantity' => isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0,
    'condition_status' => $_POST['condition_status'] ?? 'Good',
    'description' => $_POST['description'] ?? '',
    'maintenance_status' => $_POST['maintenance_status'] ?? 'Available',
    'maintenance_notes' => $_POST['maintenance_notes'] ?? '',
];

$success = $system->saveGood($data);
header('Location: goods.php?status=' . ($success ? 'saved' : 'error'));
exit;
