<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/class-borrowing-system.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$system = new BorrowingSystem();
$message = '';
$messageType = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $user = $system->loginUser($email, $password);

    if ($user) {
        $_SESSION['stockly_user'] = [
            'id' => (int) $user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'borrower_id' => $user['borrower_id'] ? (int) $user['borrower_id'] : null,
        ];
        header('Location: index.php');
        exit;
    }

    $message = 'Invalid email or password. Please try again.';
    $messageType = 'danger';
}

[$flashMessage, $flashType] = getFlash();
if ($flashMessage !== null) {
    $message = $flashMessage;
    $messageType = $flashType;
}
?>
<!doctype html>
<html lang="en">
<head>
    <?php include __DIR__ . '/template/header.php'; ?>
    <style>
        body {
            background: #f3f5f7;
        }
        .login-box {
            max-width: 420px;
            margin: 90px auto;
        }
        .card {
            border: 1px solid #dfe3e8;
            border-radius: 0.95rem;
            box-shadow: 0 12px 28px rgba(15, 76, 92, 0.08);
        }
        .btn-primary {
            background-color: #0f4c5c;
            border-color: #0f4c5c;
        }
        .btn-primary:hover {
            background-color: #1a6e8d;
            border-color: #1a6e8d;
        }
    </style>
</head>
<body>
    <div class="container login-box">
        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <img src="assets/img/logo.png" alt="IDB Bali logo" class="brand-image mb-3" style="width:72px;height:72px;" />
                    <h2 class="fw-bold mb-1">Stockly</h2>
                    <p class="text-muted mb-0">IDB Bali Campus Borrowing System</p>
                </div>

                <?php if ($message !== ''): ?>
                    <div class="alert alert-<?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?> rounded-3"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form method="post" action="login.php">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required placeholder="student@idbbali.ac.id" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required placeholder="Enter password" />
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Sign in</button>
                </form>

                <div class="mt-4 small text-muted">
                    <strong>Demo accounts</strong><br>
                    User: student@idbbali.ac.id / password123<br>
                    Operator: operator@idbbali.ac.id / operator123
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/template/script.php'; ?>
</body>
</html>
