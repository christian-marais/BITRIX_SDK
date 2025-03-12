<?php
namespace NS2B\SDK\DATABASE;
interface DatabaseInterface {
    public function __construct(string $databasePath);
    public function dbExists(string $databasePath);
    public function entityExists(string $entityName);
    public function listEntities();
    public function createEntity(string $entityName, array $data);
    public function updateEntityField(string $entityName, string $fieldName, $value);
    public function addEntityField(string $entityName, string $fieldName, string $fieldType);
    public function selectEntityField(string $entityName, string $fieldName);
    public function deleteEntityField(string $entityName, string $fieldName);
    public function selectEntity(string $entityName, array $criteria);
    public function dropEntity(string $entityName);
    public function createDatabase(string $databaseName);
    public function dropDatabase(string $databaseName);
    public function update(string $entityName, array $data);
    public function delete(string $entityName, array $criteria);
    public function select(string $entityName, array $criteria);
}
