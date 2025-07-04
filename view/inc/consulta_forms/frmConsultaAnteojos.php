<form id="tblConsulta" method="post" enctype="multipart/form-data">
    <div class="form-row fx" id="fx">
        <div class="form-group col-md-2">
            <label for="txtdocumento">Documento</label>
            <input type="text" class="form-control" id="txtdocumento" name="txtdocumento" placeholder="Cedula de identidad">
        </div>
        <div class="form-group col-md-2">
            <label for="txtficha">Ficha</label>
            <input type="text" class="form-control" id="txtficha" name="txtficha" placeholder="Ficha médica">
        </div>
        <div class="col-md-6 col-md-8">
            <label for="txtnombres">Nombres</label>
            <div class="input-group">
                <input type="text" class="form-control" id="paciente" placeholder="Buscar paciente..." aria-label="Buscar paciente">
                <div class="input-group-append">
                    <!-- <button  type="button"  class="btn btn-primary" onclick="buscar(document.getElementById('txtdocumento').value, document.getElementById('txtficha').value)" aria-label="Buscar"> -->
                    <button  type="button"  class="btn btn-primary" id="btnBuscarPersona" aria-label="Buscar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    <button   type="button" class="btn btn-success">
                        <i class="fa-solid fa-user-plus"></i>
                    </button>
                    <button  type="button" class="btn btn-dark" id="btnLimpiarPersona" aria-label="Limpiar">
                        <i class="fa-solid fa-eraser"></i>
                    </button>
                </div>
            </div>
        </div>
        <input type="hidden" id="idPersona" name="idPersona" required>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="motivoscomunes">Motivos comunes</label>
            <select class="form-control select2bs4" id="motivoscomunes" name="motivoscomunes" style="width: 100%;">
                <option selected="selected">Seleccionar</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="formatoConsulta">Preformato</label>
            <select class="form-control select2bs4 " id="formatoConsulta" name="formatoConsulta" style="width: 100%;">
                <option selected="selected">Seleccionar</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="txtmotivo">Motivo</label>
        <input type="text" class="form-control" id="txtmotivo" name="txtmotivo" placeholder="Motivo de consulta">
    </div>
    <div id="receta"  style="background: linear-gradient(to right,rgb(29, 140, 244),rgb(81, 157, 232)); padding: 20px; border-radius: 8px; box-shadow: inset 0 0 10px rgba(2, 38, 242, 0.05);">
        <h5>OD</h5>
        <div class="form-row">
            
            <div class="form-group col-md-3">
                <label for="visionod">Esfera (ESF)</label>
                <select class="form-control  select2bs4" name="od_esf" id="od_esf" style="width: 100%;">
                </select>
                <!-- <input type="text" class="form-control" id="visionod" name="visionod" placeholder="Visión OD"> -->
            </div>
            <div class="form-group col-md-3">
                <label for="visionoi">Cilindro (CIL)</label>
                <select class="form-control  select2bs4" name="od_cil" id="od_cil" style="width: 100%;">
                </select>
                <!-- <input type="text" class="form-control" id="visionoi" name="visionoi" placeholder="Visión OI"> -->
            </div>
            <div class="form-group col-md-3">
                <label for="visionod">Eje</label>
                <input type="text" class="form-control" id="ejeod" name="ejeod" placeholder="Eje OD">
            </div>
            <div class="form-group col-md-3">
                <label for="visionoi">Dnp</label>
                <input type="text" class="form-control" id="dnpod" name="visionoi" placeholder="DNP OD">
            </div>
            <div class="form-group col-md-3">
                <label for="visionoi">Adición</label>
                <select class="form-control  select2bs4" name="od_adicion" id="od_adicion" style="width: 100%;">
                </select>
                <!-- <input type="text" class="form-control" id="visionoi" name="visionoi" placeholder="Visión OI"> -->
            </div>
            <div class="form-group col-md-9">
                <label for="visionoi">Nota:</label>
                <input type="text" class="form-control" id="notaod" name="notaod" placeholder="Nota:">
            </div>
        </div>

        <h5>OI</h5>
        <div class="form-row">
            
            <div class="form-group col-md-3">
                <label for="visionod">Esfera (ESF)</label>
                <select class="form-control  select2bs4" name="oi_esf" id="oi_esf" style="width: 100%;">
                </select>
                <!-- <input type="text" class="form-control" name="oi_esf" id="oi_esf" placeholder="Visión OD"> -->
            </div>
            <div class="form-group col-md-3">
                <label for="visionoi">Cilindro (CIL)</label>
                <select class="form-control  select2bs4" name="oi_cil" id="oi_cil" style="width: 100%;">
                </select>
                <!-- <input type="text" class="form-control" id="visionoi" name="visionoi" placeholder="Visión OI"> -->
            </div>
            <div class="form-group col-md-3">
                <label for="visionod">Eje</label>
                <input type="text" class="form-control" id="ejeoi" name="ejeoi" placeholder="Eje OI">
            </div>
            <div class="form-group col-md-3">
                <label for="visionoi">Dnp</label>
                <input type="text" class="form-control" id="dnpoi" name="dnpoi" placeholder="DNO OI">
            </div>
            <div class="form-group col-md-3">
                <label for="visionoi">Adición</label>
                <select class="form-control  select2bs4" name="oi_adicion" id="oi_adicion" style="width: 100%;">
                </select>
                <!-- <input type="text" class="form-control" id="visionoi" name="visionoi" placeholder="Visión OI"> -->
            </div>
            <div class="form-group col-md-9">
                <label for="visionoi">Nota:</label>
                <input type="text" class="form-control" id="notaoi" name="notaoi" placeholder="Nota:">
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="consulta-textarea">Descripción</label>
        <textarea id="consulta-textarea" name="consulta-textarea" class="form-control compose-textarea" style="height: 180px"></textarea>
    </div>

    <div class="form-group">
        <label for="formatoreceta">Preformato de receta</label>
        <select class="form-control select2bs4" id="formatoreceta" name="formatoreceta" style="width: 100%;">
            <option selected="selected">Seleccionar</option>
        </select>
    </div>

    <div class="form-group">
        <label for="receta-textarea">Receta</label>
        <textarea id="receta-textarea" name="receta-textarea" class="form-control compose-textarea" style="height: 180px"></textarea>
    </div>

    <div class="form-group">
        <label for="txtnota">Nota</label>
        <input type="text" class="form-control" id="txtnota" name="txtnota" placeholder="Nota">
    </div>

    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="proximaconsulta">Próxima consulta</label>
            <input type="date" class="form-control" id="proximaconsulta" name="proximaconsulta">
        </div>
        <div class="form-group col-md-3">
            <label for="whatsapptxt">Nro. WhatsApp</label>
            <input type="text" class="form-control" id="whatsapptxt" name="whatsapptxt" placeholder="595983222999">
        </div>
        <div class="form-group col-md-5">
            <label for="email">Email del Paciente</label>
            <input type="text" class="form-control" id="email" name="email" placeholder="jhondoe@gmail.com">
        </div>
    </div>

   

    <div class="form-group">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="gridCheck">
            <label class="form-check-label" for="gridCheck">
                Enviar informe
            </label>
        </div>
    </div>

    <input type="hidden" id="id_user" name="id_user" value="1">
    <input type="hidden" id="id_reserva" name="id_reserva" value="0">
    <button type="button" class="btn btn-primary" id="btnGuardarConsulta">Guardar</button>
</form>
