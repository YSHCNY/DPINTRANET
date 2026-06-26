<?php
class Database {
    public static function connect() {
        return new PDO(
            "mysql:host=localhost;dbname=daltondb",
            "phpmyadmin",
            "pkii@1111",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
}

