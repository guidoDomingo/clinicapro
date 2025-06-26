<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
        <img src="view/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
            style="opacity: .8">
        <span class="brand-text font-weight-light">MiClinica</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="view/uploads/profile/<?php echo isset($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : 'user2-160x160.jpg'; ?>" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#"
                    class="d-block"><?php echo isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Usuario'; ?></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
             with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="index.php?ruta=home" class="nav-link">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Inicio</p>
                    </a>
                </li>
                <li class="nav-item <?php echo mostrar_si_tiene_permiso('ver_consultas'); ?>">
                    <a href="index.php?ruta=consultas" class="nav-link">
                        <i class="nav-icon fas fa-stethoscope"></i>
                        <p>Consultas</p>
                    </a>
                </li>
                <li class="nav-item <?php echo mostrar_si_tiene_permiso('ver_agenda'); ?>">
                    <a href="index.php?ruta=citas" class="nav-link">
                        <i class="nav-icon fas fa-calendar-alt"></i>
                        <p>Sala de espera</p>
                    </a>
                </li>
                <!-- <li class="nav-item">
                    <a href="index.php?ruta=personas" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>Personas</p>
                    </a>
                </li> -->
                <li class="nav-item <?php echo mostrar_si_tiene_permiso('ver_pacientes'); ?>">
                    <a href="index.php?ruta=rhpersonas" class="nav-link">
                        <i class="nav-icon fas fa-user-md"></i>
                        <p>Personas</p>
                    </a>
                </li>
                <li class="nav-item <?php echo mostrar_si_tiene_permiso('ver_preformatos'); ?>">
                    <a href="index.php?ruta=preformatos" class="nav-link">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>Preformatos</p>
                    </a>
                </li>
                <li class="nav-item <?php echo mostrar_si_tiene_permiso('ver_agenda'); ?>">
                    <a href="index.php?ruta=agendas" class="nav-link">
                        <i class="nav-icon fas fa-calendar-check"></i>
                        <p>Agendas</p>
                    </a>
                </li>
                <li class="nav-item <?php echo mostrar_si_tiene_permiso('ver_servicios'); ?>">
                    <a href="index.php?ruta=servicios" class="nav-link">
                        <i class="nav-icon fas fa-notes-medical"></i>
                        <p>Servicios</p>
                    </a>
                </li>

                <li class="nav-header">CONFIGURACIÓN</li>


                <li class="nav-item has-treeview <?php echo mostrar_si_tiene_permiso('administrar_roles'); ?>">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-list"></i>
                        <p>
                            Referenciales
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>                    <ul class="nav nav-treeview">
                        <li style="padding-left: 20px;" class="nav-item <?php echo mostrar_si_tiene_permiso('administrar_servicios'); ?>">
                            <a href="index.php?ruta=rs_servicios" class="nav-link">
                                <i class="nav-icon fas fa-briefcase-medical"></i>
                                <p>Gestión de Servicios</p>
                            </a>
                        </li>                        <li style="padding-left: 20px;" class="nav-item <?php echo mostrar_si_tiene_permiso('administrar_profesiones'); ?>">
                            <a href="index.php?ruta=profesiones" class="nav-link">
                                <i class="nav-icon fas fa-user-md"></i>
                                <p>Gestión de Profesiones</p>
                            </a>
                        </li>                        <li style="padding-left: 20px;" class="nav-item <?php echo mostrar_si_tiene_permiso('administrar_especialidades'); ?>">
                            <a href="index.php?ruta=especialidades" class="nav-link">
                                <i class="nav-icon fas fa-stethoscope"></i>
                                <p>Gestión de Especialidades</p>
                            </a>
                        </li>
                        <li style="padding-left: 20px;" class="nav-item <?php echo mostrar_si_tiene_permiso('administrar_motivos'); ?>">
                            <a href="index.php?ruta=motivos" class="nav-link">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>Motivos Comunes</p>
                            </a>
                        </li>
                        <li style="padding-left: 20px;" class="nav-item <?php echo mostrar_si_tiene_permiso('administrar_empresas'); ?>">
                            <a href="index.php?ruta=empresas" class="nav-link">
                                <i class="nav-icon fas fa-building"></i>
                                <p>Gestión de Empresas</p>
                            </a>
                        </li>
                        <li style="padding-left: 20px;" class="nav-item <?php echo mostrar_si_tiene_permiso('administrar_tipos_proveedores'); ?>">
                            <a href="index.php?ruta=tipos_proveedores" class="nav-link">
                                <i class="nav-icon fas fa-tags"></i>
                                <p>Tipos de Proveedores</p>
                            </a>
                        </li>
                        <li class="nav-item" style="padding-left: 20px;">
                            <a href="index.php?ruta=referencial2" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Referencial 2</p>
                            </a>
                        </li>
                        <li class="nav-item" style="padding-left: 20px;">
                            <a href="index.php?ruta=referencial3" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Referencial 3</p>
                            </a>
                        </li>
                    </ul>
                </li>





                <li class="nav-item <?php echo mostrar_si_tiene_permiso('administrar_roles'); ?>">
                    <a href="index.php?ruta=roles" class="nav-link">
                        <i class="nav-icon fas fa-user-tag"></i>
                        <p>Roles y Permisos</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?ruta=logout" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Cerrar Sesión</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>