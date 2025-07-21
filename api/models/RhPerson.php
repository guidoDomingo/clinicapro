<?php
namespace Api\Models;

use Api\Core\Model;

/**
 * RhPerson Model
 * 
 * Represents a person in the system
 */
class RhPerson extends Model
{
    /**
     * @var string The table associated with the model
     */
    protected $table = 'rh_person';
    
    /**
     * @var string The primary key column
     */
    protected $primaryKey = 'person_id';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected $fillable = [
        'document_number',
        'birth_date',
        'first_name',
        'last_name',
        'phone_number',
        'gender',
        'record_number',
        'address',
        'email',
        'department_id',
        'city_id',
        'is_minor',
        'guardian_name',
        'guardian_document',
        'registered_by',
        'modified_by',
        'last_modified_at',
        'last_accessed_at',
        'owner_id',
        'is_active',
        'business_id',
        'profile_photo'
    ];
    
    /**
     * Get persons with pagination, search and ordering
     * 
     * @param int $page The page number
     * @param int $perPage The number of records per page
     * @param string $search The search term
     * @param string $orderColumn The column to order by
     * @param string $orderDirection The order direction (asc/desc)
     * @return array
     */
    public function paginate($page = 1, $perPage = 20, $search = '', $orderColumn = 'person_id', $orderDirection = 'desc')
    {
        $offset = ($page - 1) * $perPage;
        
        // Sanitize order column to prevent SQL injection
        $allowedColumns = ['person_id', 'document_number', 'first_name', 'last_name', 'birth_date', 'record_number', 'phone_number', 'is_active'];
        $orderColumn = in_array($orderColumn, $allowedColumns) ? $orderColumn : 'person_id';
        $orderDirection = strtoupper($orderDirection) === 'ASC' ? 'ASC' : 'DESC';
        
        // Build WHERE clause for search
        $whereClause = '';
        $params = [
            'limit' => $perPage,
            'offset' => $offset
        ];
        
        if (!empty($search)) {
            $whereClause = "WHERE first_name ILIKE :search OR last_name ILIKE :search OR document_number ILIKE :search OR phone_number ILIKE :search";
            $params['search'] = "%{$search}%";
        }
        
        $sql = "SELECT * FROM {$this->table} {$whereClause} ORDER BY {$orderColumn} {$orderDirection} LIMIT :limit OFFSET :offset";
        $persons = $this->raw($sql, $params)->fetchAll();
        
        // Count total records with search filter
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $countParams = [];
        if (!empty($search)) {
            $countParams['search'] = "%{$search}%";
        }
        $totalCount = $this->raw($countSql, $countParams)->fetch()['total'];
        
        return [
            'data' => $persons,
            'pagination' => [
                'total' => (int) $totalCount,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($totalCount / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $totalCount)
            ]
        ];
    }
    
    /**
     * Search persons by name, lastname or document
     * 
     * @param string $query The search query
     * @return array
     */
    public function search($query)
    {
        $sql = "SELECT * FROM {$this->table} WHERE "
             . "first_name ILIKE :query OR "
             . "last_name ILIKE :query OR "
             . "document_number ILIKE :query "
             . "ORDER BY {$this->primaryKey} DESC";
        
        return $this->raw($sql, ['query' => "%{$query}%"])->fetchAll();
    }
    
    /**
     * Advanced search for persons with multiple filters
     * 
     * @param array $conditions Array of SQL conditions
     * @param array $params Array of parameters for the conditions
     * @return array
     */
    public function advancedSearch($conditions, $params)
    {
        $whereClause = implode(' AND ', $conditions);
        
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($whereClause)) {
            $sql .= " WHERE " . $whereClause;
        }
        
        $sql .= " ORDER BY {$this->primaryKey} DESC";
        
        return $this->raw($sql, $params)->fetchAll();
    }
    
    /**
     * Get person by document number
     * 
     * @param string $documentNumber The document number
     * @return array|null
     */
    public function getByDocument($documentNumber)
    {
        return $this->raw(
            "SELECT * FROM {$this->table} WHERE document_number = :document_number",
            ['document_number' => $documentNumber]
        )->fetch();
    }
    
    /**
     * Get person by email
     * 
     * @param string $email The email
     * @return array|null
     */
    public function getByEmail($email)
    {
        return $this->raw(
            "SELECT * FROM {$this->table} WHERE email = :email",
            ['email' => $email]
        )->fetch();
    }
    
    /**
     * Get active persons
     * 
     * @return array
     */
    public function getActive()
    {
        return $this->where('is_active', true);
    }
    
    /**
     * Associate a person with a user
     * 
     * @param int $personId The person ID
     * @param int $userId The user ID
     * @return bool
     */
    public function associateWithUser($personId, $userId)
    {
        try {
            return $this->update($personId, ['owner_id' => $userId]);
        } catch (\Exception $e) {
            return false;
        }
    }
}