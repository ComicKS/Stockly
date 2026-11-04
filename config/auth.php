<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function currentUser() {
    return $_SESSION['stockly_user'] ?? null;
}

function currentUserRole() {
    $user = currentUser();
    return $user['role'] ?? null;
}

function isLoggedIn() {
    return !empty($_SESSION['stockly_user']['id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($allowedRoles) {
    requireLogin();
    $role = currentUserRole();
    $allowed = array_map('strval', (array) $allowedRoles);
    if (!in_array((string) $role, $allowed, true)) {
        header('Location: index.php');
        exit;
    }
}

function setFlash($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlash() {
    $message = $_SESSION['flash_message'] ?? null;
    $type = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    return [$message, $type];
}
