<?php
namespace NS2B\SDK\DATABASE;
use NS2B\SDK\DATABASE\DatabaseInterface;
use \PDO;
use \PDOException;  

class DatabaseMysql implements DatabaseInterface {
    protected $pdo;
    protected $database;
    protected $host;
    protected $username;
    protected $password;
    protected $fields;
    protected $entity;
    protected $databaseCollection;

    public function __construct($database='database', $host='localhost', $username='root', $password=''){
        try{
            $this->host = $host;
            $this->username = $username;
            $this->password = $password;
        }catch(PDOException $e){
            error_log($e->getMessage(), 3, __DIR__ . '/error.log');
            throw new \PDOException($e->getMessage());
        }
    }

    public function getDatabaseName() :string {
        return $this->database;
    }

    public function setFields(array $fields) :self {
        $this->fields = $fields;
        return $this;
    }

    public function getFields() :array {
        return $this->fields;
    }

    public function __destruct(){
        $this->pdo = null;
    }

    public function dbExists($database=null) :bool {
        try {
            $tempPdo = new PDO("mysql:host={$this->host}", $this->username, $this->password);
            $stmt = $tempPdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbname");
            $stmt->execute(['dbname' => $database ?? $this->database]);
            return $stmt->fetch() !== false;
        } catch(PDOException $e) {
            error_log($e->getMessage(), 3, __DIR__ . '/error.log');
            return false;
        }
    }

