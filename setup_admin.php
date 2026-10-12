<?php
/**
 * setup_admin.php
 * 
 * Script auxiliar de administracion inicial.
 * Permite configurar o restablecer de manera segura las credenciales
 * del primer usuario Administrador directamente en la base de datos cine_db.
 * 
 * Puede ejecutarse desde el navegador (http://localhost/cine/setup_admin.php)
 * o desde la consola: php setup_admin.php
 */
require_once __DIR__ . '/config/Conexion.php';
require_once __DIR__ . '/clases/Usuario.php';

$mensaje = "";
$tipoAlerta = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? 'Administrador del Cine');
    $correo   = trim($_POST['correo'] ?? 'admin@cine.com');
    $password = $_POST['password'] ?? '';

    if (empty($correo) || empty($password)) {
        $mensaje = "Debe proporcionar un correo y una contraseña.";
        $tipoAlerta = "error";
    } else {
        $usuarioModel = new Usuario();
        $ok = $usuarioModel->crearOActualizarAdmin($nombre, $correo, $password, '987654321');

        if ($ok) {
            $mensaje = "¡Administrador configurado correctamente! Correo: {$correo}. Ya puede iniciar sesion.";
            $tipoAlerta = "exito";
        } else {
            $mensaje = "Error al configurar el administrador.";
            $tipoAlerta = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuracion de Administrador - Cine UNS</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php" style="color: inherit; text-decoration: none;">CINE<span>UNS</span></a>
    </div>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="login.php">Iniciar Sesion</a>
    </nav>
</header>

<div class="auth-container">
    <div class="auth-box">
        <p class="rojo">CONFIGURACION INICIAL</p>
        <h1>Crear o Actualizar Administrador</h1>
        <p class="subtitulo">Establece la cuenta del primer administrador del sistema de forma segura.</p>

        <?php if (!empty($mensaje)): ?>
            <div class="alerta alerta-<?= $tipoAlerta ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
            <?php if ($tipoAlerta === 'exito'): ?>
                <div style="margin-bottom: 20px;">
                    <a href="login.php" class="boton" style="display: block; text-align: center;">Ir a Iniciar Sesion</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST" action="setup_admin.php">
            <label for="nombre">Nombre del Administrador</label>
            <input 
                type="text" 
                id="nombre" 
                name="nombre" 
                value="Administrador del Cine"
                required
                style="width: 100%; padding: 13px; margin: 8px 0 18px; background: #222228; border: 1px solid #333; color: white; border-radius: 5px;"
            >

            <label for="correo">Correo electronico del Administrador</label>
            <input 
                type="email" 
                id="correo" 
                name="correo" 
                value="admin@cine.com"
                required
                style="width: 100%; padding: 13px; margin: 8px 0 18px; background: #222228; border: 1px solid #333; color: white; border-radius: 5px;"
            >

            <label for="password">Nueva contraseña para el Administrador</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="Escribe la contraseña deseada"
                required
                style="width: 100%; padding: 13px; margin: 8px 0 25px; background: #222228; border: 1px solid #333; color: white; border-radius: 5px;"
            >

            <button type="submit" class="boton" style="width: 100%;">Guardar Administrador</button>
        </form>
    </div>
</div>

<footer>
    CINE UNS
</footer>
</body>
</html>