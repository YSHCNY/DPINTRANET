<?php

echo "User: " . get_current_user() . "<br>";
echo "UID: " . getmyuid() . "<br>";
echo "Writable: ";

$dir = __DIR__ . "/../app/assets/profiles/";

var_dump(is_dir($dir));
echo "<br>";
var_dump(is_writable($dir));
echo "<br>";

file_put_contents($dir . "php-test.txt", "hello");