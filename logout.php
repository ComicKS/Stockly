<?php
require_once __DIR__ . '/config/auth.php';

$_SESSION = [];
if (session_id() !== '') {
    session_destroy();
}
header('Location: login.php');
exit;
