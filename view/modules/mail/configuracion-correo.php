<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuración de rutas
include_once __DIR__ . '/../../inc/config_rutas.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Configuración de Correo Electrónico</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="referenciales">Referenciales</a></li>
                        <li class="breadcrumb-item active">Configuración de Correo</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Alerta de estado -->
            <div id="alertContainer"></div>
            
            <!-- Card de configuración -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-envelope"></i> Configuración SMTP
                            </h3>
                            <div class="card-tools">
                                <span id="statusBadge" class="badge badge-secondary">Cargando...</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="mailConfigForm">
                                <div class="row">
                                    <!-- Configuración del Servidor -->
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-server"></i> Servidor SMTP</h5>
                                        
                                        <div class="form-group">
                                            <label for="smtp_host">Host SMTP</label>
                                            <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                                   placeholder="sandbox.smtp.mailtrap.io" required>
                                            <small class="form-text text-muted">Servidor SMTP (ej: sandbox.smtp.mailtrap.io)</small>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="smtp_port">Puerto</label>
                                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                                           value="2525" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="smtp_secure">Seguridad</label>
                                                    <select class="form-control" id="smtp_secure" name="smtp_secure">
                                                        <option value="">Ninguna</option>
                                                        <option value="tls">TLS</option>
                                                        <option value="ssl">SSL</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="smtp_auth" name="smtp_auth" checked>
                                                <label class="custom-control-label" for="smtp_auth">Requiere autenticación</label>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="smtp_username">Usuario</label>
                                            <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                                   placeholder="usuario_mailtrap" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="smtp_password">Contraseña</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                                       placeholder="contraseña_mailtrap" required>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('smtp_password')">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Configuración del Remitente -->
                                    <div class="col-md-6">
                                        <h5><i class="fas fa-user"></i> Información del Remitente</h5>
                                        
                                        <div class="form-group">
                                            <label for="from_email">Email del Remitente</label>
                                            <input type="email" class="form-control" id="from_email" name="from_email" 
                                                   placeholder="noreply@clinica.test" required>
                                            <small class="form-text text-muted">Email que aparecerá como remitente</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="from_name">Nombre del Remitente</label>
                                            <input type="text" class="form-control" id="from_name" name="from_name" 
                                                   placeholder="Sistema Clínica" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="reply_to_email">Email de Respuesta (Opcional)</label>
                                            <input type="email" class="form-control" id="reply_to_email" name="reply_to_email" 
                                                   placeholder="info@clinica.com">
                                            <small class="form-text text-muted">Email para respuestas (opcional)</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="reply_to_name">Nombre para Respuestas (Opcional)</label>
                                            <input type="text" class="form-control" id="reply_to_name" name="reply_to_name" 
                                                   placeholder="Soporte Clínica">
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active">
                                                <label class="custom-control-label" for="is_active">Configuración Activa</label>
                                                <small class="form-text text-muted">Marcar para activar esta configuración</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Botones de acción -->
                                <div class="row">
                                    <div class="col-12">
                                        <hr>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar Configuración
                                        </button>
                                        <button type="button" class="btn btn-info" onclick="testMailConnection()">
                                            <i class="fas fa-paper-plane"></i> Probar Conexión
                                        </button>
                                        <!-- <button type="button" class="btn btn-warning" onclick="runDiagnostic()">
                                            <i class="fas fa-tools"></i> Diagnóstico
                                        </button> -->
                                        <a href="referenciales" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Volver
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Card de ayuda -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-question-circle"></i> Ayuda
                            </h3>
                        </div>
                        <div class="card-body">
                            <h6><i class="fas fa-info-circle text-info"></i> Mailtrap (Pruebas)</h6>
                            <p class="small">Para pruebas, usa los valores:</p>
                            <ul class="small">
                                <li><strong>Host:</strong> sandbox.smtp.mailtrap.io</li>
                                <li><strong>Puerto:</strong> 2525</li>
                                <li><strong>Usuario:</strong> Tu username de Mailtrap</li>
                                <li><strong>Contraseña:</strong> Tu password de Mailtrap</li>
                            </ul>
                            
                            <hr>
                            
                            <h6><i class="fas fa-envelope text-success"></i> Gmail</h6>
                            <p class="small">Para Gmail:</p>
                            <ul class="small">
                                <li><strong>Host:</strong> smtp.gmail.com</li>
                                <li><strong>Puerto:</strong> 587</li>
                                <li><strong>Seguridad:</strong> TLS</li>
                                <li><strong>Usuario:</strong> tu-email@gmail.com</li>
                                <li><strong>Contraseña:</strong> App Password</li>
                            </ul>
                            
                            <hr>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Importante:</strong> Después de guardar, prueba la conexión antes de enviar correos.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Logs recientes -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history"></i> Últimos Envíos
                            </h3>
                        </div>
                        <div class="card-body">
                            <div id="recentLogs">
                                <p class="text-muted">Cargando logs...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    loadMailConfig();
    loadRecentLogs();
});

