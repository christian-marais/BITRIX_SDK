<?php

namespace NS2B\SDK\DATABASE;

use NS2B\SDK\DATABASE\DatabaseSqlite;

class NsConfiguration extends DatabaseSqlite
{
    public function __construct()
    {
        parent::__construct('parameter', __DIR__ . '/database/');
        $this->createEntity('parameter', [
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'key' => 'TEXT NOT NULL UNIQUE',
            'value' => 'TEXT',
        ]);
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setParameter(string $key, string $value): self
    {
        $stmt = $this->pdo->prepare('INSERT OR REPLACE INTO parameter (key, value) VALUES (:key, :value)');
        $stmt->execute(['key' => $key, 'value' => $value]);
        return $this;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getParameter(string $key): ?string
    {
        $stmt = $this->pdo->prepare('SELECT value FROM parameter WHERE key = :key');
        $stmt->execute(['key' => $key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : null;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function updateParameter(string $key, string $value): self
    {
        $stmt = $this->pdo->prepare('UPDATE parameter SET value = :value WHERE key = :key');
        $stmt->execute(['key' => $key, 'value' => $value]);
        return $this;
    }
}
