<?php
namespace Api\Models;

use Api\Core\Model;

/**
 * SysUser Model
 * 
 * Represents a user in the system
 */
class SysUser extends Model
{
    /**
     * @var string The table associated with the model
     */
    protected $table = 'sys_users';
    
    /**
     * @var string The primary key column
     */
    protected $primaryKey = 'user_id';
    
    /**
     * @var array The attributes that are mass assignable
     */
    protected $fillable = [
        'reg_id',
        'user_email',
        'user_pass',
        'user_expire',
        'user_first_login',
        'user_last_login',
        'user_is_active',
        'profile_photo'
    ];
    
    /**
     * Get users with pagination
     * 
     * @param int $page The page number
     * @param int $perPage The number of records per page
     * @return array
     */
    public function paginate($page = 1, $perPage = 50)
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT u.*, r.reg_name, r.reg_lastname, r.reg_email, r.reg_document, "
             . "p.first_name, p.last_name, p.document_number, p.phone_number, "
             . "CASE WHEN d.doctor_id IS NOT NULL THEN 'SÍ' ELSE 'NO' END as es_doctor, "
             . "d.doctor_id, "
             . "ARRAY_AGG(DISTINCT jsonb_build_object('role_id', ro.role_id, 'role_name', ro.role_name)) FILTER (WHERE ro.role_id IS NOT NULL) as roles "
             . "FROM {$this->table} u "
             . "LEFT JOIN sys_register r ON u.reg_id = r.reg_id "
             . "LEFT JOIN person_system_user psu ON u.user_id = psu.system_user_id "
             . "LEFT JOIN rh_person p ON psu.person_id = p.person_id "
             . "LEFT JOIN rh_doctors d ON p.person_id = d.person_id AND d.doctor_estado = 'ACTIVO' "
             . "LEFT JOIN sys_user_roles ur ON u.user_id = ur.user_id "
             . "LEFT JOIN sys_roles ro ON ur.role_id = ro.role_id "
             . "GROUP BY u.user_id, r.reg_name, r.reg_lastname, r.reg_email, r.reg_document, "
             . "p.first_name, p.last_name, p.document_number, p.phone_number, d.doctor_id "
             . "ORDER BY u.{$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        
        $users = $this->raw($sql, [
            'limit' => $perPage,
            'offset' => $offset
        ])->fetchAll();
        
        // Parse the roles JSON array for each user and enhance data
        foreach ($users as &$user) {
            $user['roles'] = $user['roles'] ? json_decode($user['roles']) : [];
            
            // Determinar el nombre completo preferido (persona o registro)
            $user['display_name'] = '';
            if (!empty($user['first_name']) && !empty($user['last_name'])) {
                $user['display_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
            } elseif (!empty($user['reg_name']) && !empty($user['reg_lastname'])) {
                $user['display_name'] = trim($user['reg_name'] . ' ' . $user['reg_lastname']);
            } else {
                $user['display_name'] = $user['user_email'];
            }
            
            // Información adicional para mostrar en la tabla
            $user['user_type'] = $user['es_doctor'] === 'SÍ' ? 'Doctor' : 'Usuario';
            $user['has_person'] = !empty($user['first_name']) ? 'SÍ' : 'NO';
        }
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $totalCount = $this->raw($countSql)->fetch()['total'];
        
        return [
            'data' => $users,
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
     * Get user by email
     * 
     * @param string $email The user email
     * @return array|null
     */
    public function getByEmail($email)
    {
        return $this->raw(
            "SELECT * FROM {$this->table} WHERE user_email = :email",
            ['email' => $email]
        )->fetch();
    }
    
    /**
     * Get user with registration data
     * 
     * @param int $userId The user ID
     * @return array|null
     */
    public function getUserWithRegistration($userId)
    {
        $sql = "SELECT u.*, r.reg_name, r.reg_lastname, r.reg_email, r.reg_document, r.reg_phone "
             . "FROM {$this->table} u "
             . "JOIN sys_register r ON u.reg_id = r.reg_id "
             . "WHERE u.{$this->primaryKey} = :userId";
        
        return $this->raw($sql, ['userId' => $userId])->fetch();
    }
    
    /**
     * Get user roles
     * 
     * @param int $userId The user ID
     * @return array
     */
    public function getUserRoles($userId)
    {
        $sql = "SELECT r.* FROM sys_roles r "
             . "JOIN sys_user_roles ur ON r.role_id = ur.role_id "
             . "WHERE ur.user_id = :userId";
        
        return $this->raw($sql, ['userId' => $userId])->fetchAll();
    }
    
    /**
     * Assign role to user
     * 
     * @param int $userId The user ID
     * @param int $roleId The role ID
     * @return bool
     */
    public function assignRole($userId, $roleId)
    {
        try {
            $sql = "INSERT INTO sys_user_roles (user_id, role_id) VALUES (:userId, :roleId)";
            $this->raw($sql, ['userId' => $userId, 'roleId' => $roleId]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Remove role from user
     * 
     * @param int $userId The user ID
     * @param int $roleId The role ID
     * @return bool
     */
    public function removeRole($userId, $roleId)
    {
        try {
            $sql = "DELETE FROM sys_user_roles WHERE user_id = :userId AND role_id = :roleId";
            $this->raw($sql, ['userId' => $userId, 'roleId' => $roleId]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Activate user account
     * 
     * @param int $userId The user ID
     * @return bool
     */
    public function activateUser($userId)
    {
        try {
            return $this->update($userId, ['user_is_active' => true]);
        } catch (\Exception $e) {
            return false;
        }
    }
}