<?php
// Include the configuration file
require_once __DIR__ . '/config.php';

// Ensure required constants are defined
if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
    error_log('Database configuration constants are missing in config.php');
    die('Error: Database configuration is incomplete. Please check config.php.');
}

class Database
{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbh;
    private $stmt;
    private $error;

    public function __construct()
    {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log('Database connection failed: ' . $this->error);
            die('Error: Unable to connect to the database. Please try again later.');
        }
    }

    public function isConnected()
    {
        return $this->dbh !== null;
    }

    public function query($sql)
    {
        $this->stmt = $this->dbh->prepare($sql);
    }

    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute()
    {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            error_log('Query execution failed: ' . $e->getMessage());
            return false;
        }
    }

    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->query($sql);
        foreach ($data as $key => $value) {
            $this->bind(":$key", $value);
        }
        return $this->execute();
    }

    public function update($table, $data, $where)
    {
        $set = '';
        foreach ($data as $key => $value) {
            $set .= "$key = :$key, ";
        }
        $set = rtrim($set, ', ');
        $where_clause = '';
        foreach ($where as $key => $value) {
            $where_clause .= "$key = :where_$key AND ";
        }
        $where_clause = rtrim($where_clause, ' AND ');
        $sql = "UPDATE $table SET $set WHERE $where_clause";
        $this->query($sql);
        foreach ($data as $key => $value) {
            $this->bind(":$key", $value);
        }
        foreach ($where as $key => $value) {
            $this->bind(":where_$key", $value);
        }
        return $this->execute();
    }

    public function delete($table, $where)
    {
        $where_clause = '';
        foreach ($where as $key => $value) {
            $where_clause .= "$key = :$key AND ";
        }
        $where_clause = rtrim($where_clause, ' AND ');
        $sql = "DELETE FROM $table WHERE $where_clause";
        $this->query($sql);
        foreach ($where as $key => $value) {
            $this->bind(":$key", $value);
        }
        return $this->execute();
    }
}
?>