<div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="start" class="h2"><b>Centro</b>Oftalmológico S.A.</a>
            <div class="logo-container">
                <img src="view/img/logo.png" width="100" height="100" alt="Centro oftalmológico S.A.">
            </div>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Iniciar sesión</p>

            <form action="" method="post">
                <div class="input-group mb-3">
                    <input type="tel" class="form-control" name="usuario" placeholder="Usuario" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">


                    <!-- /.col -->
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                    </div>
                    <!-- /.col -->
                </div>
                <?php 
           $login = new ControllerUser();
           $login -> ctrLoginUser();
         ?>
            </form>
        </div>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
</div>