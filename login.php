<?php
/**
 * login.php
 * 
 * Inicio de sesion de usuarios y administradores.
 * Valida credenciales con password_verify() y regenera la sesion para evitar fijacion de sesion.
 */
require_once "funciones.php";

// Si ya esta autenticado, redirigir segun su rol
if (estaAutenticado()) {
    if (esAdmin()) {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$error = "";
$exito = $_SESSION['mensaje_exito'] ?? "";
unset($_SESSION['mensaje_exito']);

if (isset($_GET['error']) && $_GET['error'] === 'acceso_denegado') {
    $error = "Acceso denegado: Se requieren permisos de administrador.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_validar();

    $correo = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($correo) || empty($password)) {
        $error = "Por favor ingrese su correo y contraseña.";
    } else {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->autenticar($correo, $password);

        if ($usuario) {
            session_regenerate_id(true);

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_correo'] = $usuario['correo'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            if ($usuario['rol'] === 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Credenciales incorrectas o usuario inactivo.";
        }
    }
}

$tituloPagina = "Iniciar Sesion";
include "includes/header.php";
?>

<div class="auth-container">
    <div class="auth-box">
        <p class="rojo">MI CUENTA</p>
        <h1>Iniciar Sesion</h1>
        <p class="subtitulo">Ingresa tus datos para gestionar tus reservas</p>

        <?php if (!empty($exito)): ?>
            <div class="alerta alerta-exito"><?= e($exito) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alerta alerta-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?php csrf_field(); ?>

            <label for="correo">Correo electronico</label>
            <input 
                type="email" 
                id="correo" 
                name="correo" 
                placeholder="ejemplo@correo.com" 
                required 
                value="<?= e($_POST['correo'] ?? '') ?>"
                style="width: 100%; padding: 13px; margin: 8px 0 18px; background: #222228; border: 1px solid #333; color: white; border-radius: 5px;"
            >

            <label for="password">Contraseña</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="Tu contraseña" 
                required
                style="width: 100%; padding: 13px; margin: 8px 0 25px; background: #222228; border: 1px solid #333; color: white; border-radius: 5px;"
            >

            <button type="submit" class="boton" style="width: 100%;">Ingresar</button>
        </form>

        <div class="auth-footer">
            ¿Aun no tienes una cuenta? <a href="registro.php">Registrate aqui</a>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
