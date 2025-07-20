     //Initialize Select2 Elements
    $('.select2').select2()

    //Initialize Select2 Elements
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
    $('.compose-textarea').summernote()

    // Verificación periódica de sesión
    function verificarSesion() {
        $.ajax({
            url: 'ajax/verificar_sesion.ajax.php',
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (!response.sesion_activa) {
                    // La sesión ha expirado, redirigir al login
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesión Expirada',
                        text: 'Tu sesión ha expirado. Serás redirigido al login.',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(() => {
                        window.location.href = '?ruta=login';
                    });
                }
            },
            error: function() {
                // En caso de error, asumir que la sesión no es válida
                console.log('Error al verificar sesión');
            }
        });
    }

    // Verificar sesión cada 5 minutos (300000 ms) solo si estamos en páginas protegidas
    if (window.location.href.includes('ruta=')) {
        const ruta = new URLSearchParams(window.location.search).get('ruta');
        const paginasProtegidas = ['home', 'consultas', 'personas', 'roles', 'perfil', 'rhpersonas', 'preformatos', 'agendas', 'servicios', 'rs_servicios', 'citas', 'profesiones', 'especialidades', 'motivos', 'empresas', 'tipos_proveedores', 'proveedores', 'salas', 'turnos'];
        
        if (paginasProtegidas.includes(ruta)) {
            setInterval(verificarSesion, 300000); // 5 minutos
        }
    }

  //    // DropzoneJS Demo Code Start
  // Dropzone.autoDiscover = false

  // // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
  // var previewNode = document.querySelector("#template")
  // previewNode.id = ""
  // var previewTemplate = previewNode.parentNode.innerHTML
  // previewNode.parentNode.removeChild(previewNode)

  // var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
  //   url: "view/uploads/", // Set the url
  //   thumbnailWidth: 80,
  //   thumbnailHeight: 80,
  //   parallelUploads: 20,
  //   previewTemplate: previewTemplate,
  //   autoQueue: false, // Make sure the files aren't queued until manually added
  //   previewsContainer: "#previews", // Define the container to display the previews
  //   clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
  // })

  // myDropzone.on("addedfile", function(file) {
  //   // Hookup the start button
  //   file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file) }
  // })

  // // Update the total progress bar
  // myDropzone.on("totaluploadprogress", function(progress) {
  //   document.querySelector("#total-progress .progress-bar").style.width = progress + "%"
  // })

  // myDropzone.on("sending", function(file) {
  //   // Show the total progress bar when upload starts
  //   document.querySelector("#total-progress").style.opacity = "1"
  //   // And disable the start button
  //   file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
  // })

  // // Hide the total progress bar when nothing's uploading anymore
  // myDropzone.on("queuecomplete", function(progress) {
  //   document.querySelector("#total-progress").style.opacity = "0"
  // })

  // // Setup the buttons for all transfers
  // // The "add files" button doesn't need to be setup because the config
  // // `clickable` has already been specified.
  // document.querySelector("#actions .start").onclick = function() {
  //   myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
  // }
  // document.querySelector("#actions .cancel").onclick = function() {
  //   myDropzone.removeAllFiles(true)
  // }
  // // DropzoneJS Demo Code End
  