<?php

namespace App\Models;

use PDO;
use PDOException;
use Exception;
use DateTime;
use ReflectionClass;

abstract class BaseModel {
    protected static ?PDO $db = null;
    protected static string $table = "";

    public function __construct() {
        self::initDatabase();
    }

    protected static function initDatabase(): void {
        if (self::$db === null) {
            try {
                $dsn = "mysql:host=db8;port=3306;dbname=geo;charset=utf8mb4";
                self::$db = new PDO($dsn, "root", "root");
                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
    }

    public function save(): bool {
        self::initDatabase();

        $attributes = get_object_vars($this);
        unset($attributes['db'], $attributes['table'], $attributes['id']);

        foreach ($attributes as $key => $value) {
            if ($value instanceof DateTime) {
                $attributes[$key] = $value->format('Y-m-d H:i:s');
            }
        }

        $columns = implode(", ", array_keys($attributes));
        $placeholders = implode(", ", array_fill(0, count($attributes), "?"));

        $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";
        $stmt = self::$db->prepare($sql);
        $success = $stmt->execute(array_values($attributes));

        if ($success && property_exists($this, 'id')) {
            $this->id = (int) self::$db->lastInsertId();
        }

        return $success;
    }

    public static function find(int $id): ?self {
        self::initDatabase();

        $sql = "SELECT * FROM " . static::$table . " WHERE id = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        $reflection = new ReflectionClass(static::class);
        $object = $reflection->newInstanceWithoutConstructor();

        foreach ($data as $key => $value) {
            if (property_exists($object, $key)) {
                if ($value !== null && strpos($key, '_at') !== false) {
                    $object->$key = new DateTime($value);
                } else {
                    $object->$key = $value;
                }
            }
        }

        return $object;
    }


    public function update(array $data): bool {
        self::initDatabase();

        if (!isset($this->id)) {
            throw new Exception("Cannot update record without ID.");
        }

        $updates = [];
        $values = [];

        foreach ($data as $key => $value) {
            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d H:i:s');
            }
            $updates[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $this->id;

        $sql = "UPDATE " . static::$table . " SET " . implode(", ", $updates) . " WHERE id = ?";
        $stmt = self::$db->prepare($sql);

        return $stmt->execute($values);
    }
}
