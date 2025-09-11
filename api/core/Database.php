<?php
namespace Api\Core;

use PDO;

/**
 * Database Class
 * 
 * Handles database connections and provides a query builder interface
 */
class Database
{
    /**
     * @var \PDO The PDO connection instance
     */
    private static $connection = null;
    
    /**
     * @var string The database log file path
     */
    private static $logFile = '/var/log/clinica/database.log';
    
    /**
     * Log SQL query to database log file
     * 
     * @param string $sql The SQL query
     * @param array $params The query parameters
     * @param string $error Error message if any
     * @param bool $isError Whether this is an error log
     * @return void
     */
    private static function logQuery($sql, $params = [], $error = '', $isError = false)
    {
        $timestamp = date('Y-m-d H:i:s');
        $level = $isError ? 'ERROR' : 'INFO';
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($backtrace[1]) ? basename($backtrace[1]['file']) . ':' . $backtrace[1]['line'] : 'unknown';
        
        $message = sprintf("[%s] [%s] [%s]\n", $timestamp, $level, $caller);
        $message .= "SQL: {$sql}\n";
        
        if (!empty($params)) {
            $message .= "Parameters: " . json_encode($params, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        }
        
        if (!empty($error)) {
            $message .= "Error: {$error}\n";
        }
        
        $message .= "----------------------------------------\n";
        
        error_log($message, 3, self::$logFile);
    }
    
    /**
     * @var array Database configuration
     */
    private static $config = [
        'host' => '',
        'port' => '',
        'database' => '',
        'username' => '',
        'password' => '',
        'driver' => 'pgsql'
    ];
    
    /**
     * Initialize the database connection with configuration
     * 
     * @param array $config Database configuration
     * @return void
     */
    public static function init($config)
    {
        self::$config = array_merge(self::$config, $config);
    }
    
    /**
     * Get the PDO connection instance
     * 
     * @return \PDO
     */
    public static function getConnection()
    {
        if (self::$connection === null) {
            self::connect();
        }
        
        return self::$connection;
    }
    
    /**
     * Connect to the database
     * 
     * @return void
     * @throws \Exception If connection fails
     */
    private static function connect()
    {
        $driver = self::$config['driver'];
        $host = self::$config['host'];
        $port = self::$config['port'];
        $database = self::$config['database'];
        $username = self::$config['username'];
        $password = self::$config['password'];
        
        try {
            $dsn = "{$driver}:host={$host};port={$port};dbname={$database}";
            self::$connection = new \PDO($dsn, $username, $password);
            self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Execute a SQL query
     * 
     * @param string $sql The SQL query
     * @param array $params The query parameters
     * @return \PDOStatement
     */
    // public static function query($sql, $params = [])
    // {
    //     try {
    //         // Log the query before execution
    //         self::logQuery($sql, $params);
            
    //         $stmt = self::getConnection()->prepare($sql);
    //         $stmt->execute($params);
    //         return $stmt;
    //     } catch (\PDOException $e) {
    //         // Log detailed error information
    //         $errorInfo = $e->errorInfo ?? [];
    //         $errorCode = $errorInfo[1] ?? $e->getCode();
    //         $errorMessage = "Database error ({$errorCode}): " . $e->getMessage();
    //         $errorMessage .= "\nSQL: {$sql}";
    //         if (!empty($params)) {
    //             $errorMessage .= "\nParameters: " . json_encode($params);
    //         }
    //         self::logQuery($sql, $params, $errorMessage, true);
            
    //         // Provide specific error messages based on error codes
    //         switch ($errorCode) {
    //             case '23505': // Unique violation
    //                 throw new \Exception("Duplicate entry found", 400);
    //             case '23502': // Not null violation
    //                 throw new \Exception("Required field missing", 400);
    //             case '23503': // Foreign key violation
    //                 throw new \Exception("Invalid reference", 400);
    //             default:
    //                 throw new \Exception("Database operation failed: " . $e->getMessage(), 500);
    //         }
    //     }
    // }

    public static function query($sql, $params = [])
    {
        try {
            // Log the query before execution
            self::logQuery($sql, $params);

            $stmt = self::getConnection()->prepare($sql);

            // Nueva lógica para bindear cada valor con su tipo
            foreach ($params as $key => $value) {
                $paramType = PDO::PARAM_STR;

                if (is_int($value)) {
                    $paramType = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $paramType = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $paramType = PDO::PARAM_NULL;
                }

                $stmt->bindValue(":$key", $value, $paramType);
            }

            $stmt->execute(); // ejecutamos sin pasar los $params porque ya hicimos bindValue

            return $stmt;
        } catch (\PDOException $e) {
            // Log detailed error information
            $errorInfo = $e->errorInfo ?? [];
            $errorCode = $errorInfo[1] ?? $e->getCode();
            $errorMessage = "Database error ({$errorCode}): " . $e->getMessage();
            $errorMessage .= "\nSQL: {$sql}";
            if (!empty($params)) {
                $errorMessage .= "\nParameters: " . json_encode($params);
            }
            self::logQuery($sql, $params, $errorMessage, true);

            // Provide specific error messages based on error codes
            switch ($errorCode) {
                case '23505': // Unique violation
                    throw new \Exception("Duplicate entry found", 400);
                case '23502': // Not null violation
                    throw new \Exception("Required field missing", 400);
                case '23503': // Foreign key violation
                    throw new \Exception("Invalid reference", 400);
                default:
                    throw new \Exception("Database operation failed: " . $e->getMessage(), 500);
            }
        }
    }

    
    /**
     * Fetch all records from a query
     * 
     * @param string $sql The SQL query
     * @param array $params The query parameters
     * @return array
     */
    public static function fetchAll($sql, $params = [])
    {
        return self::query($sql, $params)->fetchAll();
    }
    
    /**
     * Fetch a single record from a query
     * 
     * @param string $sql The SQL query
     * @param array $params The query parameters
     * @return array|null
     */
    public static function fetch($sql, $params = [])
    {
        $result = self::query($sql, $params)->fetch();
        return $result !== false ? $result : null;
    }
    
    /**
     * Insert a record into a table
     * 
     * @param string $table The table name
     * @param array $data The data to insert
     * @return int The last insert ID
     */
    public static function insert($table, $data)
    {
        $columns = array_keys($data);
        $placeholders = array_map(function ($column) {
            return ":$column";
        }, $columns);
        
        $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") "
             . "VALUES (" . implode(', ', $placeholders) . ")";
        
        // Log the insert operation
        self::logQuery($sql, $data, '', false);
        
        self::query($sql, $data);
        return self::getConnection()->lastInsertId();
    }
    
    /**
     * Update records in a table
     * 
     * @param string $table The table name
     * @param array $data The data to update
     * @param string $where The WHERE clause
     * @param array $whereParams The WHERE parameters
     * @return int The number of affected rows
     */
    public static function update($table, $data, $where, $whereParams = [])
    {
        $setClauses = array_map(function ($column) {
            return "$column = :$column";
        }, array_keys($data));
        
        $sql = "UPDATE $table SET " . implode(', ', $setClauses) . " WHERE $where";
        
        $params = array_merge($data, $whereParams);
        
        // Log the update operation
        self::logQuery($sql, $params, '', false);
        
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete records from a table
     * 
     * @param string $table The table name
     * @param string $where The WHERE clause
     * @param array $params The WHERE parameters
     * @return int The number of affected rows
     */
    public static function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM $table WHERE $where";
        
        // Log the delete operation
        self::logQuery($sql, $params, '', false);
        
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Begin a database transaction
     * 
     * @return bool
     */
    public static function beginTransaction()
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit a database transaction
     * 
     * @return bool
     */
    public static function commit()
    {
        return self::getConnection()->commit();
    }

    /**
     * Rollback a database transaction
     * 
     * @return bool
     */
    public static function rollBack()
    {
        return self::getConnection()->rollBack();
    }
}