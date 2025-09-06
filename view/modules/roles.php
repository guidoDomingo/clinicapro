<?php
if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
    echo '<script>window.location.href = "login";</script>';
    exit();
}
?>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Gestión de Roles y Permisos</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Roles Management -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Roles</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#modalAddRole">
                                    <i class="fas fa-plus"></i> Nuevo Rol
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="rolesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Permissions Management -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Permisos</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                    data-target="#modalAddPermission">
                                    <i class="fas fa-plus"></i> Nuevo Permiso
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="permissionsTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Roles Management -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Asignación de Roles a Usuarios</h3>
                            <div class="card-tools">
                                <span class="badge badge-info" id="totalUsersCount">Total usuarios: 0</span>
                                <span class="badge badge-success" id="doctorsCount">Doctores: 0</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="userRolesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Tipo</th>
                                        <th>Tiene Persona</th>
                                        <th>Roles Actuales</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal Assign Role -->
<div class="modal fade" id="modalAssignRole">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Asignar Roles al Usuario</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAssignRole">
                <input type="hidden" id="assignUserId" name="user_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Roles Disponibles</label>
                        <div id="roleCheckboxes" class="d-flex flex-wrap">
                            <!-- Roles will be loaded dynamically -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Role -->
<div class="modal fade" id="modalAddRole">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Agregar Nuevo Rol</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddRole">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="roleName">Nombre del Rol</label>
                        <input type="text" class="form-control" id="roleName" name="role_name" required>
                    </div>
                    <div class="form-group">
                        <label for="roleDescription">Descripción</label>
                        <textarea class="form-control" id="roleDescription" name="role_description" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Permisos</label>
                        <div id="permissionCheckboxes" class="d-flex flex-wrap">
                            <!-- Permissions will be loaded dynamically -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Permission -->
<div class="modal fade" id="modalAddPermission">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Agregar Nuevo Permiso</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formAddPermission">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="permissionName">Nombre del Permiso</label>
                        <input type="text" class="form-control" id="permissionName" name="perm_name" required>
                    </div>
                    <div class="form-group">
                        <label for="permissionDescription">Descripción</label>
                        <textarea class="form-control" id="permissionDescription" name="perm_description"
                            rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Role -->
<div class="modal fade" id="modalEditRole">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Editar Rol</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditRole">
                <input type="hidden" id="editRoleId" name="role_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editRoleName">Nombre del Rol</label>
                        <input type="text" class="form-control" id="editRoleName" name="role_name" required>
                    </div>
                    <div class="form-group">
                        <label for="editRoleDescription">Descripción</label>
                        <textarea class="form-control" id="editRoleDescription" name="role_description"
                            rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Permisos</label>
                        <div id="editPermissionCheckboxes" class="d-flex flex-wrap">
                            <!-- Permissions will be loaded dynamically -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Permission -->
<div class="modal fade" id="modalEditPermission">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Editar Permiso</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formEditPermission">
                <input type="hidden" id="editPermissionId" name="perm_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editPermissionName">Nombre del Permiso</label>
                        <input type="text" class="form-control" id="editPermissionName" name="perm_name" required>
                    </div>
                    <div class="form-group">
                        <label for="editPermissionDescription">Descripción</label>
                        <textarea class="form-control" id="editPermissionDescription" name="perm_description"
                            rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Change User Password -->
<div class="modal fade" id="modalChangeUserPassword">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cambiar Contraseña de Usuario</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formChangeUserPassword">
                <input type="hidden" id="changePasswordUserId" name="user_id">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Cambiarás la contraseña del usuario: <strong id="changePasswordUserName"></strong>
                    </div>
                    <div class="form-group">
                        <label for="newUserPassword">Nueva Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="newUserPassword" name="new_password" 
                               placeholder="Ingresa la nueva contraseña" minlength="6" required>
                        <small class="form-text text-muted">La contraseña debe tener al menos 6 caracteres</small>
                    </div>
                    <div class="form-group">
                        <label for="confirmUserPassword">Confirmar Nueva Contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="confirmUserPassword" name="confirm_password" 
                               placeholder="Confirma la nueva contraseña" required>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Cambiar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>