<?php
ob_start();
require_once __DIR__ . '/../app/config.php';

// 1. Error Reporting (Keep this during development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Routing Logic & Controller Handling
$controller = $_GET['controller'] ?? 'Auth';
$action = $_GET['action'] ?? 'login';

// Detect XHR (AJAX) requests
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

require_once "../app/controllers/{$controller}Controller.php";

$class = $controller . 'Controller';
$ctrl = new $class();

$id = $_POST['id'] ?? $_GET['id'] ?? null;

// If it's an AJAX request, execute controller immediately and stop to prevent HTML wrapping
if ($isAjax) {
    if ($id !== null) {
        $ctrl->$action($id);
    } else {
        $ctrl->$action();
    }
    exit; 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DPEARP Intranet</title>
    
    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <link rel="icon" type="image/png" href="<?= BASE_URL ?>uploads/logo/official.png" /> 
</head>
<body>

<?php
// 3. Standard Page Render (Non-AJAX)
if ($id !== null) {
    $ctrl->$action($id);
} else {
    $ctrl->$action();
}
?>

</body>
</html>