<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-search mr-2"></i>Consultar Reserva</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-2"></i> Ingrese el código de seguimiento que recibió al realizar su reserva.
                </div>
                
                <div class="form-group">
                    <label for="codigo_seguimiento">Código de Seguimiento</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                        </div>
                        <input type="text" class="form-control" id="codigo_seguimiento" placeholder="Ejemplo: RES-12345">
                        <div class="input-group-append">
                            <button onclick="buscarReservaPorCodigo()" class="btn btn-primary" type="button">Buscar</button>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="text-center mb-4">
                    <img src="assets/img/search.svg" alt="Búsqueda" class="img-fluid" style="max-width: 150px;">
                </div>
                
                <div class="alert alert-secondary">
                    <h5><i class="fas fa-question-circle mr-2"></i>¿No tiene su código de seguimiento?</h5>
                    <p>Si ha perdido su código de seguimiento, póngase en contacto con nuestro centro médico por teléfono para obtener ayuda.</p>
                </div>
                
                <div class="accordion" id="accordionPreguntas">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    ¿Qué es el código de seguimiento?
                                </button>
                            </h2>
                        </div>
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionPreguntas">
                            <div class="card-body">
                                El código de seguimiento es un identificador único que se genera al momento de crear su reserva. Este código le permite consultar los detalles de su cita en cualquier momento. Lo recibirá por correo electrónico cuando realice su reserva.
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    ¿Cómo puedo modificar mi reserva?
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionPreguntas">
                            <div class="card-body">
                                Para modificar una reserva existente, deberá contactar directamente con nuestro centro médico. Las reservas creadas en línea no pueden ser modificadas a través del sistema web, pero nuestro personal le ayudará a realizar los cambios necesarios.
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header" id="headingThree">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    ¿Puedo cancelar mi reserva?
                                </button>
                            </h2>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordionPreguntas">
                            <div class="card-body">
                                Sí, puede cancelar su reserva comunicándose con nuestro centro médico por teléfono. Le recomendamos hacerlo con al menos 24 horas de anticipación para que podamos ofrecer ese horario a otro paciente.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
