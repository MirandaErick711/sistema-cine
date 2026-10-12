<?php
/**
 * admin/index.php
 * 
 * Dashboard principal del Panel Administrativo de Cine UNS
 * Resumen general de peliculas, funciones, reservas e ingresos
 * Protegido en el servidor requiriendo rol 'admin'
 */
require_once __DIR__ . '/../funciones.php';
requerirAdmin();

$usuario = usuarioActual();

$peliculaModel = new Pelicula();
$funcionModel  = new Funcion();
$reservaModel  = new Reserva();

$totalPeliculasActivas = $peliculaModel->contarActivas();
$totalReservas         = $reservaModel->contarTotal();
$totalRecaudado        = $reservaModel->totalRecaudado();
$ultimasReservas       = array_slice($reservaModel->obtenerTodas(), 0, 5);

$tituloPagina = "Dashboard";
$paginaActiva = "dashboard";

include __DIR__ . "/../includes/admin_header.php";
?>

<section class="seccion">
    <div class="titulo">
        <div>
            <p class="rojo">PANEL DE CONTROL</p>
            <h2>Resumen del Sistema</h2>
        </div>
        <div>
            <a href="pelicula_form.php" class="boton">+ Nueva Pelicula</a>
        </div>
    </div>

    <!-- Metricas Clave -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Peliculas Activas</h3>
            <p class="numero"><?= (int)$totalPeliculasActivas ?></p>
        </div>
        <div class="stat-card">
            <h3>Total de Reservas</h3>
            <p class="numero"><?= (int)$totalReservas ?></p>
        </div>
        <div class="stat-card">
            <h3>Recaudacion Confirmada</h3>
            <p class="numero">S/ <?= number_format($totalRecaudado, 2) ?></p>
        </div>
    </div>

    <!-- Ultimas Reservas -->
    <div class="titulo" style="margin-top: 40px; margin-bottom: 20px;">
        <h3>Ultimas Reservas Registradas</h3>
        <a href="reservas.php" class="btn-secundario">Ver Todas</a>
    </div>

    <?php if (empty($ultimasReservas)): ?>
        <p style="color: #888;">No hay reservas registradas en el sistema aun.</p>
    <?php else: ?>
        <div class="tabla-responsive">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Cliente</th>
                        <th>Pelicula</th>
                        <th>Horario</th>
                        <th>Asientos</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimasReservas as $r): ?>
                        <tr>
                            <td><strong><?= e($r['codigo']) ?></strong></td>
                            <td><?= e($r['usuario_nombre']) ?></td>
                            <td><?= e($r['pelicula_titulo']) ?></td>
                            <td><?= e($r['fecha']) ?> <?= substr(e($r['hora']), 0, 5) ?></td>
                            <td><span style="color: #e50914;"><?= e($r['asientos'] ?? '-') ?></span></td>
                            <td><strong>S/ <?= number_format($r['total'], 2) ?></strong></td>
                            <td><span class="badge badge-<?= e($r['estado']) ?>"><?= ucfirst(e($r['estado'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . "/../includes/admin_footer.php"; ?>
