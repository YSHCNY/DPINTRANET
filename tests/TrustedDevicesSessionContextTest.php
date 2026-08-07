<?php

$controllerPath = __DIR__ . '/../app/controllers/TrustedDevicesController.php';
$controller = file_get_contents($controllerPath);

if (strpos($controller, '$_GET[\'user_type\'') !== false || strpos($controller, '$_POST[\'user_type\'') !== false) {
    throw new RuntimeException('TrustedDevicesController still depends on client-controlled user_type values.');
}

if (strpos($controller, 'resolveAuthenticatedUser()') === false && strpos($controller, 'resolveAuthenticatedUser') === false) {
    throw new RuntimeException('TrustedDevicesController is missing the single session-based user context resolver.');
}

if (strpos($controller, '$_SESSION[\'user_type\'') === false || strpos($controller, '$_SESSION[\'user_id\'') === false) {
    throw new RuntimeException('TrustedDevicesController must resolve trusted device rows from explicit session user_type and user_id values.');
}

$authController = file_get_contents(__DIR__ . '/../app/controllers/AuthController.php');
if (strpos($authController, '$_SESSION[\'user_type\'] = \'admin\'') === false) {
    throw new RuntimeException('Admin authentication must persist an explicit admin user_type in session.');
}

$portalController = file_get_contents(__DIR__ . '/../app/controllers/StandardPortalController.php');
if (strpos($portalController, '$_SESSION[\'user_type\'] = \'standard\'') === false) {
    throw new RuntimeException('Portal authentication must persist an explicit standard user_type in session.');
}

$standardView = file_get_contents(__DIR__ . '/../app/views/standard_portal/profile_settings.php');
$adminView = file_get_contents(__DIR__ . '/../app/views/auth/profile.php');

foreach ([$standardView, $adminView] as $viewContent) {
    if (strpos($viewContent, 'user_type=') !== false || strpos($viewContent, "fd.append('user_type'") !== false) {
        throw new RuntimeException('A trusted devices view still sends user_type to the controller.');
    }
}

echo "Trusted devices session-context regression checks passed.";
