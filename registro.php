<?php
/**
 * registro.php
 * 
 * Formulario de registro para nuevos clientes.
 * Asigna automaticamente y de forma obligatoria el rol 'cliente'.
 * Nunca permite asignar rol 'admin' desde este formulario publico.
 */
require_once "funciones.php";

if (estaAutenticado()) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_validar();

    $nombre   = trim($_POST["nombre"] ?? "");
    $correo   = trim($_POST["correo"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm  = $_POST["password_confirm"] ?? "";
    $telefono = trim($_POST["telefono"] ?? "");

    if (empty($nombre) || empty($correo) || empty($password)) {
        $error = "Por favor complete todos los campos obligatorios.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electronico ingresado no es valido.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($password !== $confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $usuarioModel = new Usuario();

        if ($usuarioModel->existeCorreo($correo)) {
            $error = "El correo electronico ya esta registrado. Intente iniciar sesion.";
        } else {
            $nuevoId = $usuarioModel->registrar($nombre, $correo, $password, $telefono);

            if ($nuevoId) {
                $_SESSION['mensaje_exito'] = "¡Registro exitoso! Ya puedes iniciar sesion.";
                header("Location: login.php");
                exit;
            } else {
                $error = "Ocurrio un error al registrar el usuario. Por favor intente mas tarde.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Cine UNS</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php" style="color: inherit; text-decoration: none;">CINE<span>UNS</span></a>
    </div>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="index.php#cartelera">Cartelera</a>
        <a href="contact.php">Contacto</a>
    </nav>
</header>

<div class="auth-container">
    <div class="auth-box">
        <p class="rojo">REGISTRO</p>
        <h1>Crear Cuenta</h1>
        <p class="subtitulo">Unete para reservar tus peliculas favoritas en Cine UNS</p>

        <?php if (!empty($error)): ?>
            <div class="alerta alerta-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="registro.php">
            <?php csrf_field(); ?>

            <label for="nombre">Nombre completo</label>
            <input 
                type="text" 
                id="nombre" 
                name="nombre" 
                placeholder="Ej. Juan Perez" 
                required 
                value="<?= e($_POST['nombre'] ?? '') ?>"
                style="width: 100%; padding: 13px; margin: 8px 0 18px; background: #222228; border: 1px solid #333; color: white; border-radius: 5px;"
            >

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

            <label for="telefono">Telefono (opcional)</label>
            <input 
                type="text" 
                id="telefono" 
                name="telefono" 
                placeholder="Ej. 987654321" 
                value="<?= e($_POST['telefono'] ?? '') ?>"
                style="width: 100%; padding: 13px; margin: 8px 0 18px; background: #222228; border: 1px solid #333; color: white; border-radius: 5px;"
            >

            <label for="password">Contraseña (minimo 6 caracteres)</label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                placeholder="Crea una contraseña segura" 
                required
                style="width: 100%; padding: 13px; margin: 8px 0 18px; background: #222228; border: 1px solid #333; color: white; border-radius: 5px;"
            >

            <label for="password_confirm">Confirmar contraseña</label>
            <input 
                type="password" 
                id="password_confirm" 
                name="password_confirm" 
                placeholder="Repite tu contraseña" 
                required
                style="width: 100%; padding: 13px; margin: 8px 0 25px; background: #222228; border: 1px solid #333; color: white; border-radius: 5px;"
            >

            <button type="submit" class="boton" style="width: 100%;">Registrarme</button>
        </form>

        <div class="auth-footer">
            ¿Ya tienes una cuenta? <a href="login.php">Inicia sesion aqui</a>
        </div>
    </div>
</div>

<footer>
    CINE UNS
</footer>
</body>
</html>