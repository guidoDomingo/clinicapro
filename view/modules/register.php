<div class="register-box">
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="index.php" class="h1"><b>Mi</b>Clinica</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Registro de nuevo usuario</p>

      <form id="frmRegister" method="post">
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="reg_document" id="reg_document" placeholder="Documento de identidad" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-id-card"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="reg_name" id="reg_name" placeholder="Nombres" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" class="form-control" name="reg_lastname" id="reg_lastname" placeholder="Apellidos" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="email" class="form-control" id="reg_email" name="reg_email" placeholder="Correo electrónico" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="text" id="reg_phone" name="reg_phone" class="form-control" placeholder="Teléfono" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span><i class="fas fa-phone"></i></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="date" id="reg_bdate" name="reg_bdate" class="form-control" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-calendar"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary">
              <input type="checkbox" id="agreeTerms" name="agreeTerms" value="agree" required>
              <label for="agreeTerms">
               Acepto los <a href="#">términos y condiciones</a>
              </label>
            </div>
          </div>
          
          <!-- /.col -->          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block" id="btnregister">
              <span class="normal-text">Registrarse</span>
              <span class="spinner-border spinner-border-sm ms-1" role="status" style="display: none; width: 1rem; height: 1rem;">
                <span class="visually-hidden">Cargando...</span>
              </span>
            </button>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <a href="index.php?route=login" class="text-center">Ya tengo una cuenta</a>
    </div>
    <!-- /.form-box -->
  </div><!-- /.card -->
</div>

<!-- Modal de Registro Exitoso -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title" id="successModalLabel">Registro Exitoso</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Su registro ha sido completado exitosamente. Se ha enviado un correo electrónico con sus credenciales de acceso.</p>
        <p>Por favor, revise su bandeja de entrada y siga las instrucciones para iniciar sesión.</p>
      </div>
      <div class="modal-footer">
        <a href="index.php?route=login" class="btn btn-primary">Ir a Iniciar Sesión</a>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  // Manejar el envío del formulario de registro
  $('#frmRegister').submit(function(e) {
    e.preventDefault();
    
    if(!$('#agreeTerms').is(':checked')) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Debe aceptar los términos y condiciones'
      });
      return false;
    }
    
    // Obtener los datos del formulario
    var formData = {
      reg_document: $('#reg_document').val(),
      reg_name: $('#reg_name').val(),
      reg_lastname: $('#reg_lastname').val(),
      reg_email: $('#reg_email').val(),
      reg_phone: $('#reg_phone').val(),
      reg_bdate: $('#reg_bdate').val(),
      reg_activation: 'pending' // Valor por defecto
    };
      // Mostrar spinner y deshabilitar botón
    const $button = $('#btnregister');
    const $spinner = $button.find('.spinner-border');
    const $text = $button.find('.normal-text');
    
    $spinner.show();
    $text.text('Registrando...');
    $button.prop('disabled', true);
    
    // Enviar los datos a la API
    $.ajax({
      url: 'api/register',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(formData),
      success: function(response) {
        // Ocultar spinner y restaurar botón
        $spinner.hide();
        $text.text('Registrarse');
        $button.prop('disabled', false);
        
        // Mostrar modal de éxito
        $('#successModal').modal('show');
        
        // Limpiar el formulario
        $('#frmRegister')[0].reset();
      },
      error: function(xhr) {
        // Ocultar spinner y restaurar botón
        $spinner.hide();
        $text.text('Registrarse');
        $button.prop('disabled', false);
        
        // Mostrar mensaje de error
        var errorMessage = 'Ha ocurrido un error al procesar su registro.';
        if (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.message) {
          errorMessage = xhr.responseJSON.error.message;
        }
        
        Swal.fire({
          icon: 'error',
          title: 'Error de Registro',
          text: errorMessage
        });
      }
    });
  });
});
</script>