    public function createDatabase(string $databaseName) :self {
        try {
            $this->database = $databaseName;
            
            // First connect without database to create it if needed
            $tempPdo = new PDO("mysql:host={$this->host}", $this->username, $this->password);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS " . $this->sanitizeIdentifier($databaseName));
            
            // Now connect to the database
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$databaseName}", $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            if(empty($this->pdo))
                throw new \PDOException('PDO not initialized, please check extensions');
                
            $this->databaseCollection["history"][] = "Création de la base de données $databaseName";
        } catch(PDOException $e) {
            error_log($e->getMessage(), 3, __DIR__ . '/error.log');
            throw $e;
        }
        return $this;
    }

    public function dropDatabase(string $databaseName) {
        try {
            $this->pdo->exec("DROP DATABASE IF EXISTS " . $this->sanitizeIdentifier($databaseName));
            $this->databaseCollection["history"][] = "Suppression de la base de données $databaseName réussie";
        } catch(PDOException $e) {
            error_log($e->getMessage(), 3, __DIR__ . '/error.log');
        }
        return $this;
    }

    public function listEntities() {
        $stmt = $this->pdo->prepare("SHOW TABLES");
        $stmt->execute();
        $this->databaseCollection["entities"] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $this->databaseCollection["history"][] = "Liste des entités de la base de données $this->database";
        return $this;
    }
    
    public function entityExists(string $entityName) :bool {
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE :name");
        $stmt->bindValue(':name', $entityName);
        $stmt->execute();
        return $this->databaseCollection[$entityName.'Exists'] = $stmt->fetch() !== false;
    }

    public function createEntity(string $entityName, array $data) {
        if(empty($entityName) || empty($data) || !is_array($data)) 
            throw new \Exception('Impossible de créer l\'entité; pdo ou entityName ou data invalide');

        $this->entityExists($entityName ?? $this->entity);
        if($this->databaseCollection[$entityName.'Exists']) {
            return $this;
        }

        $entityName = $this->sanitizeIdentifier($entityName);
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = $this->sanitizeIdentifier($key) . ' ' . $value;
        }

        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS $entityName (id INT AUTO_INCREMENT PRIMARY KEY, " . implode(", ", $fields) . ") ENGINE=InnoDB");
        $stmt->execute();
        $this->databaseCollection["history"][] = 'Insertion de l\'entité '.$entityName.' '.($this->entityExists($entityName)?'reussie':'échouée');
        return $this;
    }

    public function dropEntity(string $entityName) {
        if(empty($this->pdo) || empty($entityName))
            throw new \Exception('Pdo ou nom de l\'entité invalide');
            
        $stmt = $this->pdo->prepare("DROP TABLE IF EXISTS " . $this->sanitizeIdentifier($entityName));
        $stmt->execute();
        $this->databaseCollection["history"][] = 'Suppression de l\'entité '.$entityName.' '.(!$this->entityExists($entityName)?'reussie':'échouée');
        return $this;
    }

    public function listFields(string $entityName) {
        $stmt = $this->pdo->prepare("DESCRIBE " . $this->sanitizeIdentifier($entityName));
        $stmt->execute();
        $this->databaseCollection["fields"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->databaseCollection["history"][] = 'Liste des champs de l\'entité '.$entityName.' '.(implode(", ", array_column($this->databaseCollection["fields"], 'Field')));
        return $this;
    }

    public function addField(string $entityName, string $fieldName, string $fieldType) {
        $stmt = $this->pdo->prepare("ALTER TABLE " . $this->sanitizeIdentifier($entityName) . " ADD COLUMN " . $this->sanitizeIdentifier($fieldName) . " $fieldType");
        $this->databaseCollection["history"][] = 'Ajout du champ '.$fieldName.' de type '.$fieldType.' à l\'entité '.$entityName.' '.($stmt->execute()?'reussie':'échouée');
        return $this;
    }

    public function deleteField(string $entityName, string $fieldName) {
        $stmt = $this->pdo->prepare("ALTER TABLE " . $this->sanitizeIdentifier($entityName) . " DROP COLUMN " . $this->sanitizeIdentifier($fieldName));
        $stmt->execute();
        $this->databaseCollection["history"][] = 'Suppression du champ '.$fieldName.' de l\'entité '.$entityName;
        return $this;
    }
    
    public function updateField(string $entityName, string $oldFieldName, string $newFieldName) {
        // Get the field type first
        $stmt = $this->pdo->prepare("DESCRIBE " . $this->sanitizeIdentifier($entityName) . " " . $this->sanitizeIdentifier($oldFieldName));
        $stmt->execute();
        $field = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($field) {
            $type = $field['Type'];
            $stmt = $this->pdo->prepare("ALTER TABLE " . $this->sanitizeIdentifier($entityName) . 
                " CHANGE COLUMN " . $this->sanitizeIdentifier($oldFieldName) . " " . 
                $this->sanitizeIdentifier($newFieldName) . " " . $type);
            $stmt->execute();
        }
        return $this;
    }

    public function insert(string $entityName, array $fields) {
        $parameters = array_map(function($field) { 
            return '?'; 
        }, array_keys($fields));
        
        $sql = "INSERT INTO " . $this->sanitizeIdentifier($entityName) . 
               " (" . implode(", ", array_keys($fields)) . ") VALUES (" . 
               implode(", ", $parameters) . ")";
               
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($fields));
        $this->databaseCollection["history"][] = 'Insertion de l\'entité '.$entityName.' '.($this->entityExists($entityName)?'reussie':'échouée');
        return $this;
    }

    public function onlyAllowedFields(array $fields) {
        return $this->databaseCollection["allowedFields"] = array_filter($fields, function($field) {
            return in_array($field, array_values($this->fields));
        });
    }

    public function inserts(string $entityName, array $elements) {
        array_map(function($element) use($entityName) {
            $this->insert($entityName, $element);
        }, $elements);
        return $this;
    }

    public function update(string $entityName, array $fields, $id) {
        $setClause = implode(", ", array_map(function($field) { 
            return "$field = :$field"; 
        }, array_keys($fields)));
        
        $stmt = $this->pdo->prepare("UPDATE " . $this->sanitizeIdentifier($entityName) . 
                                   " SET $setClause WHERE id = :id");
        
        foreach ($fields as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $this->databaseCollection["history"][] = 'Mise à jour de l\'entité '.$entityName;
        return $this;
    }

    public function select(string $entityName, array $fieldNames) {
        $entityName = $this->sanitizeIdentifier($entityName);
        $sanitizedFields = array_map([$this, 'sanitizeIdentifier'], $fieldNames);
        $fieldsList = implode(", ", $sanitizedFields);

        $stmt = $this->pdo->prepare("SELECT " . $fieldsList . " FROM " . $entityName);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selects(string $entityName) {
        if($this->dbExists() && $this->entityExists($entityName)) {
            $entityName = $this->sanitizeIdentifier($entityName);
            $stmt = $this->pdo->prepare("SELECT * FROM " . $entityName);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    public function selectWhere(string $entityName, array $criteria) {
        $query = "SELECT * FROM " . $this->sanitizeIdentifier($entityName);
        $whereClauses = [];
        
        if (!empty($criteria)) {
            foreach ($criteria as $value) {
                $whereClauses[] = $this->sanitizeIdentifier($value["field"]) .' '.((strtolower($value["operator"])=="in")?')':"").$value["operator"]??"=".' :'.$value["value"].((strtolower($value["operator"])=="in")?')':"");
            }
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }
        
        $stmt = $this->pdo->prepare($query);
        die($stmt);
        foreach ($criteria as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateWhere(string $entityName, array $fields, array $where) {
        $setClause = implode(", ", array_map(function($field) { 
            return "$field = :set_$field"; 
        }, array_keys($fields)));
        
        $query = "UPDATE " . $this->sanitizeIdentifier($entityName) . " SET $setClause";
        $whereClauses = [];
        
        if (!empty($where)) {
            foreach ($where as $field => $value) {
                $whereClauses[] = $this->sanitizeIdentifier($field) . " = :where_$field";
            }
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }
        
        $stmt = $this->pdo->prepare($query);
        
        foreach ($fields as $field => $value) {
            $stmt->bindValue(":set_$field", $value);
        }
        
        foreach ($where as $field => $value) {
            $stmt->bindValue(":where_$field", $value);
        }
        
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function deleteWhere(string $entityName, array $where) {
        $query = "DELETE FROM " . $this->sanitizeIdentifier($entityName);
        $whereClauses = [];
        
        if (!empty($where)) {
            foreach ($where as $field => $value) {
                $whereClauses[] = $this->sanitizeIdentifier($field) . " = :$field";
            }
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }
        
        $stmt = $this->pdo->prepare($query);
        
        foreach ($where as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function delete(string $entityName, $id) {
        $stmt = $this->pdo->prepare("DELETE FROM " . $this->sanitizeIdentifier($entityName) . " WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function truncate(string $entityName) {
        $stmt = $this->pdo->prepare("TRUNCATE TABLE " . $this->sanitizeIdentifier($entityName));
        $stmt->execute();
        return true;
    }

    public function deleteAll(string $entityName) {
        return $this->truncate($entityName);
    }

    /**
     * Sanitize SQL identifiers (table names, column names, etc.)
     * Handles all special characters and potential SQL injection attempts
     * @param string $identifier The identifier to sanitize
     * @return string The sanitized identifier
     * @throws \InvalidArgumentException If identifier contains invalid characters
     */
    protected function sanitizeIdentifier(string $identifier): string {
        $identifier = preg_replace('/[\x00-\x1F\x7F]/', '', $identifier);
        if (!preg_match('/^[a-zA-Z0-9_$@.\-]+$/', $identifier)) {
            _error_log('Invalid identifier: contains unauthorized characters');
            echo 'Warning: BAD behavior detected - Invalid identifier: contains unauthorized characters';
            exit;
        }
        $identifier = strtolower($identifier);
        $identifier = str_replace(["`", "``"], ["``", "```"], $identifier);
        return "`{$identifier}`";
    }
    
}