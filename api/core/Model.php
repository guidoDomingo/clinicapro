<?php
namespace Api\Core;

/**
 * Base Model Class
 * 
 * Provides common database operations for all models
 */
abstract class Model
{
    /**
     * @var string The table name associated with the model
     */
    protected $table;
    
    /**
     * @var string The primary key column name
     */
    protected $primaryKey = 'id';
    
    /**
     * @var array Fillable attributes that can be mass assigned
     */
    protected $fillable = [];
    
    /**
     * Get all records from the model's table
     * 
     * @return array
     */
    public function all()
    {
        return Database::fetchAll("SELECT * FROM {$this->table}");
    }
    
    /**
     * Find a record by its primary key
     * 
     * @param mixed $id The primary key value
     * @return array|null
     */
    public function find($id)
    {
        return Database::fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    /**
     * Find records by a specific field value
     * 
     * @param string $field The field name
     * @param mixed $value The field value
     * @return array
     */
    public function where($field, $value)
    {
        return Database::fetchAll(
            "SELECT * FROM {$this->table} WHERE {$field} = :value",
            ['value' => $value]
        );
    }
    
    /**
     * Create a new record
     * 
     * @param array $data The record data
     * @return int The new record ID
     */
    public function create($data)
    {
        try {
            // Log original data before filtering
            $logData = [
                'table' => $this->table,
                'original_data' => $data,
                'fillable_fields' => $this->fillable
            ];
            
            // Filter data to only include fillable attributes
            $filteredData = array_intersect_key($data, array_flip($this->fillable));
            
            // Add filtered data to log
            $logData['filtered_data'] = $filteredData;
            
            // Log data that was excluded by the filter
            $excludedData = array_diff_key($data, $filteredData);
            if (!empty($excludedData)) {
                $logData['excluded_data'] = $excludedData;
            }
            
            // Validate that we have data to insert
            if (empty($filteredData)) {
                throw new \Exception('No valid data provided for insertion');
            }
            
            // Log the insert attempt to database.log
            error_log(
                "[" . date('Y-m-d H:i:s') . "] [INFO] [Model::create]\n" .
                "Table: {$this->table}\n" .
                "Original Data: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n" .
                "Filtered Data: " . json_encode($filteredData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n" .
                "Excluded Data: " . json_encode($excludedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n" .
                "----------------------------------------\n",
                3,
                '/var/log/clinica/database.log'
            );
            
            $id = Database::insert($this->table, $filteredData);
            
            if (!$id) {
                throw new \Exception('Failed to insert record');
            }
            
            return $id;
        } catch (\Exception $e) {
            // Log error to database.log
            error_log(
                "[" . date('Y-m-d H:i:s') . "] [ERROR] [Model::create]\n" .
                "Table: {$this->table}\n" .
                "Error: " . $e->getMessage() . "\n" .
                "----------------------------------------\n",
                3,
                '/var/log/clinica/database.log'
            );
            throw $e;
        }
    }
    
    /**
     * Update a record
     * 
     * @param mixed $id The primary key value
     * @param array $data The record data
     * @return int The number of affected rows
     */
    public function update($id, $data)
    {
        // Filter data to only include fillable attributes
        $filteredData = array_intersect_key($data, array_flip($this->fillable));
        
        return Database::update(
            $this->table,
            $filteredData,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    /**
     * Delete a record
     * 
     * @param mixed $id The primary key value
     * @return int The number of affected rows
     */
    public function delete($id)
    {
        return Database::delete(
            $this->table,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    /**
     * Execute a raw SQL query
     * 
     * @param string $sql The SQL query
     * @param array $params The query parameters
     * @return \PDOStatement
     */
    public function raw($sql, $params = [])
    {
        return Database::query($sql, $params);
    }
}