// Cargar configuración actual
async function loadMailConfig() {
    try {
        const url = window.APP_CONFIG.apiBase + 'mail_config.php?action=get';
        console.log('Debug: URL construida para mail config:', url);
        console.log('Debug: APP_CONFIG:', window.APP_CONFIG);
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success && data.config) {
            const config = data.config;
            
            // Llenar formulario
            document.getElementById('smtp_host').value = config.smtp_host || '';
            document.getElementById('smtp_port').value = config.smtp_port || 2525;
            document.getElementById('smtp_secure').value = config.smtp_secure || '';
            document.getElementById('smtp_auth').checked = config.smtp_auth || false;
            document.getElementById('smtp_username').value = config.smtp_username || '';
            document.getElementById('smtp_password').value = config.smtp_password || '';
            document.getElementById('from_email').value = config.from_email || '';
            document.getElementById('from_name').value = config.from_name || '';
            document.getElementById('reply_to_email').value = config.reply_to_email || '';
            document.getElementById('reply_to_name').value = config.reply_to_name || '';
            document.getElementById('is_active').checked = config.is_active || false;
            
            // Actualizar badge de estado
            const statusBadge = document.getElementById('statusBadge');
            if (config.is_active) {
                statusBadge.className = 'badge badge-success';
                statusBadge.textContent = 'Activo';
            } else {
                statusBadge.className = 'badge badge-warning';
                statusBadge.textContent = 'Inactivo';
            }
        }
    } catch (error) {
        console.error('Error cargando configuración:', error);
        showAlert('error', 'Error al cargar la configuración de correo');
    }
}

// Guardar configuración
document.getElementById('mailConfigForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    
    // Convertir checkbox a booleanos
    data.smtp_auth = document.getElementById('smtp_auth').checked;
    data.is_active = document.getElementById('is_active').checked;
    
    try {
        const response = await fetch(window.APP_CONFIG.mailApi, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save',
                config: data
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', 'Configuración guardada exitosamente');
            loadMailConfig(); // Recargar configuración
        } else {
            showAlert('error', result.message || 'Error al guardar la configuración');
        }
    } catch (error) {
        console.error('Error guardando configuración:', error);
        showAlert('error', 'Error al guardar la configuración');
    }
});

// Probar conexión de correo
async function testMailConnection() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Probando...';
    
    try {
        const response = await fetch(window.APP_CONFIG.mailApi, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'test'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('success', 'Conexión exitosa. Email de prueba enviado.');
        } else {
            showAlert('error', result.message || 'Error en la conexión');
        }
    } catch (error) {
        console.error('Error probando conexión:', error);
        showAlert('error', 'Error al probar la conexión');
    } finally {
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

// Cargar logs recientes
async function loadRecentLogs() {
    try {
        const url = window.APP_CONFIG.apiBase + 'mail_config.php?action=logs&limit=5';
        console.log('Debug: URL construida para logs:', url);
        
        const response = await fetch(url);
        const data = await response.json();
        
        const logsContainer = document.getElementById('recentLogs');
        
        if (data.success && data.logs.length > 0) {
            let html = '';
            data.logs.forEach(log => {
                const statusClass = log.status === 'sent' ? 'success' : 'danger';
                const statusIcon = log.status === 'sent' ? 'check' : 'times';
                html += `
                    <div class="small mb-2">
                        <i class="fas fa-${statusIcon} text-${statusClass}"></i>
                        <strong>Consulta ${log.consulta_id}</strong><br>
                        Para: ${log.recipient_email}<br>
                        <small class="text-muted">${new Date(log.sent_at).toLocaleString()}</small>
                    </div>
                    <hr class="my-2">
                `;
            });
            logsContainer.innerHTML = html;
        } else {
            logsContainer.innerHTML = '<p class="text-muted small">No hay envíos recientes</p>';
        }
    } catch (error) {
        console.error('Error cargando logs:', error);
    }
}

// Mostrar/ocultar contraseña
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = event.target.closest('button').querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        field.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Ejecutar diagnóstico del sistema
async function runDiagnostic() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Diagnosticando...';
    
    try {
        const response = await fetch(window.APP_CONFIG.apiBase + 'check_mail_setup.php');
        const result = await response.json();
        
        let messageType = result.success ? 'success' : 'warning';
        let messageIcon = result.success ? 'check-circle' : 'exclamation-triangle';
        
        let detailsHtml = '<div class="mt-3">';
        detailsHtml += `<h6>Resumen: ${result.summary.passed}/${result.summary.total} verificaciones pasaron</h6>`;
        detailsHtml += '<ul class="list-unstyled">';
        
        for (let check of Object.values(result.checks)) {
            let statusIcon = check.status ? 'fa-check text-success' : 'fa-times text-danger';
            detailsHtml += `<li><i class="fas ${statusIcon}"></i> <strong>${check.name}:</strong> ${check.message}</li>`;
        }
        
        detailsHtml += '</ul></div>';
        
        Swal.fire({
            icon: messageType,
            title: 'Diagnóstico del Sistema de Correo',
            html: detailsHtml,
            width: 600,
            confirmButtonText: 'Entendido'
        });
        
    } catch (error) {
        console.error('Error en diagnóstico:', error);
        showAlert('error', 'Error al ejecutar el diagnóstico: ' + error.message);
    } finally {
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

// Mostrar alertas
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertIcon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
    
    alertContainer.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas fa-${alertIcon}"></i> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            $(alert).alert('close');
        }
    }, 5000);
}
</script>