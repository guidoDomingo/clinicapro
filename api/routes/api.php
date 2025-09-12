<?php
/**
 * API Routes
 * 
 * This file defines all the API routes and maps them to controller actions
 */

use Api\Core\Router;

/**
 * @var Router $router
 */

// Authentication routes
// Usar el controlador existente pero con ruta absoluta y asegurar que está incluido
$controllerPath = realpath(__DIR__ . '/../../controller/user.controller.php');
if (!$controllerPath) {
    die('Error: No se puede encontrar el archivo controller/user.controller.php');
}
require_once $controllerPath;
$router->post('auth/login', '\\ControllerUser', 'ctrLoginUser');
$router->post('auth/logout', 'Api\\Controllers\\AuthController', 'logout');

// User Registration routes
$router->get('register', 'Api\\Controllers\\SysRegisterController', 'index');
$router->get('register/show', 'Api\\Controllers\\SysRegisterController', 'show');
$router->post('register', 'Api\\Controllers\\SysRegisterController', 'store');
$router->put('register', 'Api\\Controllers\\SysRegisterController', 'update');
$router->delete('register', 'Api\\Controllers\\SysRegisterController', 'destroy');

// User Management routes
$router->get('users', 'Api\\Controllers\\SysUserController', 'index');
$router->get('users/show', 'Api\\Controllers\\SysUserController', 'show');
// Change this route
// $router->get('users/{id}/roles', 'Api\\Controllers\\SysUserController', 'getUserRoles');
$router->post('users/roles', 'Api\\Controllers\\SysUserController', 'getUserRoles');
$router->put('users', 'Api\\Controllers\\SysUserController', 'update');
$router->put('users/toggle-active', 'Api\\Controllers\\SysUserController', 'toggleActive');
$router->post('users/assign-role', 'Api\\Controllers\\SysUserController', 'assignRole');
$router->post('users/remove-role', 'Api\\Controllers\\SysUserController', 'removeRole');
$router->post('users/associate-person', 'Api\\Controllers\\SysUserController', 'associateWithPerson');

// User Profile routes
$router->get('users/profile', 'Api\\Controllers\\SysUserController', 'getProfile');
$router->post('users/update-profile', 'Api\\Controllers\\SysUserController', 'updateProfile');
$router->post('users/change-password', 'Api\\Controllers\\SysUserController', 'changePassword');
$router->post('users/admin-change-password', 'Api\\Controllers\\SysUserController', 'adminChangePassword');
$router->post('users/upload-photo', 'Api\\Controllers\\SysUserController', 'uploadProfilePhoto');

// Role routes
$router->get('roles', 'Api\\Controllers\\SysRoleController', 'index');
$router->get('roles/{id}', 'Api\\Controllers\\SysRoleController', 'show');
$router->post('roles/create', 'Api\\Controllers\\SysRoleController', 'create');
$router->post('roles/update', 'Api\\Controllers\\SysRoleController', 'update');
$router->post('roles/delete', 'Api\\Controllers\\SysRoleController', 'delete');

// Permission routes
$router->get('permissions', 'Api\\Controllers\\SysPermissionController', 'index');
$router->post('permissions/show', 'Api\\Controllers\\SysPermissionController', 'show');
$router->post('permissions/create', 'Api\\Controllers\\SysPermissionController', 'create');
$router->post('permissions/update', 'Api\\Controllers\\SysPermissionController', 'update');
$router->post('permissions/delete', 'Api\\Controllers\\SysPermissionController', 'delete');

// Person Management routes
$router->get('persons', 'Api\Controllers\RhPersonController', 'index');
$router->get('people', 'Api\Controllers\RhPersonController', 'index'); // Alias para compatibilidad
$router->get('persons/show', 'Api\Controllers\RhPersonController', 'show');
$router->post('persons', 'Api\Controllers\RhPersonController', 'store');
$router->put('persons', 'Api\Controllers\RhPersonController', 'update');
$router->delete('persons', 'Api\Controllers\RhPersonController', 'destroy');
$router->get('persons/search', 'Api\Controllers\RhPersonController', 'search');
$router->get('persons/active', 'Api\Controllers\RhPersonController', 'getActive');
$router->post('persons/upload-photo', 'Api\Controllers\RhPersonController', 'uploadProfilePhoto');
$router->get('persons/professional', 'Api\Controllers\RhPersonController', 'getProfessionalInfo');
$router->post('persons/professional', 'Api\Controllers\RhPersonController', 'saveProfessionalInfo');

// Rutas para especialidades
$router->get('especialidades', 'Api\Controllers\EspecialidadController', 'index');
$router->get('specialties', 'Api\Controllers\EspecialidadController', 'index'); // Alias para compatibilidad
$router->get('especialidades/person', 'Api\Controllers\EspecialidadController', 'getForPerson');
$router->post('especialidades/assign', 'Api\Controllers\EspecialidadController', 'assignToPerson');

// Rutas para empresas
$router->get('businesses', 'Api\Controllers\SysBusinessController', 'index');
$router->get('businesses/show', 'Api\Controllers\SysBusinessController', 'show');
$router->get('businesses/doctor', 'Api\Controllers\SysBusinessController', 'getDoctorInfo');
$router->get('doctors', 'Api\Controllers\SysBusinessController', 'getDoctors');

// Location routes (departamentos y ciudades)
$router->get('departments', 'Api\Controllers\LocationController', 'getDepartments');
$router->get('cities', 'Api\Controllers\LocationController', 'getCities');

// ICD
$router->get('disease/{code}', 'Api\Controllers\ICD11Controller', 'getDetailedDiseaseByCode');