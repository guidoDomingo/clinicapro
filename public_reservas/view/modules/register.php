<?php
/**
 * Vista de registro para reservas públicas
 * Adaptada del sistema principal
 */
?>
<div class="register-box mx-auto">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="index.php" class="h1"><b>Reservas</b>MiClinica</a>
      <div class="logo-container">
        <img src="../../view/img/thnlogo.jpg" alt="Logo de la clínica" style="max-height: 60px;">
      </div>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Registro de nuevo usuario</p>

      <?php if (isset($resultadoAuth) && $resultadoAuth !== null): ?>
        <?php if (isset($resultadoAuth['error']) && $resultadoAuth['error']): ?>
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $resultadoAuth['mensaje']; ?>
          </div>
        <?php elseif (isset($resultadoAuth['mensaje'])): ?>
          <div class="alert alert-success">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $resultadoAuth['mensaje']; ?>
            <?php if (isset($resultadoAuth['redirect'])): ?>
            <script>
                setTimeout(function() {
                    window.location.href = "<?php echo $resultadoAuth['redirect']; ?>";
                }, 1500);
            </script>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['auth_message'])): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle mr-2"></i> <?php echo $_SESSION['auth_message']; ?>
        </div>
        <?php unset($_SESSION['auth_message']); ?>
      <?php endif; ?>

      <form id="frmRegister" method="post">
        <div class="input-group mb-3">
          <input type="text" class="form-control" id="regDoc" name="reg_document" placeholder="Documento de identidad" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-id-card"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" class="form-control" id="regName" name="reg_name" placeholder="Nombres" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" class="form-control" id="regLastName" name="reg_lastname" placeholder="Apellidos" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="email" class="form-control" id="regEmail" name="reg_email" placeholder="Correo electrónico" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" id="regTel" name="reg_phone" class="form-control" placeholder="Teléfono" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span><i class="fas fa-phone"></i></span>
            </div>
          </div>
        </div>
        <!-- <div class="input-group mb-3">
          <input type="password" id="regPassword" name="reg_password" class="form-control" placeholder="Contraseña" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" id="regConfirmPassword" name="reg_confirm_password" class="form-control" placeholder="Confirmar contraseña" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div> -->
        <div class="input-group mb-3">
          <input type="date" id="regBdate" name="reg_bdate" class="form-control" placeholder="Fecha de nacimiento" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-calendar"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="acceptTerms" name="acceptTerms" required>
              <label for="acceptTerms">
               Acepto los <a href="#">términos y condiciones</a>
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block" id="btnRegister">
              <span class="normal-text">Registrar</span>
              <span class="spinner-border spinner-border-sm ms-1" role="status" style="display: none;">
                <span class="visually-hidden">Cargando...</span>
              </span>
            </button>
          </div>
          <!-- Botón alternativo en caso de que el submit no funcione -->
          <!-- <div class="col-12 mt-2">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnRegistrarAlternativo">
              Registrar (Alternativo)
            </button>
          </div> -->
          <!-- /.col -->
        </div>
      </form>

      <a href="index.php?view=login" class="text-center">Ya tengo una cuenta</a>
    </div>
    <!-- /.form-box -->
  </div><!-- /.card -->
</div>

<div class="row mt-4">
  <div class="col-md-8 mx-auto">
    <div class="card bg-light">
      <div class="card-body">
        <h5>¿Por qué registrarse?</h5>
        <p>Registrarse en nuestro sistema le permitirá:</p>
        <ul>
          <li>Reservar citas médicas de forma rápida y sencilla</li>
          <li>Recibir recordatorios de sus próximas citas</li>
          <li>Gestionar su historial médico y consultas previas</li>
          <li>Acceder a servicios exclusivos para pacientes registrados</li>
        </ul>
        <p><strong>Nota:</strong> Sus datos personales están protegidos según la ley de protección de datos vigente.</p>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Registro Exitoso -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title text-white" id="successModalLabel">Registro Exitoso</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>¡Su registro ha sido completado exitosamente!</p>
        <p>Se ha enviado un correo electrónico con sus credenciales de acceso. Por favor, revise su bandeja de entrada y siga las instrucciones para activar su cuenta.</p>
        <p>Una vez activada su cuenta, podrá iniciar sesión y acceder a todos los servicios del sistema de reservas.</p>
      </div>
      <div class="modal-footer">
        <a href="index.php?view=login" class="btn btn-primary">Ir a Iniciar Sesión</a>
      </div>
    </div>
  </div>
</div>

