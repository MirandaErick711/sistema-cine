<?php
/**
 * compra.php
 * 
 * Flujo de reserva / compra de entradas para el cliente.
 * - Requiere inicio de sesion (control de acceso seguro).
 * - Muestra informacion de la pelicula y sus funciones disponibles.
 * - Permite elegir la funcion deseada.
 * - Muestra el mapa de asientos disponibles y ocupados en tiempo real.
 * - Evita colisiones de asientos mediante transacciones y bloqueos de BD.
 * - Calcula el total en el servidor (descuento estudiante 20%).
 * - Genera el codigo unico de reserva y la guarda en la base de datos.
 * - Logica JavaScript externa en assets/js/reserva.js.
 */
require_once "funciones.php";

// 1. Control de acceso: La reserva requiere usuario logueado
requerirAutenticacion();
$usuario = usuarioActual();

// 2. Obtener pelicula
$peliculaId = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$peliculaModel = new Pelicula();
$pelicula = $peliculaModel->obtenerPorId($peliculaId);

if (!$pelicula || $pelicula['estado'] !== 'activa') {
    die("Pelicula no disponible o no encontrada. <a href='index.php'>Volver al inicio</a>");
}

// 3. Obtener funciones de esta pelicula
$funcionModel = new Funcion();
$funciones = $funcionModel->obtenerPorPelicula($peliculaId);

// Seleccionar funcion por defecto o segun parametro
$funcionIdSeleccionada = isset($_GET["funcion_id"]) ? (int)$_GET["funcion_id"] : 0;
if ($funcionIdSeleccionada <= 0 && !empty($funciones)) {
    $funcionIdSeleccionada = (int)$funciones[0]['id'];
}

// Obtener datos de la funcion actual
$funcionActual = null;
if ($funcionIdSeleccionada > 0) {
    $funcionActual = $funcionModel->obtenerPorId($funcionIdSeleccionada);
}

// Obtener asientos ocupados para esta funcion
$asientosOcupados = [];
if ($funcionActual) {
    $asientosOcupados = $funcionModel->obtenerAsientosOcupados($funcionIdSeleccionada);
}

// Generar lista fija de 18 asientos de sala (Filas A, B, C con columnas 1 a 6)
$listaAsientos = [
    'A1', 'A2', 'A3', 'A4', 'A5', 'A6',
    'B1', 'B2', 'B3', 'B4', 'B5', 'B6',
    'C1', 'C2', 'C3', 'C4', 'C5', 'C6'
];

$mensajeExito = "";
$mensajeError = "";
$reservaCreada = null;

// 4. Procesamiento del POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_validar();

    $funcionIdPost = (int)($_POST["funcion_id"] ?? 0);
    $tipoEntrada   = $_POST["tipo"] ?? "normal";
    $asientosPost  = $_POST["asientos"] ?? [];

    if ($funcionIdPost <= 0) {
        $mensajeError = "Por favor seleccione una funcion valida.";
    } elseif (empty($asientosPost) || !is_array($asientosPost)) {
        $mensajeError = "Debe seleccionar al menos un asiento en el mapa de la sala.";
    } else {
        // Ejecutar transaccion segura de reserva
        $reservaModel = new Reserva();
        $resultado = $reservaModel->crearReserva(
            $usuario['id'],
            $funcionIdPost,
            $asientosPost,
            $tipoEntrada
        );

        if ($resultado['exito']) {
            $mensajeExito = $resultado['mensaje'];
            $reservaCreada = $reservaModel->obtenerPorId($resultado['reserva_id'], $usuario['id']);
        } else {
            $mensajeError = $resultado['mensaje'];
        }
    }
}

$tituloPagina = "Reservar Entrada - " . $pelicula["titulo"];
$paginaActiva = "cartelera";

include "includes/header.php";
?>

