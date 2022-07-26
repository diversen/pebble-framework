<?php

declare(strict_types=1);

namespace Pebble;

use PDO;
use PDOStatement;
use Throwable;

/**
 * Simple database class that can do anything you need to do with a database
 */
class DB
{
    /**
     * @var PDOStatement
     */
    private PDOStatement $stmt;

    /**
     * @var PDO
     */
    private PDO $dbh;

    /**
     * Set database handle direct
     */
    public function setDbh(PDO $dbh): void
    {
        $this->dbh = $dbh;
    }

    /**
     * Return the objects database handle.
     */
    public function getDbh(): PDO
    {
        return $this->dbh;
    }

    /**
     * Create a database handle in the constructor
     * @param array<mixed> $options
     */
    public function __construct(string $url, string $username = '', string $password = '', array $options = [])
    {
        $this->dbh = new PDO(
            $url,
            $username,
            $password,
            $options
        );
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Change in 8.1
        // Integers and floats in result sets will now be returned using native PHP types
        // instead of strings when using emulated prepared statements.
        $this->dbh->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);
    }

    /**
     * Prepare and execute an arbitrary string of SQL
     * `$db->prepareExecute('DELETE FROM auth WHERE email = ?', ['test@mail.com']); `
     * @param string $sql
     * @param array<mixed> $values
     */
    public function prepareExecute(string $sql, array $values = []): bool
    {
        $this->stmt = $this->dbh->prepare($sql);
        return $this->stmt->execute($values);
    }

    /**
     * Prepare and fetch all rows using `$sql` and `$params`
     * `$db->prepareFetch("SELECT * FROM invites WHERE auth_id = ? ", [$auth_id]);`
     * @param string $sql
     * @param array<mixed> $params
     * @return array<mixed>
     */
    public function prepareFetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->getStmt($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Prepare and fetch a single row or an empty array
     * `$db->prepareFetch("SELECT * FROM invites WHERE auth_id = ? ", [$auth_id]);`
     * @param string $sql
     * @param array<mixed> $params
     * @return array<mixed>
     */
    public function prepareFetch(string $sql, array $params = []): array
    {
        $stmt = $this->getStmt($sql, $params);

        // Fetch returns false when 0 rows. FetchAll always returns an array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($row)) {
            return $row;
        }
        return [];
    }


    /**
     * Count number of rows in a table from a `$table` name, the `$field` to count from, and `$where` conditions
     * @param string $table
     * @param string $field
     * @param array<string> $where
     */
    public function getTableNumRows(string $table, string $field, array $where = []): int
    {
        $sql = "SELECT count($field) as num_rows FROM $table ";
        $sql .= $this->getWhereSql($where);
        $row = $this->prepareFetch($sql, $where);
        return (int)$row['num_rows'];
    }

    /**
     * Prepare SQL with params and return stmt
     * Then run e.g. `$stmt->fetch(PDO::FETCH_ASSOC)`;
     * @param string $sql
     * @param array<mixed> $params
     */
    public function getStmt(string $sql, array $params = []): PDOStatement
    {
        $this->stmt = $this->dbh->prepare($sql);
        $this->stmt->execute($params);
        return $this->stmt;
    }

    /**
     * Return number of affected rows
     * Use this with 'Delete', 'Update', 'Insert' if you need the row count.
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Returns last insert ID
     */
    public function lastInsertId(string $name = null): string
    {
        return $this->dbh->lastInsertId($name);
    }
    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->dbh->beginTransaction();
    }
    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->dbh->rollBack();
    }
    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->dbh->commit();
    }

    /**
     * Generate array with keys as named params =>
     * 
     * $post['title'] = $title will be transformed into
     * $post[':title'] = $title
     * 
     * @param array<mixed> $values
     * @return array<mixed>
     */
    private function generateNamedParams(array $values = []): array
    {
        $ret_val = [];
        foreach ($values as $key => $val) {
            $ret_val[':' . $key] = $val;
        }
        return $ret_val;
    }

    /**
     * Insert into $table a new row generated from $values:
     * `$db->insert('users_table', ['user_email' => 'test@test.com', 'user_name' => 'John Doe']);`
     * 
     * @param array<mixed> $values
     */
    public function insert(string $table, array $values): bool
    {
        $field_names = array_keys($values);

        $sql = "INSERT INTO $table";

        // Escape field names
        $field_names_escaped = [];
        foreach ($field_names as $key => $val) {
            $field_names_escaped[] = "`$val`";
        }

        // Insert values
        $fields = '( ' . implode(', ', $field_names_escaped) . ' )';

        // Variable bindings
        $bound = '(:' . implode(', :', $field_names) . ' )';

        // SQL statement
        $sql .= $fields . ' VALUES ' . $bound;

        // Named params
        $values = $this->generateNamedParams($values);

        // Prepare and execute
        return $this->prepareExecute($sql, $values);
    }

    /**
     * UPDATE table row(s)
     * `$db->update('user_table', ['user_email' => 'new_email@domain', 'user_name' => 'new name'], ['id' => 42]);`
     * 
     * @param array<mixed> $values
     * @param array<mixed> $where
     */
    public function update(string $table, array $values, array $where): bool
    {
        $sql = "UPDATE $table SET ";

        $final_values = [];
        $update_ary = [];
        $where_ary = [];

        // Generate named update parameters from insert value keys
        foreach ($values as $field => $value) {
            $update_ary[] = " `$field`=" . ":$field ";
            $final_values[$field] = $value;
        }

        $sql .= implode(',', $update_ary);
        $sql .= " WHERE ";

        // Generate named WHERE parameters from where array
        $i = 0;
        foreach ($where as $field => $value) {

            // Update values may be the same as where values
            // Ensure that all named params are unique
            $field_key = $field;
            if (isset($final_values[$field])) {
                $field_key = $field . '_' . $i;
                $i += 1;
            }

            $where_ary[] = " `$field`=" . ":$field_key ";
            $final_values[$field_key] = $value;
        }

        $sql .= implode(' AND ', $where_ary);
        $final_values = $this->generateNamedParams($final_values);

        return $this->prepareExecute($sql, $final_values);
    }

    /**
     * Generates simple where part of SQL, e.g. `['email' => 'some@email.dk', 'user' => 'some user']` =>
     * `WHERE username = :username AND user = :user`
     * 
     * @param array<mixed> $where
     */
    public function getWhereSql(array $where): string
    {
        if (empty($where)) {
            return ' ';
        }

        foreach ($where as $field => $value) {
            $where_ary[] = " `$field`=" . ":$field ";
        }

        $sql  = " WHERE ";
        $sql .= implode(' AND ', $where_ary) . ' ';
        return $sql;
    }

    /**
     * Return limit SQL
     * 
     * @param array<int> $limit index 0 is limit and index 1 is offset
     */
    public function getLimitSql(array $limit = []): string
    {
        if (empty($limit)) {
            return '';
        }

        $offset = (int)$limit[0];
        $limit = (int)$limit[1];

        return "LIMIT $offset, $limit ";
    }

    /**
     * Return `order by ... ` SQL string from an array
     * @param array<mixed> $order_by `An array of arrays contains order where index 0 is field and index 1 is direction`
     */
    public function getOrderBySql(array $order_by = []): string
    {
        if (empty($order_by)) {
            return '';
        }

        foreach ($order_by as $field => $direction) {
            $order_by_sql_ary[] = "`$field` $direction";
        }

        $order_by_sql = 'ORDER BY ';
        $order_by_sql .= implode(', ', $order_by_sql_ary);

        return $order_by_sql . ' ';
    }

    /**
     * Delete from rows from a table
     * `$db->delete('project', ['id' => $id]);`
     * 
     * @param array<mixed> $where
     */
    public function delete(string $table, array $where): bool
    {
        $sql = "DELETE FROM $table ";
        $sql .= $this->getWhereSql($where);

        $where = $this->generateNamedParams($where);
        $res = $this->prepareExecute($sql, $where);
        return $res;
    }

    /**
     * Shortcut to get one row, e.g:
     * `$db->getOne('auth', ['verified' => 1, 'email' => $email]);`
     * 
     * @param array<mixed> $where
     * @param array<mixed> $order_by
     * @return array<mixed>
     */
    public function getOne(string $table, array $where, array $order_by = []): array
    {
        $sql = "SELECT * FROM `$table` ";
        $sql .= $this->getWhereSql($where);
        $sql .= $this->getOrderBySql($order_by);
        $row = $this->prepareFetch($sql, $where);
        return $row;
    }

    /**
     * Shortcut to get all rows, e.g:
     * `$db->getAll('invites', ['invite_email' => $email]);`
     * 
     * @param array<mixed> $where
     * @param array<mixed> $order_by
     * @param array<mixed> $limit
     * @return array<mixed>
     */
    public function getAll(string $table, array $where, array $order_by = [], array $limit = []): array
    {
        $sql = "SELECT * FROM `$table` ";
        $sql .= $this->getWhereSql($where);
        $sql .= $this->getOrderBySql($order_by);
        $sql .= $this->getLimitSql($limit);

        $rows = $this->prepareFetchAll($sql, $where);
        return $rows;
    }

    /**
     * Prepare and fetch a single row using params in the where clause
     * Use this when you want to generate 'WHERE' clause from `$params`
     * 
     * @param array<mixed> $params
     * @param array<mixed> $order_by
     * @return array<mixed>
     */
    public function getOneQuery(string $sql, array $params = [], array $order_by = []): array
    {
        $where = $params;

        $sql .= ' ';
        $sql .= $this->getWhereSql($where);
        $sql .= $this->getOrderBySql($order_by);
        $stmt = $this->getStmt($sql, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($row)) {
            return $row;
        }
        return [];
    }

    /**
     * Prepare and fetch all rows using `$params` in the where clause
     * `$db->prepareQueryAll("SELECT * FROM invites", ['status' =>1], ['updated' => 'DESC'], [20, 10]]);`
     * 
     * @param array<mixed> $params
     * @param array<mixed> $order_by
     * @param array<mixed> $limit
     * @return array<mixed>
     */
    public function getAllQuery(string $sql, array $params = [], array $order_by = [], array $limit = []): array
    {
        $where = $params;

        $sql .= ' ';
        $sql .= $this->getWhereSql($where);
        $sql .= $this->getOrderBySql($order_by);
        $sql .= $this->getLimitSql($limit);
        $stmt = $this->getStmt($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Excecute a callable inside a transaction.
     * If an exception is thrown inside the callable, then
     * the exception will be re-thrown
     *
     * If possible the result of the callable will be
     * returned
     * 
     * @return mixed
     */

    public function inTransactionExec(callable $call)
    {
        try {
            $this->beginTransaction();
            $res = $call();
            $this->commit();
            return $res;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }
}
