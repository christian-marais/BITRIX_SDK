<?php
namespace NS2B\SDK\DATABASE;
use NS2B\SDK\DATABASE\DatabaseInterface;
use \PDO;
use \PDOException;  
class DatabaseSqlite implements DatabaseInterface {
    protected $pdo;
    protected $database;
    protected $databasePath;
    protected $fields;
    protected $entity;
    protected $databaseCollection;

    public function __construct($database='database'){
        try{
            $this->createDatabase($database);
        }catch(PDOException $e){
            error_log($e->getMessage(),destination: __DIR__ . '/error.log');
            throw new \PDOException($e->getMessage());
        }
    }
    public function getDatabaseName() :string {
        return $this->database;
    }
    public function setFields(array$fields) :self{
        $this->fields = $fields;
        return $this;
    }
    public function getFields() :array{
        return $this->fields;
    }
    public function __destruct(){
        $this->pdo = null;
    }

    public function dbExists($database=null) :bool{
        return file_exists($this->databasePath = __DIR__ . '/' . ($database??$this->database) . '.sqlite');
    }

    public function createDatabase(string $databaseName) :self{
        try{
            
            $this->database = $databaseName;
            $this->databasePath = __DIR__ . '/' . $databaseName . '.sqlite';
            $this->pdo = new PDO('sqlite:' . $this->databasePath);
            $this->databaseCollection["history"][]="Création de la base de données $databaseName";
        }catch(PDOException $e){
            error_log($e->getMessage(),destination:__DIR__ . '/error.log');
        }
        return $this;
    }

    public function dropDatabase(string $databaseName) {
        $databasePath = $databaseName?__DIR__ . '/' .$databaseName.'.sqlite':$this->databasePath ;
        if(file_exists($databasePath)){
            unlink($databasePath);
            $this->databaseCollection["history"][]="Suppression de la base de données $databaseName réussie";
        }
        
        return $this;
    }

    public function listEntities() {
        $stmt = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type='table'");
        $stmt->execute();
        $this->databaseCollection["entities"] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $this->databaseCollection["history"][]="Liste des entités de la base de données $this->database";
        return $this;
    }
    
    public function entityExists(string $entityName) :bool{
        $stmt = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = :name");
        $stmt->bindValue(':name', $entityName);
        $stmt->execute();
        return $this->databaseCollection[$entityName.'Exists'] = $stmt->fetch() !== false;
    }
    

