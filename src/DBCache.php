<?php

declare(strict_types=1);

namespace Pebble;

use Pebble\DB;

class DBCache
{
    /**
     * Default database cache table name
     */
    private $table = 'cache_system';

    /**
     * constructor
     * @param   object $conn PDO connection
     * @param   string $table database table
     */
    public function __construct(DB $db, $table = null)
    {
        $this->db = $db;
        if ($table) {
            $this->table = $table;
        }
    }

    private function generateJsonKey($id)
    {
        $key = null;
        if (is_string($id)) {
            $key = $id;
        } else {
            $key = json_encode($id);
        }
        return $key;
    }

    private function generateHashKey($id)
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
     */
    public function get($id, int $max_life_time = 0): mixed
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
     */
    public function set($id, $data)
    {
        $this->db->inTransactionExec(function () use ($id, $data) {
            $this->delete($id);
            $query = "INSERT INTO {$this->table} (id, json_key, unix_ts, data) VALUES (?, ?, ?, ?)";
            $this->db->prepareExecute($query, [$this->generateHashKey($id), $this->generateJsonKey($id), time(), json_encode($data)]);
        });
    }

    /**
     * Delete a string from cache by ID
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