<!-- Asegurar que jQuery esté cargado -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
$(document).ready(function() {
  console.log('Script de registro inicializado');
  
  // Botón alternativo para enviar el formulario
  $('#btnRegistrarAlternativo').click(function() {
    console.log('Botón alternativo presionado');
    enviarFormulario();
  });
  
  // Función para enviar el formulario
  function enviarFormulario() {
    console.log('Función enviarFormulario ejecutada');
    
    if(!$('#acceptTerms').is(':checked')) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Debe aceptar los términos y condiciones'
      });
      return false;
    }
    
    // Verificar que las contraseñas coincidan
    if($('#regPassword').val() !== $('#regConfirmPassword').val()) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Las contraseñas no coinciden'
      });
      return false;
    }
    
    // Verificar que la fecha de nacimiento esté presente
    if(!$('#regBdate').val()) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'La fecha de nacimiento es obligatoria'
      });
      return false;
    }
    
    console.log('Formulario validado correctamente');
    
    // Obtener los datos del formulario en el formato que espera la API
    var formData = {
      reg_document: $('#regDoc').val(),
      reg_name: $('#regName').val(),
      reg_lastname: $('#regLastName').val(),
      reg_email: $('#regEmail').val(),
      reg_phone: $('#regTel').val(),
      reg_password: $('#regPassword').val(),
      reg_confirm_password: $('#regConfirmPassword').val(),
      reg_bdate: $('#regBdate').val(), // Añadido el campo de fecha de nacimiento
      reg_activation: 'pending'
    };
    
    // Mostrar spinner y deshabilitar botón
    const $button = $('#btnRegister');
    const $spinner = $button.find('.spinner-border');
    const $text = $button.find('.normal-text');
    
    $spinner.show();
    $text.text('Registrando...');
    $button.prop('disabled', true);
    
    // Usar la URL que sabemos que funciona
    var apiUrl = window.location.origin + '/api/register';
    console.log('URL de la API:', apiUrl);
    console.log('Enviando datos:', formData);
    
    // Usar jQuery AJAX para mayor compatibilidad
    $.ajax({
      url: apiUrl,
      type: 'POST',
      dataType: 'json',
      contentType: 'application/json',
      data: JSON.stringify(formData),
      success: function(data) {
        console.log('Respuesta exitosa:', data);
        
        // Ocultar spinner y restaurar botón
        $spinner.hide();
        $text.text('Registrar');
        $button.prop('disabled', false);
        
        // Verificar si la respuesta contiene un error
        if (data && data.error) {
          var errorMessage = 'Ha ocurrido un error al procesar su registro.';
          if (data.error.message) {
            errorMessage = data.error.message;
          }
          
          Swal.fire({
            icon: 'error',
            title: 'Error de Registro',
            text: errorMessage
          });
        } else {
          // Mostrar modal de éxito
          $('#successModal').modal('show');
          
          // Limpiar el formulario
          $('#frmRegister')[0].reset();
        }
      },
      error: function(xhr, status, error) {
        // Ocultar spinner y restaurar botón
        $spinner.hide();
        $text.text('Registrar');
        $button.prop('disabled', false);
        
        console.error('Error en la solicitud AJAX:');
        console.error('Status:', status);
        console.error('Error:', error);
        console.error('Respuesta:', xhr.responseText);
        
        // Mostrar detalles del error para diagnóstico
        let errorMessage = 'Error desconocido';
        try {
          let jsonResponse = JSON.parse(xhr.responseText);
          if (jsonResponse && jsonResponse.error) {
            errorMessage = jsonResponse.error.message || 'Error en el servidor';
          }
        } catch (e) {
          errorMessage = 'Error de comunicación: ' + error;
          
          // Prueba con otra URL si falla
          if (xhr.status === 404) {
            console.log('Intentando con URL alternativa...');
            setTimeout(function() {
              intentarConURLAlternativa(formData);
            }, 500);
          }
        }
        
        Swal.fire({
          icon: 'error',
          title: 'Error de Registro',
          html: 'Ha ocurrido un error al procesar su registro.<br><br>' +
                '<strong>Detalles técnicos:</strong><br>' +
                'URL: ' + apiUrl + '<br>' +
                'Código: ' + xhr.status + '<br>' + 
                'Error: ' + errorMessage,
          confirmButtonText: 'Entendido'
        });
      }
    });
  }
  
  // Función para intentar con URL alternativa
  function intentarConURLAlternativa(formData) {
    // Asegurar que formData tiene todos los campos necesarios
    if (!formData.reg_bdate) {
      formData.reg_bdate = $('#regBdate').val();
    }
    
    const $button = $('#btnRegister');
    const $spinner = $button.find('.spinner-border');
    const $text = $button.find('.normal-text');
    
    // Probar con otra ruta
    var altApiUrl = window.location.origin + '/api/register';
    console.log('Intentando con URL alternativa:', altApiUrl);
    
    $.ajax({
      url: altApiUrl,
      type: 'POST',
      dataType: 'json',
      contentType: 'application/json',
      data: JSON.stringify(formData),
      success: function(data) {
        console.log('Respuesta exitosa (alt):', data);
        
        // Ocultar spinner y restaurar botón
        $spinner.hide();
        $text.text('Registrar');
        $button.prop('disabled', false);
        
        if (data && data.status === 'success') {
          // Mostrar modal de éxito con mensaje personalizado si existe
          let successMessage = 'Su registro ha sido completado exitosamente.';
          if (data.data && data.data.message) {
            successMessage = data.data.message;
          }
          
          Swal.fire({
            icon: 'success',
            title: 'Registro Exitoso',
            text: successMessage,
            showConfirmButton: true,
            confirmButtonText: 'Continuar'
          }).then((result) => {
            if (result.isConfirmed) {
              $('#successModal').modal('show');
              $('#frmRegister')[0].reset();
            }
          });
        } else {
          // Si hay un error específico en la respuesta
          var errorMessage = (data && data.error && data.error.message) ? 
                           data.error.message : 
                           'Ha ocurrido un error al procesar su registro.';
          Swal.fire({
            icon: 'error',
            title: 'Error de Registro',
            text: errorMessage
          });
        }
      },
      error: function(xhr, status, error) {
        // Ocultar spinner y restaurar botón
        $spinner.hide();
        $text.text('Registrar');
        $button.prop('disabled', false);
        
        console.error('Error en la solicitud alternativa:', status, error);
        Swal.fire({
          icon: 'error',
          title: 'Error de Registro',
          html: 'No se pudo conectar con el servidor. Por favor, inténtelo más tarde.<br><br>' +
                '<strong>URLs probadas:</strong><br>' +
                '/api/register<br>' +
                altApiUrl,
          confirmButtonText: 'Entendido'
        });
      }
    });
  }
  
  // Manejar el envío del formulario de registro
  $('#frmRegister').submit(function(e) {
    e.preventDefault();
    console.log('Formulario submit interceptado');
    enviarFormulario();
    return false;
  });
});
</script>
