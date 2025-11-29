<?php

namespace DB;

use PDO;
use PDOException;

class Database
{
    /**
     * @var array<string, string> Database configuration
     */
    private static array $config = [
        "host" => "localhost",
        "db" => "voting_app",
        "user" => "catlinks",
        "pass" => "!1234567",
        "charset" => "utf8mb4"
    ];

    /**
     * @var PDO|null Shared PDO instance
     */
    private static ?PDO $pdo = null;

    /**
     * Returns a singleton PDO instance.  
     * Creates the connection if it doesn't exist yet.
     *
     * @throws PDOException If connection fails
     *
     * @return PDO
     */
    public static function connect(): PDO
    {
        if (self::$pdo === null) {

            $dsn = "mysql:host=" . self::$config["host"] .
                ";dbname=" . self::$config["db"] .
                ";charset=" . self::$config["charset"];

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$pdo = new PDO($dsn, self::$config["user"], self::$config["pass"], $options);
            } catch (PDOException $e) {
                throw new PDOException("DB Connection failed: " . $e->getMessage(), (int) $e->getCode());
            }
        }

        return self::$pdo;
    }

    /**
     * Insert a row into a table.
     *
     * @param string              $table Table name
     * @param array<string,mixed> $data  Column => Value pairs
     *
     * @return int Number of affected rows (usually 1)
     */
    public static function insert(string $table, array $data): int
    {
        $pdo = self::connect();

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), "?");
        $values = array_values($data);

        $query = "INSERT INTO `$table` (" .
            implode(", ", array_map(fn($c) => "`$c`", $columns)) .
            ") VALUES (" . implode(", ", $placeholders) . ")";

        $stmt = $pdo->prepare($query);
        $stmt->execute($values);

        return $stmt->rowCount();
    }

    /**
     * Update rows in a table.
     *
     * @param string              $table       Table name
     * @param array<string,mixed> $data        Columns to update
     * @param string              $where       SQL WHERE clause (without "WHERE")
     * @param array<int,mixed>    $whereParams Parameters for WHERE
     *
     * @return int Number of affected rows
     */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $pdo = self::connect();

        $columns = array_keys($data);
        $setParts = [];

        foreach ($columns as $col) {
            $setParts[] = "`$col` = ?";
        }

        $sql = "UPDATE `$table` SET " . implode(", ", $setParts) . " WHERE $where";

        $params = array_merge(array_values($data), $whereParams);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Delete rows from a table.
     *
     * @param string            $table  Table name
     * @param string            $where  SQL WHERE clause
     * @param array<int,mixed>  $params Parameters for WHERE
     *
     * @return int Number of affected rows
     */
    public static function delete(string $table, string $where, array $params = []): int
    {
        $pdo = self::connect();

        $sql = "DELETE FROM `$table` WHERE $where";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    /**
     * Run any SELECT query.
     *
     * @param string            $sql      The SQL SELECT query
     * @param array<int,mixed>  $params   Parameters for prepared query
     * @param bool              $fetchAll Return all rows or only the first row
     *
     * @return array<mixed> If $fetchAll=true → list of rows  
     *                      If $fetchAll=false → single row or empty array
     */
    public static function query(string $sql, array $params = [], bool $fetchAll = true): array
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($fetchAll) {
            return $stmt->fetchAll();
        }

        $result = $stmt->fetch();
        return $result === false ? [] : $result;
    }

    /**
     * Fetch a single row.
     *
     * @param string            $sql    SQL SELECT (expecting one row)
     * @param array<int,mixed>  $params Bound parameters
     *
     * @return array<string,mixed> Single row or empty array
     */
    public static function fetch(string $sql, array $params = []): array
    {
        return self::query($sql, $params, false);
    }

    /**
     * Fetch all rows.
     *
     * @param string            $sql    SQL SELECT
     * @param array<int,mixed>  $params Bound parameters
     *
     * @return array<int,array<string,mixed>> List of rows
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params, true);
    }

    /**
     * Execute INSERT/UPDATE/DELETE queries manually.
     *
     * @param string            $sql    SQL query
     * @param array<int,mixed>  $params Bound parameters
     *
     * @return int Number of affected rows
     */
    public static function execQuery(string $sql, array $params = []): int
    {
        $pdo = self::connect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    // Track nested transaction depth
    private static int $transactionDepth = 0;

    /**
     * Transaction helper with nested transaction handling and named savepoints
     */
    public static function useTransaction(callable $fn)
    {
        $pdo = self::connect();

        // Increase nesting level
        self::$transactionDepth++;

        try {

            if (self::$transactionDepth === 1) {
                // Real transaction start
                $pdo->beginTransaction();
            } else {
                // Inside a nested one → create savepoint
                $savepoint = "SAVEPOINT_" . self::$transactionDepth;
                $pdo->exec("SAVEPOINT $savepoint");
            }

            // Execute user code
            $result = $fn(self::class, $pdo);

            if (self::$transactionDepth === 1) {
                // Outermost transaction
                $pdo->commit();
            } else {
                // Nested commit = release savepoint
                $savepoint = "SAVEPOINT_" . self::$transactionDepth;
                $pdo->exec("RELEASE SAVEPOINT $savepoint");
            }

            return $result;

        } catch (\Throwable $e) {

            if (self::$transactionDepth === 1) {
                // Outermost rollback
                $pdo->rollBack();
            } else {
                // Roll back to this savepoint only
                $savepoint = "SAVEPOINT_" . self::$transactionDepth;
                $pdo->exec("ROLLBACK TO $savepoint");
            }

            throw $e;

        } finally {
            // Critical: always decrease depth
            self::$transactionDepth--;
        }
    }
}
