<?php
/**
 * Vista de registro para reservas públicas
 * Adaptada del sistema principal
 */
?>
<div class="register-box mx-auto">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="index.php" class="h1"><b>Reservas</b></a>
      <div class="logo-container">
        <img src="../../view/img/logo.png" alt="Centro oftalmológico" style="max-height: 60px;">
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
        <div class="input-group mb-3">
          <input type="date" id="regBdate" name="reg_bdate" class="form-control" placeholder="Fecha de nacimiento" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-calendar"></span>
            </div>
          </div>
        </div>
        <div class="alert alert-info">
          <i class="fas fa-info-circle mr-2"></i>
          <strong>Nota:</strong> Su contraseña temporal será enviada por correo electrónico una vez completado el registro.
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
              <span class="btn-text">Registrar</span>
              <span class="btn-spinner" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Registrando...
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
        <p><strong>Se ha enviado un correo electrónico con sus credenciales de acceso.</strong></p>
        
        <div class="alert alert-info">
          <h6><i class="fas fa-info-circle mr-2"></i>Próximos pasos:</h6>
          <ol class="mb-0">
            <li>Revise su bandeja de entrada de email</li>
            <li><strong>Usuario:</strong> Su dirección de correo electrónico</li>
            <li><strong>Contraseña:</strong> La contraseña temporal enviada por email</li>
            <li>Inicie sesión para acceder al sistema de reservas</li>
          </ol>
        </div>
        
        <p class="text-muted"><small>Si no encuentra el correo, revise su carpeta de spam.</small></p>
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
  
  // Función para verificar si un email ya existe
  function verificarEmailUnico(email) {
    return fetch('index.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'action=verificar_email&email=' + encodeURIComponent(email)
    })
    .then(response => response.json())
    .then(data => {
      return !data.existe; // Retorna true si NO existe (es único)
    })
    .catch(error => {
      console.error('Error al verificar email:', error);
      return true; // En caso de error, permitir continuar
    });
  }
  
  // Función para verificar si un documento ya existe
  function verificarDocumentoUnico(documento) {
    return fetch('index.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: 'action=verificar_documento&documento=' + encodeURIComponent(documento)
    })
    .then(response => response.json())
    .then(data => {
      return !data.existe; // Retorna true si NO existe (es único)
    })
    .catch(error => {
      console.error('Error al verificar documento:', error);
      return true; // En caso de error, permitir continuar
    });
  }
  
  // Event listeners para validación en tiempo real
  let emailTimeout, documentoTimeout;
  
  $('#regEmail').on('input', function() {
    const email = $(this).val();
    const $field = $(this);
    
    // Limpiar timeout anterior
    clearTimeout(emailTimeout);
    
    // Limpiar indicadores previos
    $field.removeClass('is-valid is-invalid');
    $('#emailFeedback').remove();
    
    if (email.length > 0 && email.includes('@')) {
      emailTimeout = setTimeout(async () => {
        try {
          const emailUnico = await verificarEmailUnico(email);
          if (emailUnico) {
            $field.addClass('is-valid');
            $field.after('<div id="emailFeedback" class="valid-feedback">Email disponible</div>');
          } else {
            $field.addClass('is-invalid');
            $field.after('<div id="emailFeedback" class="invalid-feedback">Este email ya está registrado</div>');
          }
        } catch (error) {
          console.error('Error al validar email:', error);
        }
      }, 500); // Esperar 500ms después de que pare de escribir
    }
  });
  
  $('#regDoc').on('input', function() {
    const documento = $(this).val();
    const $field = $(this);
    
    // Limpiar timeout anterior
    clearTimeout(documentoTimeout);
    
    // Limpiar indicadores previos
    $field.removeClass('is-valid is-invalid');
    $('#docFeedback').remove();
    
    if (documento.length >= 7) { // Validar solo si tiene al menos 7 caracteres
      documentoTimeout = setTimeout(async () => {
        try {
          const documentoUnico = await verificarDocumentoUnico(documento);
          if (documentoUnico) {
            $field.addClass('is-valid');
            $field.after('<div id="docFeedback" class="valid-feedback">Documento disponible</div>');
          } else {
            $field.addClass('is-invalid');
            $field.after('<div id="docFeedback" class="invalid-feedback">Este documento ya está registrado</div>');
          }
        } catch (error) {
          console.error('Error al validar documento:', error);
        }
      }, 500); // Esperar 500ms después de que pare de escribir
    }
  });

  // Botón alternativo para enviar el formulario
  $('#btnRegistrarAlternativo').click(function() {
    console.log('Botón alternativo presionado');
    enviarFormulario();
  });
  
  // Función para enviar el formulario con validaciones de duplicados
  async function enviarFormulario() {
    console.log('Función enviarFormulario ejecutada');
    
    if(!$('#acceptTerms').is(':checked')) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Debe aceptar los términos y condiciones'
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
    
    // Obtener valores de email y documento
    const email = $('#regEmail').val();
    const documento = $('#regDoc').val();
    
    // Verificar que el email no esté duplicado
    try {
      const emailUnico = await verificarEmailUnico(email);
      if (!emailUnico) {
        Swal.fire({
          icon: 'error',
          title: 'Email ya registrado',
          text: 'El email ingresado ya está registrado en el sistema. Por favor use otro email o inicie sesión.'
        });
        return false;
      }
    } catch (error) {
      console.error('Error al verificar email único:', error);
    }
    
    // Verificar que el documento no esté duplicado
    try {
      const documentoUnico = await verificarDocumentoUnico(documento);
      if (!documentoUnico) {
        Swal.fire({
          icon: 'error',
          title: 'Documento ya registrado',
          text: 'El número de documento ingresado ya está registrado en el sistema. Por favor verifique el documento o inicie sesión.'
        });
        return false;
      }
    } catch (error) {
      console.error('Error al verificar documento único:', error);
    }
    
    console.log('Formulario validado correctamente - sin duplicados');
    
    // Mostrar spinner y deshabilitar botón
    const $button = $('#btnRegister');
    const $spinner = $button.find('.btn-spinner');
    const $text = $button.find('.btn-text');
    
    $text.hide();
    $spinner.show();
    $button.prop('disabled', true);
    
    // Usar la URL del sistema public_reservas en lugar de la API
    var formAction = 'index.php';
    console.log('URL de destino:', formAction);
    
    // Crear FormData con los nombres de campos esperados por AuthController
    var formData = new FormData();
    formData.append('action', 'register');
    formData.append('regName', $('#regName').val());
    formData.append('regLastName', $('#regLastName').val());
    formData.append('regEmail', $('#regEmail').val());
    formData.append('regDoc', $('#regDoc').val());
    formData.append('regTel', $('#regTel').val());
    formData.append('regBdate', $('#regBdate').val());
    formData.append('acceptTerms', $('#acceptTerms').is(':checked') ? 'on' : 'off');
    
    console.log('Enviando datos via FormData (sin contraseñas)');
    
    // Usar fetch para enviar al AuthController
    fetch(formAction, {
      method: 'POST',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    })
    .then(response => {
      // Verificar si la respuesta es exitosa
      if (response.ok) {
        return response.json(); // Obtener como JSON
      }
      throw new Error('Error en la respuesta del servidor');
    })
    .then(data => {
      console.log('Respuesta exitosa:', data);
      
      // Ocultar spinner y restaurar botón
      $spinner.hide();
      $text.show();
      $button.prop('disabled', false);
      
      // Si no hay error, es exitoso
      if (!data.error) {
        // Mostrar modal de éxito con información sobre el email
        Swal.fire({
          icon: 'success',
          title: 'Registro Exitoso',
          html: `
            <p><strong>${data.mensaje}</strong></p>
            <div class="alert alert-info mt-3">
              <i class="fas fa-envelope mr-2"></i>
              <strong>Próximos pasos:</strong><br>
              1. Revise su bandeja de entrada de email<br>
              2. Use su <strong>email</strong> como usuario<br>
              3. Use la <strong>contraseña temporal</strong> enviada por correo<br>
              4. Inicie sesión para acceder al sistema
            </div>
          `,
          showConfirmButton: true,
          confirmButtonText: 'Ir a Iniciar Sesión',
          allowOutsideClick: false
        }).then((result) => {
          if (result.isConfirmed) {
            // Limpiar formulario
            $('#frmRegister')[0].reset();
            
            // Redirigir al login
            window.location.href = 'index.php?view=login';
          }
        });
      } else {
        // Mostrar error
        Swal.fire({
          icon: 'error',
          title: 'Error de Registro',
          text: data.mensaje
        });
      }
    })
    .catch(error => {
      console.error('Error:', error);
      
      // Ocultar spinner y restaurar botón
      $spinner.hide();
      $text.show();
      $button.prop('disabled', false);
      
      Swal.fire({
        icon: 'error',
        title: 'Error de Registro',
        text: 'Ha ocurrido un error al procesar su registro. Por favor, intente nuevamente.'
      });
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
