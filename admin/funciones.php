<?php
/**
 * admin/funciones.php
 * 
 * Gestion de funciones y horarios de proyeccion en el Panel Administrativo
 * Permite al administrador crear nuevos horarios para peliculas,
 * definir sala, fecha, hora y capacidad de la sala
 */
require_once __DIR__ . '/../funciones.php';
requerirAdmin();

$funcionModel = new Funcion();
$peliculaModel = new Pelicula();

$peliculas = $peliculaModel->obtenerTodas();

// Procesar formulario de nueva funcion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_funcion'])) {
    csrf_validar();

    $peliculaId = (int)($_POST['pelicula_id'] ?? 0);
    $sala       = trim($_POST['sala'] ?? 'Sala 1');
    $fecha      = trim($_POST['fecha'] ?? '');
    $hora       = trim($_POST['hora'] ?? '');
    $capacidad  = (int)($_POST['capacidad'] ?? 30);
    $estado     = $_POST['estado'] ?? 'disponible';

    if ($peliculaId > 0 && !empty($fecha) && !empty($hora)) {
        $ok = $funcionModel->crear([
            'pelicula_id' => $peliculaId,
            'sala'        => $sala,
            'fecha'       => $fecha,
            'hora'        => $hora,
            'capacidad'   => $capacidad,
            'estado'      => $estado
        ]);

        if ($ok) {
            $_SESSION['mensaje_exito'] = "Nueva funcion agregada con exito.";
        } else {
            $_SESSION['mensaje_error'] = "Error al registrar la funcion.";
        }
    } else {
        $_SESSION['mensaje_error'] = "Por favor complete todos los campos de la funcion.";
    }

    header("Location: funciones.php");
    exit;
}

// Procesar cambio de estado o eliminacion
if (isset($_GET['accion']) && isset($_GET['id'])) {
    $token = $_GET['token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $idAccion = (int)$_GET['id'];
        if ($_GET['accion'] === 'disponible' || $_GET['accion'] === 'cancelada') {
            $funcionModel->cambiarEstado($idAccion, $_GET['accion']);
            $_SESSION['mensaje_exito'] = "Estado de funcion actualizado.";
        } elseif ($_GET['accion'] === 'eliminar') {
            $funcionModel->eliminar($idAccion);
            $_SESSION['mensaje_exito'] = "Funcion eliminada o cancelada.";
        }
    }
    header("Location: funciones.php");
    exit;
}

$exito = $_SESSION['mensaje_exito'] ?? "";
$error = $_SESSION['mensaje_error'] ?? "";
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);

$funciones = $funcionModel->obtenerTodas();

$tituloPagina = "Gestion de Funciones";
$paginaActiva = "funciones";

include __DIR__ . "/../includes/admin_header.php";
?>

<section class="seccion">
    <div class="titulo">
        <div>
            <p class="rojo">CARTELERA Y HORARIOS</p>
            <h2>Gestion de Funciones</h2>
        </div>
    </div>

    <?php if (!empty($exito)): ?>
        <div class="alerta alerta-exito"><?= e($exito) ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alerta alerta-error"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- Formulario para agregar una nueva funcion -->
    <div class="formulario" style="margin-bottom: 40px;">
        <h3>Programar Nueva Funcion</h3>
        <form method="POST" action="funciones.php" style="margin-top: 15px;">
            <?php csrf_field(); ?>
            <input type="hidden" name="crear_funcion" value="1">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <label>Pelicula:</label>
                    <select name="pelicula_id" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($peliculas as $p): ?>
                            <option value="<?= (int)$p['id'] ?>"><?= e($p['titulo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Sala:</label>
                    <input type="text" name="sala" value="Sala 1" required placeholder="Ej. Sala 1, Sala 2">
                </div>
                <div>
                    <label>Fecha:</label>
                    <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div>
                    <label>Hora:</label>
                    <input type="time" name="hora" value="18:00" required>
                </div>
                <div>
                    <label>Capacidad:</label>
                    <input type="number" name="capacidad" value="30" min="1" max="100" required>
                </div>
            </div>

            <button type="submit" class="boton" style="margin-top: 15px; width: auto; padding: 12px 30px;">
                + Agregar Horario
            </button>
        </form>
    </div>

    <!-- Listado de Funciones -->
    <div class="tabla-responsive">
        <table class="tabla">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pelicula</th>
                    <th>Sala</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Capacidad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($funciones)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #888;">No hay funciones registradas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($funciones as $f): ?>
                        <tr>
                            <td><?= (int)$f['id'] ?></td>
                            <td><strong><?= e($f['pelicula_titulo']) ?></strong></td>
                            <td><?= e($f['sala']) ?></td>
                            <td><?= e($f['fecha']) ?></td>
                            <td><?= substr(e($f['hora']), 0, 5) ?></td>
                            <td><?= (int)$f['capacidad'] ?></td>
                            <td>
                                <span class="badge badge-<?= e($f['estado']) ?>">
                                    <?= ucfirst(e($f['estado'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($f['estado'] === 'disponible'): ?>
                                    <a href="funciones.php?accion=cancelada&id=<?= (int)$f['id'] ?>&token=<?= csrf_token() ?>" class="btn-accion btn-accion-estado">Cancelar</a>
                                <?php else: ?>
                                    <a href="funciones.php?accion=disponible&id=<?= (int)$f['id'] ?>&token=<?= csrf_token() ?>" class="btn-accion" style="background:#183e20; color:#4ade80;">Habilitar</a>
                                <?php endif; ?>
                                <a href="funciones.php?accion=eliminar&id=<?= (int)$f['id'] ?>&token=<?= csrf_token() ?>" class="btn-accion" style="background:#222; color:#888;" onclick="return confirm('¿Desea eliminar este horario?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . "/../includes/admin_footer.php"; ?>
