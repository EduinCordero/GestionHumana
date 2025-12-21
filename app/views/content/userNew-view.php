<form class="FormularioAjax" action="<?php echo APP_URL; ?>app/ajax/userAjax.php" method="POST" autocomplete="off" enctype="multipart/form-data">
            
            <input type="hidden" name="modulo_usuario" value="registrar">

            <div class="action-btn layout-top-spacing mb-7 d-flex align-items-center justify-content-between flex-wrap gap-6">
                <h5 class="mb-0 fs-5">Información principal</h5>
            </div>

            <div class="row mb-3">
                <div class="col-md">
                    <label for="exampleInputEmail1" class="form-label">Tipo Identificación <span style="color: red;">*</span></label>
                    <select id="disabledSelect" class="form-select" name="tipoidentificacion" required>
                        <option value=""></option>
                        <option value="CC">Cedula de Ciudadania</option>
                    </select>
                </div>
                <div class="col-md">
                    <label for="exampleInputEmail1" class="form-label">Identificación <span style="color: red;">*</span></label>
                    <input type="number" class="form-control" aria-describedby="emailHelp" name="identificacion" maxlength="15" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md">
                    <label for="exampleInputEmail1" class="form-label">Nombres <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" aria-describedby="emailHelp" name="nombres" maxlength="30" required>
                </div>
                <div class="col-md">
                    <label for="exampleInputEmail1" class="form-label">Apellidos <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" aria-describedby="emailHelp" name="apellidos" maxlength="40" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md">
                    <label for="exampleInputEmail1" class="form-label">Correo Electrónico <span style="color: red;">*</span></label>
                    <input type="email" class="form-control" aria-describedby="emailHelp" name="correo" required>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md">
                    <label for="exampleInputEmail1" class="form-label">Usuario <span style="color: red;">*</span></label>
                    <input type="text" class="form-control" aria-describedby="emailHelp" name="usuario" maxlength="15" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}" required>
                </div>
                <div class="col-md">
                    <label for="exampleInputEmail1" class="form-label">Password <span style="color: red;">*</span></label>
                    <input type="password" class="form-control" aria-describedby="emailHelp" name="password" required>
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" value="1" id="exampleCheck1" name="estado" checked>
                <label class="form-check-label" for="exampleCheck1">Registro activo</label>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">

        <div class="action-btn layout-top-spacing mb-7 d-flex align-items-center justify-content-between flex-wrap gap-6">
            <h5 class="mb-0 fs-5">Roles asignados</h5>
        </div>

        <div class="col-md-12">
            <label for="exampleInputEmail1" class="form-label">Sucursal <span style="color: red;">*</span></label>
            <select id="disabledSelect" class="form-select" name="tipoidentificacion" required>
                <option value=""></option>
                <option value="CC"></option>
            </select>
        </div>
        
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
</form>