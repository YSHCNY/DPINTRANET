<?php
class Database {
    public static function connect() {
        return new PDO(
            "mysql:host=localhost;dbname=daltondb",
            "",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
}

