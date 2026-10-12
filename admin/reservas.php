<?php
/**
 * admin/reservas.php
 * 
 * Gestion y consulta de todas las reservas de Cine UNS para el Administrador.
 * Permite filtrar, revisar detalle de clientes, asientos, totales y cambiar el estado.
 */
require_once __DIR__ . '/../funciones.php';
requerirAdmin();

$reservaModel = new Reserva();

// Accion de cambiar estado de una reserva (confirmada / cancelada / finalizada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_estado'])) {
    csrf_validar();
    $reservaId = (int)($_POST['reserva_id'] ?? 0);
    $nuevoEstado = $_POST['nuevo_estado'] ?? '';

    if ($reservaId > 0 && in_array($nuevoEstado, ['confirmada', 'cancelada', 'finalizada'])) {
        $reservaModel->cambiarEstado($reservaId, $nuevoEstado);
        $_SESSION['mensaje_exito'] = "Estado de la reserva actualizado a: " . ucfirst($nuevoEstado);
    }
    header("Location: reservas.php");
    exit;
}

$exito = $_SESSION['mensaje_exito'] ?? "";
$error = $_SESSION['mensaje_error'] ?? "";
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);

$reservas = $reservaModel->obtenerTodas();

$tituloPagina = "Gestion de Reservas";
$paginaActiva = "reservas";

include __DIR__ . "/../includes/admin_header.php";
?>

<section class="seccion">
    <div class="titulo">
        <div>
            <p class="rojo">ADMINISTRACION</p>
            <h2>Listado General de Reservas</h2>
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
                    <th>Codigo</th>
                    <th>Cliente</th>
                    <th>Pelicula</th>
                    <th>Sala y Horario</th>
                    <th>Asientos</th>
                    <th>Entradas</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservas)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: #888;">No se han registrado reservas aun.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reservas as $r): ?>
                        <tr>
                            <td>
                                <strong style="font-family: monospace; background: #222; padding: 4px 6px; border-radius: 4px;">
                                    <?= e($r['codigo']) ?>
                                </strong>
                            </td>
                            <td>
                                <strong><?= e($r['usuario_nombre']) ?></strong><br>
                                <small style="color: #888;"><?= e($r['usuario_correo']) ?></small>
                            </td>
                            <td><?= e($r['pelicula_titulo']) ?></td>
                            <td>
                                <?= e($r['sala']) ?><br>
                                <small style="color: #aaa;"><?= e($r['fecha']) ?> <?= substr(e($r['hora']), 0, 5) ?></small>
                            </td>
                            <td>
                                <strong style="color: #e50914;"><?= e($r['asientos'] ?? '-') ?></strong>
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
                                <form method="POST" action="reservas.php" style="display: flex; gap: 5px; align-items: center;">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="cambiar_estado" value="1">
                                    <input type="hidden" name="reserva_id" value="<?= (int)$r['id'] ?>">
                                    <select name="nuevo_estado" style="width: auto; padding: 4px 6px; font-size: 12px; margin: 0; background: #222; color: #fff; border: 1px solid #444;">
                                        <option value="confirmada" <?= $r['estado'] === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                                        <option value="finalizada" <?= $r['estado'] === 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
                                        <option value="cancelada" <?= $r['estado'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                    </select>
                                    <button type="submit" class="btn-accion" style="background: #333; color: #fff; border: 1px solid #555; padding: 5px 8px;">
                                        OK
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . "/../includes/admin_footer.php"; ?>
