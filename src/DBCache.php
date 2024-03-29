<?php

declare(strict_types=1);

namespace Pebble;

use InvalidArgumentException;

class DBCache
{
    /**
     * Default database cache table name
     */
    private string $table = 'cache_system';

    private \Pebble\DB $db;

    /**
     * @param string $table
     * @param \Pebble\DB $db
     */
    public function __construct(\Pebble\DB $db, string $table = null)
    {
        $this->db = $db;
        if ($table) {
            $this->table = $table;
        }
    }

    /**
     * @param mixed $id
     */
    private function generateJsonKey($id): string
    {
        $key = null;
        if (is_string($id)) {
            $key = $id;
        } else {
            $key = json_encode($id);
            if ($key === false) {
                throw new InvalidArgumentException('Invalid JSON key');
            }
        }
        return $key;
    }

    /**
     * @param mixed $id
     */
    private function generateHashKey($id): string
    {
        $json_key = $this->generateJsonKey($id);
        return $this->hash($json_key);
    }

    /**
     * Hash a key using sha256
     */
    private function hash(string $key): string
    {
        return hash('sha256', $key);
    }

    /**
     * Get a cache result by ID and max_life_time in seconds
     * (from the time when the result was cached)
     * If no result is found return null
     * @param mixed $id
     * @return mixed
     */
    public function get($id, int $max_life_time = 0)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = ? ";
        $row = $this->db->prepareFetch($query, [$this->generateHashKey($id)]);

        if (empty($row)) {
            return null;
        }

        if ($max_life_time) {
            $expire = $row['unix_ts'] + $max_life_time;
            if ($expire < time()) {
                $this->delete($this->generateHashKey($id));
                return null;
            } else {
                return json_decode($row['data'], true);
            }
        } else {
            return json_decode($row['data'], true);
        }
    }
    /**
     * Sets data in cache using ID
     * Throws on error
     * @param mixed $id
     * @param mixed $data
     */
    public function set($id, $data): void
    {
        $this->db->inTransactionExec(function () use ($id, $data) {
            $this->delete($id);
            $query = "INSERT INTO {$this->table} (id, json_key, unix_ts, data) VALUES (?, ?, ?, ?)";
            $this->db->prepareExecute($query, [$this->generateHashKey($id), $this->generateJsonKey($id), time(), json_encode($data)]);
        });
    }

    /**
     * Delete a string from cache by ID
     * @param mixed $id
     */
    public function delete($id): bool
    {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $row = $this->db->prepareFetch($query, [$this->generateHashKey($id)]);

        if (!empty($row)) {
            $query = "DELETE FROM {$this->table} WHERE id = ?";
            return $this->db->prepareExecute($query, [$this->generateHashKey($id)]);
        }

        return true;
    }
}
