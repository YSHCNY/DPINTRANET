<?php
$controller = file_get_contents(__DIR__ . '/../app/controllers/StandardPortalController.php');
if (strpos($controller, 'public function profileSettings()') === false) {
    throw new RuntimeException('StandardPortalController is missing profileSettings().');
}
if (strpos($controller, 'public function updateProfileSettings()') === false) {
    throw new RuntimeException('StandardPortalController is missing updateProfileSettings().');
}
echo "Standard portal profile settings actions are present.\n";
