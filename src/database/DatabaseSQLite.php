<?php
namespace NS2B\SDK\DATABASE;

use \PDO;
class DatabaseSQLite implements DatabaseInterface {
    protected $pdo;
    protected $databasePath;

    public function __construct($databasePath=__DIR__ . '/database.sqlite') {
        $this->pdo = new PDO('sqlite:' . $databasePath);
        $this->databasePath = $databasePath;
    }
    public function dbExists() {
        return file_exists($this->databasePath);
    }

    public function createDatabase(string $databaseName) {
        // Logic to create a new SQLite database
        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS " . $databaseName);
    }

    public function dropDatabase(string $databaseName) {
        // Logic to drop an existing SQLite database
        $this->pdo->exec("DROP DATABASE IF EXISTS " . $databaseName);
    }

    public function createEntity(string $entityName, array $data) {
        $fields = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), '?'));
        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS " . $entityName . " ($fields) VALUES ($placeholders)");
        $stmt->execute(array_values($data));
    }

    public function updateEntityField(string $entityName, array $fields, $id) {
        $setClause = implode(", ", array_map(function($field) { return "$field = ?"; }, array_keys($fields)));
        $stmt = $this->pdo->prepare("UPDATE " . $entityName . " SET " . $setClause . " WHERE id = ?");
        $stmt->execute(array_merge(array_values($fields), [$id]));
    }

    public function addEntityField(string $entityName, string $fieldName, string $fieldType) {
        $this->pdo->exec("ALTER TABLE " . $entityName . " ADD COLUMN " . $fieldName . " " . $fieldType);
    }

    public function selectEntityField(string $entityName, string $fieldName) {
        $stmt = $this->pdo->prepare("SELECT " . $fieldName . " FROM " . $entityName);
        $stmt->execute();
        return $stmt->fetchAll();
    }

   public function deleteEntityField(string $entityName, string $fieldName) {
       // Get the current table structure
       $stmt = $this->pdo->prepare("PRAGMA table_info(" . $entityName . ")");
       $stmt->execute();
       $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
   
       // Create a new table without the field to delete
       $newTableName = $entityName . '_new';
       $fields = array_diff($columns, [$fieldName]);
       $fieldsDefinition = implode(", ", array_map(function($field) { return "$field TEXT"; }, $fields));
       $this->pdo->exec("CREATE TABLE " . $newTableName . " ($fieldsDefinition)");
   
       // Copy data from the old table to the new table, preserving ids
       $fieldList = implode(", ", $fields);
       $this->pdo->exec("INSERT INTO " . $newTableName . " (id, $fieldList) SELECT id, $fieldList FROM " . $entityName);
   
       // Drop the old table
       $this->pdo->exec("DROP TABLE " . $entityName);
   
       // Rename the new table to the old table name
       $this->pdo->exec("ALTER TABLE " . $newTableName . " RENAME TO " . $entityName);
   }

    public function selectEntity(string $entityName, array $criteria) {
        $query = "SELECT * FROM " . $entityName;
        if (!empty($criteria)) {
            $query .= " WHERE " . http_build_query($criteria, '', ' AND ');
        }
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function dropEntity(string $entityName) {
        $this->pdo->exec("DROP TABLE IF EXISTS " . $entityName);
    }

    public function entityExists(string $entityName) {
        $stmt = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=:entityName");
        $stmt->bindParam(':entityName', $entityName);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    public function listEntities() {
        $stmt = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type='table'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
