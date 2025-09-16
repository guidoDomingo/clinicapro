$(document).ready(function() {
    // Load roles for user role assignment
    function loadRolesForAssignment() {
        $.get('api/roles', function(response) {
            const roles = response.data;
            let checkboxes = '';
            roles.forEach(function(role) {
                checkboxes += `
                    <div class="custom-control custom-checkbox mr-3 mb-2">
                        <input type="checkbox" class="custom-control-input" id="role_${role.role_id}" value="${role.role_id}">
                        <label class="custom-control-label" for="role_${role.role_id}">${role.role_name}</label>
                    </div>
                `;
            });
            $('#roleCheckboxes').html(checkboxes);
        });
    }

    // Initialize User Roles DataTable
    const userRolesTable = $('#userRolesTable').DataTable({
        processing: true,
        serverSide: false,
        pageLength: 50,
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Todos"]],
        ajax: {
            url: 'api/users',
            dataSrc: function(response) {
                if (!response || !response.data || !response.data.data) return [];
                const users = response.data.data;
                
                // Actualizar contadores en la interfaz
                const totalUsers = users.length;
                const doctorsCount = users.filter(user => user.es_doctor === 'SÍ').length;
                
                $('#totalUsersCount').text(`Total usuarios: ${totalUsers}`);
                $('#doctorsCount').text(`Doctores: ${doctorsCount}`);
                
                users.forEach(function(user) {
                    user.roles = [];
                    $.ajax({
                        type: 'POST',
                        url: 'api/users/roles',
                        contentType: 'application/json',
                        data: JSON.stringify({ id: user.user_id }),
                        async: false,
                        success: function(data) {
                            user.roles = data.data || [];
                        },
                        error: function() {
                            console.error('Error fetching roles for user:', user.user_id);
                        }
                    });
                });
                return users;
            },
            error: function(xhr, error, thrown) {
                console.error('Error loading users:', error, thrown);
                Swal.fire('Error', 'Error loading users data', 'error');
            }
        },
        columns: [
            { data: 'user_id' },
            { 
                data: null,
                render: function(data, type, row) {
                    return row.display_name || (row.reg_name + ' ' + row.reg_lastname);
                }
            },
            { data: 'user_email' },
            {
                data: 'user_type',
                render: function(data, type, row) {
                    if (row.es_doctor === 'SÍ') {
                        return '<span class="badge badge-success"><i class="fas fa-user-md"></i> Doctor</span>';
                    }
                    return '<span class="badge badge-secondary"><i class="fas fa-user"></i> Usuario</span>';
                }
            },
            {
                data: 'has_person',
                render: function(data, type, row) {
                    if (data === 'SÍ') {
                        return '<span class="badge badge-success">SÍ</span>';
                    }
                    return '<span class="badge badge-warning">NO</span>';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return row.roles ? row.roles.map(role => role.role_name).join(', ') : '';
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-primary btn-sm assignRole" data-id="${row.user_id}">
                            <i class="fas fa-user-tag"></i> Asignar Roles
                        </button>
                        <button class="btn btn-warning btn-sm ml-1 changePassword" data-id="${row.user_id}" data-name="${row.display_name || (row.reg_name + ' ' + row.reg_lastname)}">
                            <i class="fas fa-key"></i> Cambiar Contraseña
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'desc']]
    });

    // Initialize DataTables
    const rolesTable = $('#rolesTable').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        ajax: {
            url: 'api/roles',
            dataSrc: function(response) {
                return response.data || [];
            },
            error: function(xhr, error, thrown) {
                console.error('Error loading roles:', error, thrown);
                Swal.fire('Error', 'Error loading roles data', 'error');
            }
        },
        columns: [
            { data: 'role_id' },
            { data: 'role_name' },
            { data: 'role_description' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-warning btn-sm editRole" data-id="${row.role_id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm deleteRole" data-id="${row.role_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    const permissionsTable = $('#permissionsTable').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        ajax: {
            url: 'api/permissions',
            dataSrc: 'data',
            error: function(xhr, error, thrown) {
                console.error('Error loading permissions:', error, thrown);
                Swal.fire('Error', 'Error loading permissions data', 'error');
            }
        },
        columns: [
            { data: 'perm_id' },
            { data: 'perm_name' },
            { data: 'perm_description' },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-warning btn-sm editPermission" data-id="${row.perm_id}">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm deletePermission" data-id="${row.perm_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });

    // Load permissions for role forms
    // Add debug logging for permissions loading
    function loadPermissions() {
        console.log('Loading permissions...');
        $.get('api/permissions', function(response) {
            console.log('Permissions loaded:', response);
            const permissions = response.data;
            let addCheckboxes = '';
            let editCheckboxes = '';
            permissions.forEach(function(permission) {
                addCheckboxes += `
                    <div class="custom-control custom-checkbox mr-3 mb-2">
                        <input type="checkbox" class="custom-control-input" id="perm_${permission.perm_id}" value="${permission.perm_id}">
                        <label class="custom-control-label" for="perm_${permission.perm_id}">${permission.perm_name}</label>
                    </div>
                `;
                editCheckboxes += `
                    <div class="custom-control custom-checkbox mr-3 mb-2">
                        <input type="checkbox" class="custom-control-input" id="edit_perm_${permission.perm_id}" value="${permission.perm_id}">
                        <label class="custom-control-label" for="edit_perm_${permission.perm_id}">${permission.perm_name}</label>
                    </div>
                `;
            });
            $('#permissionCheckboxes').html(addCheckboxes);
            $('#editPermissionCheckboxes').html(editCheckboxes);
        });
    }

    // Add Role
    $('#formAddRole').on('submit', function(e) {
        e.preventDefault();
        const permissions = [];
        const roleName = $('#roleName').val().trim();
        const roleDescription = $('#roleDescription').val().trim();
        
        if (!roleName) {
            Swal.fire('Error', 'Role name is required', 'error');
            return;
        }
        
        $('#permissionCheckboxes input:checked').each(function() {
            permissions.push($(this).val());
        });
        
        console.log('Submitting role:', { roleName, roleDescription, permissions });
        
        $.ajax({
            url: 'api/roles/create',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                role_name: roleName,
                role_description: roleDescription,
                permissions: permissions
            }),
            success: function(response) {
                console.log('Role created - Full response:', response);
                console.log('Role data:', response.data);
                $('#modalAddRole').modal('hide');
                rolesTable.ajax.reload();
                Swal.fire('¡Éxito!', 'Rol creado correctamente', 'success');
                $('#formAddRole')[0].reset();
            },
            error: function(xhr, status, error) {
                console.error('Error creating role:', error, xhr.responseText);
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al crear el rol', 'error');
            }
        });
    });

    // Add Permission
    $('#formAddPermission').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'api/permissions/create',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                perm_name: $('#permissionName').val(),
                perm_description: $('#permissionDescription').val()
            }),
            success: function(response) {
                console.log('Permission created - Full response:', response);
                console.log('Permission data:', response.data);
                $('#modalAddPermission').modal('hide');
                permissionsTable.ajax.reload();
                loadPermissions();
                Swal.fire('¡Éxito!', 'Permiso creado correctamente', 'success');
            },
            error: function(xhr) {
                console.error('Error creating permission:', xhr.responseText);
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al crear el permiso', 'error');
            }
        });
    });

    // Edit Role
    $(document).on('click', '.editRole', function() {
        const roleId = $(this).data('id');
        console.log('Editing role with ID:', roleId);
        $.get('api/roles/' + roleId, function(response) {
            const role = response.data;
            $('#editRoleId').val(role.role_id);
            $('#editRoleName').val(role.role_name);
            $('#editRoleDescription').val(role.role_description);
            
            // Check permissions
            $('#editPermissionCheckboxes input').prop('checked', false);
            role.permission_ids.forEach(function(permId) {
                $(`#editPermissionCheckboxes #edit_perm_${permId}`).prop('checked', true);
            });
            
            $('#modalEditRole').modal('show');
        });
    });

    // Update Role
    $('#formEditRole').on('submit', function(e) {
        e.preventDefault();
        const roleId = $('#editRoleId').val();
        const permissions = [];
        $('#editPermissionCheckboxes input:checked').each(function() {
            permissions.push($(this).val());
        });
    
        $.ajax({
            url: 'api/roles/update',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                id: roleId,
                role_name: $('#editRoleName').val(),
                role_description: $('#editRoleDescription').val(),
                permissions: permissions
            }),
            success: function(response) {
                console.log('Role updated - Full response:', response);
                console.log('Updated role data:', response.data);
                $('#modalEditRole').modal('hide');
                rolesTable.ajax.reload();
                Swal.fire('¡Éxito!', 'Rol actualizado correctamente', 'success');
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al actualizar el rol', 'error');
            }
        });
    });

    // Handle Assign Role Click
    $(document).on('click', '.assignRole', function() {
        const userId = $(this).data('id');
        $('#assignUserId').val(userId);
        
        // Load user's current roles
        $.ajax({
            url: 'api/users/roles',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: userId }),
            success: function(response) {
                const userRoles = response.data;
                console.log('User roles:', userRoles);
                
                // First, ensure all roles are loaded
                $.get('api/roles', function(rolesResponse) {
                    const allRoles = rolesResponse.data;
                    let checkboxes = '';
                    
                    // Create checkboxes for all roles
                    allRoles.forEach(function(role) {
                        // Check if this role is assigned to the user
                        const isChecked = userRoles.some(userRole => userRole.role_id === role.role_id);
                        checkboxes += `
                            <div class="custom-control custom-checkbox mr-3 mb-2">
                                <input type="checkbox" class="custom-control-input" id="role_${role.role_id}" 
                                       value="${role.role_id}" ${isChecked ? 'checked' : ''}>
                                <label class="custom-control-label" for="role_${role.role_id}">${role.role_name}</label>
                            </div>
                        `;
                    });
                    
                    // Update the modal with the new checkboxes
                    $('#roleCheckboxes').html(checkboxes);
                    $('#modalAssignRole').modal('show');
                });
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error loading user roles', 'error');
            }
        });
    });

    // Handle Role Assignment Form Submit
    $('#formAssignRole').on('submit', function(e) {
        e.preventDefault();
        const userId = $('#assignUserId').val();
        const roles = [];
        
        $('#roleCheckboxes input:checked').each(function() {
            roles.push($(this).val());
        });
        
        $.ajax({
            url: 'api/users/assign-role',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ roles: roles, id: userId }),
            success: function(response) {
                $('#modalAssignRole').modal('hide');
                userRolesTable.ajax.reload();
                Swal.fire('¡Éxito!', 'Roles asignados correctamente', 'success');
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al asignar roles', 'error');
            }
        });
    });

    // We don't need to load roles here as they are already loaded in the click event
    // and properly checked based on user's current roles
    /*$('#modalAssignRole').on('show.bs.modal', function() {
        loadRolesForAssignment();
    });*/

    // Delete Role
    $(document).on('click', '.deleteRole', function() {
        const roleId = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/roles/delete',
                    method: 'POST',
                    data: JSON.stringify({ id: roleId, action: 'delete' }),
                    contentType: 'application/json',
                    success: function() {
                        rolesTable.ajax.reload();
                        Swal.fire('¡Eliminado!', 'El rol ha sido eliminado', 'success');
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON.message || 'Error al eliminar el rol', 'error');
                    }
                });
            }
        });
    });

    // Edit Permission
    $(document).on('click', '.editPermission', function() {
        const permId = $(this).data('id');
        console.log('Editing permission with ID:', permId);
        $.ajax({
            url: 'api/permissions/show',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ id: permId }),
            success: function(response) {
                const permission = response.data;
                $('#editPermissionId').val(permission.perm_id);
                $('#editPermissionName').val(permission.perm_name);
                $('#editPermissionDescription').val(permission.perm_description);
                $('#modalEditPermission').modal('show');
            },
            error: function(xhr) {
                console.error('Error loading permission:', xhr.responseText);
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al cargar el permiso', 'error');
            }
        });
    });

    // Update Permission
    $('#formEditPermission').on('submit', function(e) {
        e.preventDefault();
        const permId = $('#editPermissionId').val();
        
        $.ajax({
            url: 'api/permissions/update',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                id: permId,
                perm_name: $('#editPermissionName').val(),
                perm_description: $('#editPermissionDescription').val()
            }),
            success: function(response) {
                console.log('Permission updated - Full response:', response);
                $('#modalEditPermission').modal('hide');
                permissionsTable.ajax.reload();
                loadPermissions();
                Swal.fire('¡Éxito!', 'Permiso actualizado correctamente', 'success');
            },
            error: function(xhr) {
                console.error('Error updating permission:', xhr.responseText);
                Swal.fire('Error', xhr.responseJSON?.message || 'Error al actualizar el permiso', 'error');
            }
        });
    });

    // Delete Permission
    $(document).on('click', '.deletePermission', function() {
        const permId = $(this).data('id');
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/permissions/delete',
                    method: 'POST',
                    data: JSON.stringify({ id: permId }),
                    contentType: 'application/json',
                    success: function() {
                        permissionsTable.ajax.reload();
                        loadPermissions();
                        Swal.fire('¡Eliminado!', 'El permiso ha sido eliminado', 'success');
                    },
                    error: function(xhr) {
                        console.error('Error deleting permission:', xhr.responseText);
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al eliminar el permiso', 'error');
                    }
                });
            }
        });
    });

    // Handle Change Password Click
    $(document).on('click', '.changePassword', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        
        $('#changePasswordUserId').val(userId);
        $('#changePasswordUserName').text(userName);
        $('#modalChangeUserPassword').modal('show');
    });

    // Handle Change User Password Form Submit
    $('#formChangeUserPassword').on('submit', function(e) {
        e.preventDefault();
        
        const userId = $('#changePasswordUserId').val();
        const newPassword = $('#newUserPassword').val();
        const confirmPassword = $('#confirmUserPassword').val();
        
        // Validar que las contraseñas coincidan
        if (newPassword !== confirmPassword) {
            Swal.fire('Error', 'Las contraseñas no coinciden', 'error');
            return;
        }
        
        // Validar longitud mínima
        if (newPassword.length < 6) {
            Swal.fire('Error', 'La contraseña debe tener al menos 6 caracteres', 'error');
            return;
        }
        
        // Confirmar acción
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Se cambiará la contraseña del usuario seleccionado',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, cambiar contraseña',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar indicador de carga
                Swal.fire({
                    title: 'Cambiando contraseña...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: 'api/users/admin-change-password',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        user_id: userId,
                        new_password: newPassword
                    }),
                    success: function(response) {
                        $('#modalChangeUserPassword').modal('hide');
                        $('#formChangeUserPassword')[0].reset();
                        Swal.fire('¡Éxito!', 'Contraseña cambiada correctamente', 'success');
                    },
                    error: function(xhr) {
                        console.error('Error changing password:', xhr.responseText);
                        Swal.fire('Error', xhr.responseJSON?.message || 'Error al cambiar la contraseña', 'error');
                    }
                });
            }
        });
    });

    // Load permissions on page load
    loadPermissions();

    // Clear forms on modal close
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
});