<!DOCTYPE html>
<html>
<head>
    <title>Login de Prueba - Clínica</title>
    <meta charset="utf-8">
</head>
<body>
    <h2>Login de Prueba para Debugging</h2>
    
    <?php
    session_start();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
        // Simular login exitoso
        $_SESSION["iniciarSesion"] = "ok";
        $_SESSION["validarSesion"] = "ok";
        $_SESSION["user_id"] = 1;
        $_SESSION["nombre"] = "Admin Test";
        $_SESSION["usuario"] = "admin";
        $_SESSION["rol"] = "admin";
        $_SESSION["roles"] = ["admin"];
        $_SESSION["email"] = "admin@test.com";
        $_SESSION["profile_complete"] = true;
        
        echo "<div style='color: green; margin: 10px 0;'>✅ Sesión establecida correctamente como admin</div>";
        echo "<p><strong>ID de Sesión:</strong> " . session_id() . "</p>";
        echo "<p><a href='index.php?ruta=consultas&form_type=general&id_consulta=27&skip_modal=1' target='_blank'>
              🔗 Probar URL problemática</a></p>";
        echo "<p><a href='index.php?ruta=consultas' target='_blank'>
              🔗 Ir a consultas normal</a></p>";
        echo "<p><a href='index.php?ruta=home' target='_blank'>
              🔗 Ir al home</a></p>";
    } else {
        ?>
        <form method="POST">
            <p>Para hacer debugging, haz clic en el botón para establecer una sesión de admin:</p>
            <button type="submit" name="login" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px;">
                🔑 Establecer Sesión de Admin
            </button>
        </form>
        <?php
    }
    
    if (!empty($_SESSION)) {
        echo "<h3>Estado actual de la sesión:</h3>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
    }
    ?>
    
</body>
</html>
