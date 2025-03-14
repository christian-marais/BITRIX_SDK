<?php
namespace NS2B\SDK\DATABASE;
/**
 * section databases
 * 
 * @method dbExists($database=null)
 * @method createDatabase(string $databaseName)
 * @method dropDatabase(string $databaseName=null)
 * 
 * section entities
 * 
 * @method listEntities()
 * @method entityExists(string $entityName)
 * @method createEntity(string $entityName, array $data)
 * @method dropEntity(string $entityName)
 * @method listFields(string $entityName)
 * @method addField(string $entityName, string $fieldName, string $fieldType)
 * @method deleteField(string $entityName, string $fieldName)
 * @method updateField(string $entityName, string $oldFieldName, string $newFieldName)
 * 
 * section table entries
 * 
 * @method insert(string $entityName, array $fields)
 * @method onlyAllowedFields(array $fields)
 * @method inserts(string $entityName, array $elements)
 * @method update(string $entityName, array $fields, $id)
 * @method select(string $entityName, array $fieldNames)
 * @method selects(string $entityName)
 * @method selectWhere(string $entityName, array $criteria)
 * @method updateWhere(string $entityName, array $fields, array $where)
 * @method deleteWhere(string $entityName, array $where)
 * @method delete(string $entityName, $id)
 * @method truncate(string $entityName)
 * @method deleteAll(string $entityName)
 */
interface DatabaseInterface {
    // section databases
    /**
     * Vérifie si la base de données existe.
     *
     * @param string|null $database Chemin de la base de données.
     * @return bool True si la base de données existe, false sinon.
     */
    public function dbExists($database=null);

    /**
     * Crée une nouvelle base de données.
     *
     * @param string $databaseName Nom de la base de données.
     */
    public function createDatabase(string $databaseName);

    /**
     * Supprime une base de données.
     *
     * @param string|null $databaseName Nom de la base de données.
     */
    public function dropDatabase(string $databaseName);

    // section entities
    /**
     * Récupère la liste des entités.
     *
     * @return array Liste des entités.
     */
    public function listEntities();

    /**
     * Vérifie si une entité existe.
     *
     * @param string $entityName Nom de l'entité.
     * @return bool True si l'entité existe, false sinon.
     */
    public function entityExists(string $entityName);

    /**
     * Crée une nouvelle entité.
     *
     * @param string $entityName Nom de l'entité.
     * @param array $data Données de l'entité.
     */
    public function createEntity(string $entityName, array $data);

    /**
     * Supprime une entité.
     *
     * @param string $entityName Nom de l'entité.
     */
    public function dropEntity(string $entityName);

    /**
     * Récupère la liste des champs d'une entité.
     *
     * @param string $entityName Nom de l'entité.
     * @return array Liste des champs.
     */
    public function listFields(string $entityName);

    /**
     * Ajoute un nouveau champ à une entité.
     *
     * @param string $entityName Nom de l'entité.
     * @param string $fieldName Nom du champ.
     * @param string $fieldType Type du champ.
     */
    public function addField(string $entityName, string $fieldName, string $fieldType);

    /**
     * Supprime un champ d'une entité.
     *
     * @param string $entityName Nom de l'entité.
     * @param string $fieldName Nom du champ.
     */
    public function deleteField(string $entityName, string $fieldName);

    /**
     * Met à jour le nom d'un champ d'une entité.
     *
     * @param string $entityName Nom de l'entité.
     * @param string $oldFieldName Ancien nom du champ.
     * @param string $newFieldName Nouveau nom du champ.
     */
    public function updateField(string $entityName, string $oldFieldName, string $newFieldName);

    /**
     * Modifie la liste des champs d'une entité.
     *
     * @param array $fields Liste des champs.
     */
    public function setFields(array $fields);

    /**
     * Récupère la liste des champs.
     *
     * @return array Liste des champs.
     */
    public function getFields();

    /**
     * Insère une nouvelle entrée dans une entité.
     *
     * @param string $entityName Nom de l'entité.
     * @param array $fields Champs à insérer.
     */
    public function insert(string $entityName, array $fields);

    /**
     * Récupère les champs autorisés pour une entité.
     *
     * @param array $fields Champs à vérifier.
     * @return array Champs autorisés.
     */
    public function onlyAllowedFields(array $fields);

    /**
     * Insère plusieurs nouvelles entrées dans une entité.
     *
     * @param string $entityName Nom de l'entité.
     * @param array $elements Entrées à insérer.
     */
    public function inserts(string $entityName, array $elements);

    /**
     * Met à jour une entrée d'une entité.
     *
     * @param string $entityName Nom de l'entité.
     * @param array $fields Champs à mettre à jour.
     * @param int $id Identifiant de l'entrée.
     */
    public function update(string $entityName, array $fields, $id);

    /**
     * Récupère des entrées d'une entité.
     *
     * @param string $entityName Nom de l'entité.
     * @param array $fieldNames Noms des champs à récupérer.
     * @return array Liste des entrées.
     */
    public function select(string $entityName, array $fieldNames);

    /**
     * Récupère toutes les entrées d'une entité.
     *
     * @param string $entityName Nom de l'entité.
     * @return array Liste des entrées.
     */
    public function selects(string $entityName);

    /**
     * Récupère des entrées d'une entité en fonction de critères.
     *
     * @param string $entityName Nom de l'entité.
     * @param array $criteria Critères de recherche.
     * @return array Liste des entrées.
     */
    public function selectWhere(string $entityName, array $criteria);

    /**
     * Met à jour des entrées d'une entité en fonction de critères.
     *
     * @param string $entityName Nom de l'entité.
     * @param array $fields Champs à mettre à jour.
     * @param array $where Critères de recherche.
     * @return array Liste des entrées mises à jour.
     */
    public function updateWhere(string $entityName, array $fields, array $where);

    /**
     * Supprime des entrées d'une entité en fonction de critères.
     *
     * @param string $entityName Nom de l'entité.
     * @param array $where Critères de recherche.
     */
    public function deleteWhere(string $entityName, array $where);

    /**
     * Supprime une entrée d'une entité.
     *
     * @param string $entityName Nom de l'entité.
     * @param int $id Identifiant de l'entrée.
     */
    public function delete(string $entityName, $id);

    /**
     * Vide une entité.
     *
     * @param string $entityName Nom de l'entité.
     */
    public function truncate(string $entityName);

    /**
     * Supprime toutes les entrées d'une entité.
     *
     * @param string $entityName Nom de l'entité.
     */
    public function deleteAll(string $entityName);
}
