<?php
/**
 * includes/header.php
 * 
 * Cabecera y barra de navegacion compartida para el sitio publico de Cine UNS.
 * Variables esperadas opcionales:
 * - $tituloPagina (string): Titulo especifico de la pagina en el <title>
 * - $paginaActiva (string): 'inicio', 'cartelera', 'contacto', 'reservas', etc.
 */
if (!isset($usuario)) {
    $usuario = usuarioActual();
}
$tituloDoc = isset($tituloPagina) ? e($tituloPagina) . " - Cine UNS" : "Cine UNS";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloDoc ?></title>
    <!-- CSS organizado en assets/css/ -->
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php" style="color: inherit; text-decoration: none;">CINE<span>UNS</span></a>
    </div>
    <nav>
        <a href="index.php" <?= (isset($paginaActiva) && $paginaActiva === 'inicio') ? 'style="color:white; font-weight:bold;"' : '' ?>>Inicio</a>
        <a href="index.php#cartelera">Cartelera</a>
        <a href="contact.php" <?= (isset($paginaActiva) && $paginaActiva === 'contacto') ? 'style="color:white; font-weight:bold;"' : '' ?>>Contacto</a>
        
        <?php if ($usuario): ?>
            <?php if ($usuario['rol'] === 'admin'): ?>
                <a href="admin/index.php" class="btn-nav">Panel Admin</a>
            <?php else: ?>
                <a href="mis_reservas.php" <?= (isset($paginaActiva) && $paginaActiva === 'reservas') ? 'style="color:white; font-weight:bold;"' : '' ?>>Mis reservas</a>
            <?php endif; ?>
            <a href="logout.php" class="btn-nav-outline">Cerrar sesion (<?= e($usuario['nombre']) ?>)</a>
        <?php else: ?>
            <a href="login.php" class="btn-nav">Iniciar sesion</a>
            <a href="registro.php" class="btn-nav-outline">Registrarse</a>
        <?php endif; ?>
    </nav>
</header>
