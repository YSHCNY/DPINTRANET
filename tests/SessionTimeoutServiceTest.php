<?php
require_once __DIR__ . '/../app/Services/Security/SessionTimeoutService.php';

use App\Services\Security\SessionTimeoutService;

session_start();
$_SESSION = [];
$_SESSION['user'] = 'tester';
$_SESSION['last_activity'] = time() - 1200;

$service = new SessionTimeoutService(900, true);
if (!$service->isExpired()) {
    fwrite(STDERR, "Expected an expired session to be detected.\n");
    exit(1);
}

$service->clearSession();
if (!empty($_SESSION['user'])) {
    fwrite(STDERR, "Expected the session to be cleared after timeout.\n");
    exit(1);
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

session_start();
$_SESSION = [];
$_SESSION['user'] = 'tester';
$_SESSION['user_id'] = 42;
$_SESSION['user_level'] = 1;
$_SESSION['last_activity'] = time() - 1200;
$_SESSION['session_expired'] = true;
$_SESSION['session_expired_message'] = 'Session expired';
$_SESSION['session_recovery_user'] = [
    'id' => (int) ($_SESSION['user_id'] ?? 0),
    'username' => (string) ($_SESSION['user'] ?? ''),
    'userLevel' => (int) ($_SESSION['user_level'] ?? 3),
];
$_SESSION['session_recovery_user_id'] = (int) ($_SESSION['user_id'] ?? 0);

$service = new SessionTimeoutService(300, true);
$service->clearSession(['session_expired', 'session_expired_message', 'session_recovery_user', 'session_recovery_user_id']);
if (empty($_SESSION['session_expired']) || $_SESSION['session_expired_message'] !== 'Session expired' || empty($_SESSION['session_recovery_user']['username']) || empty($_SESSION['session_recovery_user_id'])) {
    fwrite(STDERR, "Expected preserved session flags and recovery identity to survive timeout cleanup.\n");
    exit(1);
}

$defaultService = new SessionTimeoutService();
if ($defaultService->getTimeoutMinutes() !== 15) {
    fwrite(STDERR, "Expected the default timeout to be fifteen minutes.\n");
    exit(1);
}

echo "Session timeout service test passed\n";
