<?php
/**
 * mis_reservas.php
 * 
 * Historial de reservas privado para el cliente autenticado.
 * Solo muestra las reservas pertenecientes a su usuario_id.
 * Impide que un usuario acceda a las reservas de otra persona.
 */
require_once "funciones.php";

// Requerir que el usuario este conectado
requerirAutenticacion();
$usuario = usuarioActual();

$reservaModel = new Reserva();
$reservas = $reservaModel->obtenerPorUsuario($usuario['id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas - Cine UNS</title>
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
        <a href="mis_reservas.php" style="color: white; font-weight: bold;">Mis reservas</a>
        <a href="contact.php">Contacto</a>
        <a href="logout.php" class="btn-nav-outline">Cerrar sesion</a>
    </nav>
</header>

<section class="seccion" style="min-height: 500px;">
    <div class="titulo">
        <div>
            <p class="rojo">MI HISTORIAL</p>
            <h2>Mis Reservas</h2>
        </div>
        <div>
            <a href="index.php#cartelera" class="boton">+ Nueva Reserva</a>
        </div>
    </div>

    <?php if (empty($reservas)): ?>
        <div class="mensaje">
            <h3>Aun no tienes reservas registradas</h3>
            <p>Explora nuestra cartelera y reserva tus entradas para las mejores peliculas.</p>
            <a href="index.php#cartelera" class="boton" style="margin-top: 10px; display: inline-block;">Ver cartelera</a>
        </div>
    <?php else: ?>
        <div class="tabla-responsive">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Pelicula</th>
                        <th>Sala</th>
                        <th>Fecha y Hora</th>
                        <th>Asientos</th>
                        <th>Entradas</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $r): ?>
                        <tr>
                            <td>
                                <strong style="color: #fff; font-family: monospace; background: #222; padding: 4px 6px; border-radius: 4px;">
                                    <?= e($r['codigo']) ?>
                                </strong>
                            </td>
                            <td>
                                <strong><?= e($r['pelicula_titulo']) ?></strong>
                            </td>
                            <td><?= e($r['sala']) ?></td>
                            <td>
                                <?= e($r['fecha']) ?><br>
                                <small style="color: #aaa;"><?= substr(e($r['hora']), 0, 5) ?></small>
                            </td>
                            <td>
                                <span style="color: #e50914; font-weight: bold;"><?= e($r['asientos'] ?? '-') ?></span>
                            </td>
                            <td>
                                <?= (int)$r['cantidad_entradas'] ?> 
                                <small style="color: #888;">(<?= e($r['tipo_entrada']) ?>)</small>
                            </td>
                            <td>
                                <strong style="color: #4ade80;">S/ <?= number_format($r['total'], 2) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-<?= e($r['estado']) ?>">
                                    <?= ucfirst(e($r['estado'])) ?>
                                </span>
                            </td>
                            <td>
                                <small style="color: #888;"><?= e($r['creado_en']) ?></small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<footer>
    CINE UNS
</footer>
</body>
</html>