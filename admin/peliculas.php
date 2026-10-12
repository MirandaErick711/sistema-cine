<?php
/**
 * admin/peliculas.php
 * 
 * Gestion y listado de peliculas para el Administrador
 * Permite registrar, editar, cambiar estado (activa/inactiva) o retirar peliculas
 */
require_once __DIR__ . '/../funciones.php';
requerirAdmin();

$usuario = usuarioActual();
$peliculaModel = new Pelicula();

// Mensajes de accion flash
$exito = $_SESSION['mensaje_exito'] ?? "";
$error = $_SESSION['mensaje_error'] ?? "";
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);

// Listado de todas las peliculas
$peliculas = $peliculaModel->obtenerTodas();

$tituloPagina = "Gestion de Peliculas";
$paginaActiva = "peliculas";

include __DIR__ . "/../includes/admin_header.php";
?>

<section class="seccion">
    <div class="titulo">
        <div>
            <p class="rojo">CATALOGO</p>
            <h2>Gestion de Peliculas</h2>
        </div>
        <div>
            <a href="pelicula_form.php" class="boton">+ Agregar Pelicula</a>
        </div>
    </div>

    <?php if (!empty($exito)): ?>
        <div class="alerta alerta-exito"><?= e($exito) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alerta alerta-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="tabla-responsive">
        <table class="tabla">
            <thead>
                <tr>
                    <th>Poster</th>
                    <th>Titulo</th>
                    <th>Genero</th>
                    <th>Duracion</th>
                    <th>Precio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($peliculas)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #888;">No hay peliculas registradas</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($peliculas as $p): ?>
                        <tr>
                            <td>
                                <img src="../<?= e($p['imagen']) ?>" alt="<?= e($p['titulo']) ?>" style="width: 50px; height: 70px; object-fit: cover; border-radius: 4px;">
                            </td>
                            <td>
                                <strong><?= e($p['titulo']) ?></strong>
                            </td>
                            <td><?= e($p['genero']) ?></td>
                            <td><?= e($p['duracion']) ?></td>
                            <td><strong>S/ <?= number_format($p['precio'], 2) ?></strong></td>
                            <td>
                                <span class="badge badge-<?= e($p['estado']) ?>">
                                    <?= ucfirst(e($p['estado'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="pelicula_form.php?id=<?= (int)$p['id'] ?>" class="btn-accion btn-accion-editar">
                                    Editar
                                </a>

                                <?php if ($p['estado'] === 'activa'): ?>
                                    <a href="eliminar_pelicula.php?id=<?= (int)$p['id'] ?>&accion=desactivar&token=<?= csrf_token() ?>" 
                                       class="btn-accion btn-accion-estado"
                                       onclick="return confirm('¿Desea retirar esta pelicula de la cartelera? No se eliminara el historial de reservas.');">
                                        Desactivar
                                    </a>
                                <?php else: ?>
                                    <a href="eliminar_pelicula.php?id=<?= (int)$p['id'] ?>&accion=activar&token=<?= csrf_token() ?>" 
                                       class="btn-accion" style="background:#183e20; color:#4ade80; border:1px solid #22c55e;">
                                        Activar
                                    </a>
                                <?php endif; ?>

                                <a href="eliminar_pelicula.php?id=<?= (int)$p['id'] ?>&accion=eliminar&token=<?= csrf_token() ?>" 
                                   class="btn-accion" style="background:#222; color:#888; border:1px solid #333;"
                                   onclick="return confirm('¿Desea intentar eliminar el registro? Si tiene reservas asociadas se mantendra como inactiva por seguridad.');">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . "/../includes/admin_footer.php"; ?>
