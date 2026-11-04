<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';
requireLogin();
requireRole('Operator');

$system = new BorrowingSystem();

if (isset($_GET['action']) && $_GET['action'] === 'delete' && !empty($_GET['id'])) {
    $system->deleteFacility((int) $_GET['id']);
    header('Location: facilities.php?status=deleted');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: facilities.php');
    exit;
}

$data = [
    'id' => isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null,
    'name' => $_POST['name'] ?? '',
    'building' => $_POST['building'] ?? '',
    'capacity' => $_POST['capacity'] ?? 1,
    'description' => $_POST['description'] ?? '',
    'availability_status' => $_POST['availability_status'] ?? 'Available',
    'maintenance_status' => $_POST['maintenance_status'] ?? 'Operational',
    'maintenance_notes' => $_POST['maintenance_notes'] ?? '',
];

$success = $system->saveFacility($data);
header('Location: facilities.php?status=' . ($success ? 'saved' : 'error'));
exit;
