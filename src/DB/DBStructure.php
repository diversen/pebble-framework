<?php

declare(strict_types=1);

namespace Pebble\DB;

use PDO;

class DBStructure {

    public $dbh;

    public function __construct(PDO $dbh) {
        $this->dbh = $dbh;
    }

    public function getDatabaseName() {
        return $this->dbh->query('SELECT DATABASE()')->fetchColumn();
    }

    public function getTableFields(string $table_name) {
        
        $sql = "
        SELECT table_schema, table_name, column_name, ordinal_position, data_type, numeric_precision, column_type 
            FROM information_schema.columns 
        WHERE table_name = :table_name and table_schema = :table_schema ORDER BY ordinal_position;";
        
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([
            ':table_name' => $table_name,
            ':table_schema' => $this->getDatabaseName()
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    public function getTableKeys(string $table_name) {

        $sql = "SHOW keys FROM $table_name";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    public function getForeignKeys(string $database = null) {
        if (!$database) {
            $database = $this->getDatabaseName();
        }

        $sql = "
        SELECT DISTINCT KCU.TABLE_NAME, KCU.COLUMN_NAME, REFERENCED_TABLE_SCHEMA, KCU.REFERENCED_TABLE_NAME, KCU.REFERENCED_COLUMN_NAME, UPDATE_RULE, DELETE_RULE  FROM information_schema.KEY_COLUMN_USAGE 
        KCU INNER JOIN information_schema.referential_constraints RC ON KCU.CONSTRAINT_NAME = RC.CONSTRAINT_NAME WHERE TABLE_SCHEMA = '$database' AND KCU.REFERENCED_TABLE_NAME IS NOT NULL ORDER BY KCU.TABLE_NAME, KCU.COLUMN_NAME;
        ";

        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }       
}
