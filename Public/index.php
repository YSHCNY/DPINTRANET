<?php
ob_start();

// 1. Routing Logic & Controller Handling
$controller = $_GET['controller'] ?? 'Auth';
$action = $_GET['action'] ?? 'login';

// Detect AJAX/API requests by header or Ajax-style action naming.
$isAjax = false;
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $isAjax = true;
} elseif (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    $isAjax = true;
} elseif (is_string($action) && str_ends_with($action, 'Ajax')) {
    $isAjax = true;
}

if ($isAjax) {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);

    function sendJsonAjaxError(int $code, string $message): void {
        if (ob_get_length() !== false) {
            @ob_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    set_error_handler(function(int $errno, string $errstr, string $errfile, int $errline) {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });

    set_exception_handler(function(Throwable $exception) {
        sendJsonAjaxError(500, 'Internal server error');
    });

    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
            sendJsonAjaxError(500, 'Internal server error');
        }
    });
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/core/bootstrap.php';

$controllerFile = __DIR__ . "/../app/controllers/{$controller}Controller.php";
if (!file_exists($controllerFile)) {
    if ($isAjax) {
        sendJsonAjaxError(404, 'Not found');
    }
    http_response_code(404);
    exit;
}

require_once $controllerFile;
$class = $controller . 'Controller';
if (!class_exists($class, false)) {
    if ($isAjax) {
        sendJsonAjaxError(404, 'Not found');
    }
    http_response_code(404);
    exit;
}

$ctrl = new $class();
$id = $_POST['id'] ?? $_GET['id'] ?? null;

if ($isAjax) {
    try {
        if (!is_callable([$ctrl, $action])) {
            sendJsonAjaxError(404, 'Not found');
        }

        if ($id !== null) {
            $ctrl->$action($id);
        } else {
            $ctrl->$action();
        }
    } catch (Throwable $exception) {
        sendJsonAjaxError(500, 'Internal server error');
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