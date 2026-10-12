<?php
/**
 * includes/admin_header.php
 * 
 * Cabecera y menu de navegacion del Panel Administrativo.
 * Variables esperadas opcionales:
 * - $tituloPagina (string): Titulo especifico de la seccion admin
 * - $paginaActiva (string): 'dashboard', 'peliculas', 'funciones', 'reservas'
 */
if (!isset($usuario)) {
    $usuario = usuarioActual();
}
$tituloDoc = isset($tituloPagina) ? e($tituloPagina) . " - Admin Cine UNS" : "Panel de Administracion - Cine UNS";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloDoc ?></title>
    <!-- CSS organizado en assets/css/ -->
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>

<header>
    <div class="logo">
        <a href="index.php" style="color: inherit; text-decoration: none;">CINE<span>UNS</span> <small style="font-size: 13px; color: #aaa;">| ADMIN</small></a>
    </div>
    <nav>
        <a href="index.php" <?= (isset($paginaActiva) && $paginaActiva === 'dashboard') ? 'style="color:white; font-weight:bold;"' : '' ?>>Dashboard</a>
        <a href="peliculas.php" <?= (isset($paginaActiva) && $paginaActiva === 'peliculas') ? 'style="color:white; font-weight:bold;"' : '' ?>>Peliculas</a>
        <a href="funciones.php" <?= (isset($paginaActiva) && $paginaActiva === 'funciones') ? 'style="color:white; font-weight:bold;"' : '' ?>>Funciones</a>
        <a href="reservas.php" <?= (isset($paginaActiva) && $paginaActiva === 'reservas') ? 'style="color:white; font-weight:bold;"' : '' ?>>Reservas</a>
        <a href="../index.php" target="_blank" style="color: #4ade80;">Ver Sitio Publico</a>
        <a href="../logout.php" class="btn-nav-outline">Salir</a>
    </nav>
</header>
