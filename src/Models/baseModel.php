<?php

namespace App\Models;

use \PDO;
use \PDOException;

abstract class BaseModel {
    protected static ?PDO $db = null;
    protected static string $table;

    public static function initDatabase(?PDO $connection = null): void {
        if ($connection) {
            self::$db = $connection;
            return;
        }

        if (self::$db === null) {
            try {
                self::$db = new PDO("mysql:host=geo-db8-1;dbname=geo", "root", "root");
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("❌ Database connection failed: " . $e->getMessage());
            }
        }
    }

    public function create(array $data): bool {
        self::initDatabase();
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";

        $stmt = self::$db->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    public static function all(): array {
        self::initDatabase();
        $sql = "SELECT * FROM " . static::$table;
        $stmt = self::$db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array {
        self::initDatabase();
        $sql = "SELECT * FROM " . static::$table . " WHERE id = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function delete(int $id): bool {
        self::initDatabase();
        $sql = "DELETE FROM " . static::$table . " WHERE id = ?";
        $stmt = self::$db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