    public function createEntity(string $entityName, array $data) {
        // Sanitiser le nom de l'entité
        if(empty($entityName)||empty($data)||!is_array($data))throw new \Exception('Impossible de créer l\'entité;pdo ou entityName ou data invalide');
        $this->entityExists($entityName??$this->entity);
        if($this->databaseCollection[$entityName.'Exists']){
            return $this;
        }
        $entityName = $this->sanitizeIdentifier($entityName);

        // Créer la table si elle n'existe pas
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = $this->sanitizeIdentifier($key) . ' ' . $value;
        }
        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS $entityName (id INTEGER PRIMARY KEY AUTOINCREMENT, " . implode(", ", $fields) . ")");
        $stmt->execute([]);
        $this->databaseCollection["history"][]='Insertion de l\'entité '.$entityName.' '.($this->entityExists($entityName)?'reussie':'échouée');
        return $this;
    }

    public function dropEntity(string $entityName) {
        if(empty($this->pdo)||empty($entityName))throw new \Exception('Pdo ou nom de l\'entité invalide');
        $stmt = $this->pdo->prepare("DROP TABLE IF EXISTS " . $this->sanitizeIdentifier($entityName));
        $stmt->execute([]);
        $this->databaseCollection["history"][]='Suppression de l\'entité '.$entityName.' '.(!$this->entityExists($entityName)?'reussie':'échouée');
        return $this;
    }

    public function listFields(string $entityName) {
        $stmt = $this->pdo->prepare("PRAGMA table_info(" . $this->sanitizeIdentifier($entityName) . ")");
        $stmt->execute();
        $this->databaseCollection["fields"] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->databaseCollection["history"][]='Liste des champs de l\'entité $entityName '.(implode(", ", $this->databaseCollection["fields"]??[]));
        return $this;
    }

    public function addField(string $entityName, string $fieldName, string $fieldType) {
        $stmt = $this->pdo->prepare("ALTER TABLE " . $this->sanitizeIdentifier($entityName) . " ADD COLUMN " . $this->sanitizeIdentifier($fieldName) . " $fieldType");
        $this->databaseCollection["history"][]='Ajout du champ '.$fieldName.' de type '.$fieldType.' à l\'entité '.$entityName.' '.($stmt->execute()?'reussie':'échouée');
        return $this;
    }

    public function deleteField(string $entityName, string $fieldName) {
        // Get current table structure
        $stmt = $this->pdo->prepare("PRAGMA table_info(" . $this->sanitizeIdentifier($entityName) . ")");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Create new table without the field
        $newTableName = $entityName . '_new';
        $fields = array_diff($columns, [$fieldName]);
        
        $this->pdo->beginTransaction();
        try {
            // Create new table
            $fieldsDefinition = implode(", ", array_map(function($field) { return "$field TEXT"; }, $fields));
            $stmt = $this->pdo->prepare("CREATE TABLE " . $this->sanitizeIdentifier($newTableName) . " (id INTEGER PRIMARY KEY AUTOINCREMENT, $fieldsDefinition)");
            $stmt->execute();
            
            // Copy data
            $fieldList = implode(", ", $fields);
            $stmt = $this->pdo->prepare("INSERT INTO " . $this->sanitizeIdentifier($newTableName) . " (id, $fieldList) SELECT id, $fieldList FROM " . $this->sanitizeIdentifier($entityName));
            $stmt->execute();
            
            // Drop old table
            $stmt = $this->pdo->prepare("DROP TABLE " . $this->sanitizeIdentifier($entityName));
            $stmt->execute();
            
            // Rename new table
            $stmt = $this->pdo->prepare("ALTER TABLE " . $this->sanitizeIdentifier($newTableName) . " RENAME TO " . $this->sanitizeIdentifier($entityName));
            $stmt->execute();
            
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
        $this->databaseCollection["history"][]='Suppression du champ '.$fieldName.' de l\'entité '.$entityName;
        return $this;
    }
    
    public function updateField(string $entityName, string $oldFieldName, string $newFieldName) {
        $stmt = $this->pdo->prepare("PRAGMA table_info(" . $this->sanitizeIdentifier($entityName) . ")");
        $stmt->execute([]);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $columns = array_map(function($column) use ($oldFieldName, $newFieldName) {
            if ($column['name'] === $oldFieldName) {
                $column['name'] = $newFieldName;
            }
            return $column;
        }, $columns);
        
        $this->pdo->beginTransaction();
        try {
            // Create new table
            $fieldsDefinition = implode(", ", array_map(function($column) { return "{$column['name']} {$column['type']}"; }, $columns));
            $newTableName = $entityName . '_new';
            $stmt = $this->pdo->prepare("CREATE TABLE " . $this->sanitizeIdentifier($newTableName) . " (id INTEGER PRIMARY KEY AUTOINCREMENT, $fieldsDefinition)");
            $stmt->execute();
            
            // Copy data
            $fieldList = implode(", ", array_column($columns, 'name'));
            $stmt = $this->pdo->prepare("INSERT INTO " . $this->sanitizeIdentifier($newTableName) . " (id, $fieldList) SELECT id, $fieldList FROM " . $this->sanitizeIdentifier($entityName));
            $stmt->execute();
            
            // Drop old table
            $stmt = $this->pdo->prepare("DROP TABLE " . $this->sanitizeIdentifier($entityName));
            $stmt->execute();
            
            // Rename new table
            $stmt = $this->pdo->prepare("ALTER TABLE " . $this->sanitizeIdentifier($newTableName) . " RENAME TO " . $this->sanitizeIdentifier($entityName));
            $stmt->execute();
            
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    public function insert(string $entityName, array $fields) {
        $parameters = array_map(function($field) { 
            return '?'; 
        }, array_keys($fields));
        $sql="INSERT INTO " . $this->sanitizeIdentifier($entityName) . " (" . implode(", ", array_keys($fields)) . ") VALUES (" . implode(", ",$parameters) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($fields));
        $this->databaseCollection["history"][]='Insertion de l\'entité '.$entityName.' '.($this->entityExists($entityName)?'reussie':'échouée');
        return $this;
    }

    public function onlyAllowedFields(array $fields){
        return $this->databaseCollection["allowedFields"] = array_filter($fields, function($field) {
            return in_array($field, array_values($this->fields));
        });
    }

    public function inserts(string $entityName, array $elements) {
        array_map(function($element)use($entityName){
            $this->insert($entityName, $element);
        }, $elements);
        return $this;
    }

    public function update(string $entityName, array $fields, $id) {
        $setClause = implode(", ", array_map(function($field) { return "$field = :$field"; }, array_keys($fields)));
        $stmt = $this->pdo->prepare("UPDATE " . $this->sanitizeIdentifier($entityName) . " SET $setClause WHERE id = :id");
        
        foreach ($fields as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $this->databaseCollection["history"][]='Mise à jour de l\'entité '.$entityName;
        return $this;
    }

    public function select(string $entityName, array $fieldNames) {
        // Sanitiser le nom de l'entité
        $entityName = $this->sanitizeIdentifier($entityName);

        // Sanitiser les noms des champs
        $sanitizedFields = array_map([$this, 'sanitizeIdentifier'], $fieldNames);
        $fieldsList = implode(", ", $sanitizedFields);

        $stmt = $this->pdo->prepare("SELECT " . $fieldsList . " FROM " . $entityName);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selects(string $entityName) {
        if($this->dbExists() && $this->entityExists($entityName)){
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
            foreach ($criteria as $field => $value) {
                $whereClauses[] = $this->sanitizeIdentifier($field) . " = :$field";
            }
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }
        
        $stmt = $this->pdo->prepare($query);
        
        foreach ($criteria as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateWhere(string $entityName, array $fields, array $where) {
        $setClause = implode(", ", array_map(function($field) { return "$field = :$field"; }, array_keys($fields)));
        $query = "UPDATE " . $this->sanitizeIdentifier($entityName) . " SET $setClause";
        $whereClauses = [];
        
        if (!empty($where)) {
            foreach ($where as $field => $value) {
                $whereClauses[] = $this->sanitizeIdentifier($field) . " = :$field";
            }
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }
        
        $stmt = $this->pdo->prepare($query);
        
        foreach ($fields as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        foreach ($where as $field => $value) {
            $stmt->bindValue(":$field", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $entityName, $id) {
        $stmt = $this->pdo->prepare("DELETE FROM " . $this->sanitizeIdentifier($entityName) . " WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function truncate(string $entityName) {
        $stmt = $this->pdo->prepare("DELETE FROM " . $this->sanitizeIdentifier($entityName));
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteAll(string $entityName) {
        $this->truncate($entityName);
    }

    protected function sanitizeIdentifier(string $identifier): string {
        // Échapper les caractères indésirables pour les noms de tables et de colonnes
        return preg_replace('/[^a-zA-Z0-9_]/', '', $identifier);
    }
}