<section class="compra">
    <div class="compra-info">
        <img src="<?= e($pelicula["imagen"]) ?>" alt="<?= e($pelicula["titulo"]) ?>">
        <div>
            <span class="genero"><?= e($pelicula["genero"]) ?></span>
            <h1><?= e($pelicula["titulo"]) ?></h1>
            <p>Duracion: <strong><?= e($pelicula["duracion"]) ?></strong></p>
            <p>Precio base por entrada: <strong>S/ <?= number_format($pelicula["precio"], 2) ?></strong></p>
        </div>
    </div>

    <?php if (!empty($mensajeExito) && $reservaCreada): ?>
        <!-- Confirmacion exitosa de la reserva -->
        <div class="mensaje" style="border-left-color: #28a745;">
            <h2 style="color: #4ade80;">¡<?= e($mensajeExito) ?></h2>
            <p><strong>Codigo de Reserva:</strong> <span style="font-size: 18px; color: #fff; background: #222; padding: 4px 8px; border-radius: 4px;"><?= e($reservaCreada["codigo"]) ?></span></p>
            <p><strong>Pelicula:</strong> <?= e($reservaCreada["pelicula_titulo"]) ?></p>
            <p><strong>Sala y Horario:</strong> <?= e($reservaCreada["sala"]) ?> — <?= e($reservaCreada["fecha"]) ?> a las <?= substr(e($reservaCreada["hora"]), 0, 5) ?></p>
            <p><strong>Asiento(s):</strong> <?= e($reservaCreada["asientos"]) ?></p>
            <p><strong>Tipo de Entrada:</strong> <?= ucfirst(e($reservaCreada["tipo_entrada"])) ?></p>
            <p><strong>Total a Pagar en Taquilla:</strong> <strong style="color: #4ade80; font-size: 18px;">S/ <?= number_format($reservaCreada["total"], 2) ?></strong></p>
            
            <div style="margin-top: 25px; display: flex; gap: 15px;">
                <a href="mis_reservas.php" class="boton">Ver en Mis Reservas</a>
                <a href="index.php" class="btn-secundario">Volver a Cartelera</a>
            </div>
        </div>
    <?php else: ?>

        <?php if (!empty($mensajeError)): ?>
            <div class="alerta alerta-error"><?= e($mensajeError) ?></div>
        <?php endif; ?>

        <?php if (empty($funciones)): ?>
            <div class="mensaje">
                <h3>No hay funciones disponibles</h3>
                <p>Actualmente no hay horarios programados para esta pelicula.</p>
                <a href="index.php" class="boton">Explorar otras peliculas</a>
            </div>
        <?php else: ?>
            <form method="POST" action="compra.php?id=<?= (int)$pelicula["id"] ?>" class="formulario" id="formReserva" data-precio-base="<?= (float)$pelicula['precio'] ?>">
                <?php csrf_field(); ?>

                <h2>Configura tu reserva</h2>

                <!-- 1. Seleccion de Horario / Funcion -->
                <label for="select_funcion"><strong>1. Selecciona la funcion y horario:</strong></label>
                <select name="funcion_id" id="select_funcion" data-pelicula-id="<?= (int)$pelicula['id'] ?>" required>
                    <?php foreach ($funciones as $f): ?>
                        <option value="<?= (int)$f['id'] ?>" <?= ($f['id'] == $funcionIdSeleccionada) ? 'selected' : '' ?>>
                            <?= e($f['fecha']) ?> - <?= substr(e($f['hora']), 0, 5) ?> (<?= e($f['sala']) ?>) - <?= (int)$f['asientos_disponibles'] ?> asientos libres
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- 2. Tipo de Entrada -->
                <label for="tipo_entrada"><strong>2. Tipo de entrada:</strong></label>
                <select name="tipo" id="tipo_entrada">
                    <option value="normal" data-desc="0">Entrada normal - S/ <?= number_format($pelicula['precio'], 2) ?></option>
                    <option value="estudiante" data-desc="20">Estudiante (20% de descuento) - S/ <?= number_format($pelicula['precio'] * 0.8, 2) ?></option>
                </select>

                <!-- 3. Mapa Interactivo de Asientos -->
                <label><strong>3. Selecciona tus asientos en la sala:</strong></label>
                <div class="pantalla-cine">PANTALLA</div>

                <div class="asientos-mapa">
                    <?php foreach ($listaAsientos as $asiento): ?>
                        <?php $estaOcupado = in_array($asiento, $asientosOcupados); ?>
                        <label class="asiento-item <?= $estaOcupado ? 'ocupado' : '' ?>" id="label_<?= $asiento ?>">
                            <input 
                                type="checkbox" 
                                name="asientos[]" 
                                value="<?= $asiento ?>" 
                                <?= $estaOcupado ? 'disabled' : '' ?>
                            >
                            <?= $asiento ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div style="display: flex; gap: 20px; font-size: 13px; color: #aaa; margin-bottom: 20px;">
                    <div><span style="display:inline-block; width:12px; height:12px; background:#222228; border:1px solid #444; border-radius:2px; vertical-align:middle; margin-right:5px;"></span> Disponible</div>
                    <div><span style="display:inline-block; width:12px; height:12px; background:#e50914; border-radius:2px; vertical-align:middle; margin-right:5px;"></span> Seleccionado</div>
                    <div><span style="display:inline-block; width:12px; height:12px; background:#1a1a1a; border:1px solid #333; border-radius:2px; vertical-align:middle; margin-right:5px;"></span> Ocupado</div>
                </div>

                <!-- 4. Resumen y Total Calculado en Vivo -->
                <div style="background: #1e1e26; padding: 15px 20px; border-radius: 6px; margin: 15px 0 25px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div id="txtAsientos">Asientos seleccionados: Ninguno</div>
                        <small style="color: #aaa;">El importe final sera verificado por el servidor al confirmar.</small>
                    </div>
                    <div>
                        <span>Total:</span>
                        <strong id="txtTotal" style="font-size: 20px; color: #4ade80;">S/ 0.00</strong>
                    </div>
                </div>

                <button type="submit">Confirmar reserva</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</section>

<!-- Archivo JavaScript desacoplado y organizado -->
<script src="assets/js/reserva.js"></script>

<?php include "includes/footer.php"; ?>