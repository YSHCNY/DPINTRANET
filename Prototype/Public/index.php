<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DALTON BYPASS - FMS</title>
    <script src="https://cdn.tailwindcss.com"></script>

<!-- Quill.js -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<!-- Alpine.js (for Recipients chips) -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Favicon -->
<link rel="icon" type="image/png" href=".././app/assets/logo/icon.png" />

</head>


<body>



<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$controller = $_GET['controller'] ?? 'Auth';
$action = $_GET['action'] ?? 'login';



require_once "../app/controllers/{$controller}Controller.php";

$class = $controller . 'Controller';
$ctrl = new $class();

$id = $_POST['id'] ?? $_GET['id'] ?? null;

if ($id !== null) {
    $ctrl->$action($id);
} else {
    $ctrl->$action();
}

?>

</body>

</body>
</html>
