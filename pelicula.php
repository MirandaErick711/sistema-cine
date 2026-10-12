<?php
/**
 * pelicula.php
 * 
 * Vista detallada de una pelicula seleccionada.
 * Muestra sinopsis, duracion, precio, trailer y horarios/funciones disponibles
 * desde la base de datos MySQL con enlaces directos para reservar.
 */
require_once "funciones.php";

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

$peliculaModel = new Pelicula();
$pelicula = $peliculaModel->obtenerPorId($id);

// Si la pelicula no existe o esta inactiva, redirigir amigablemente a la cartelera
if (!$pelicula || $pelicula['estado'] !== 'activa') {
    header("Location: index.php#cartelera");
    exit;
}

// Obtener las funciones disponibles para esta pelicula
$funcionModel = new Funcion();
$funciones = $funcionModel->obtenerPorPelicula($id);

$usuario = usuarioActual();
$tituloPagina = $pelicula["titulo"];
$paginaActiva = "cartelera";

include "includes/header.php";
?>

<section class="detalle">
    <img src="<?= e($pelicula["imagen"]) ?>" alt="<?= e($pelicula["titulo"]) ?>">
    <div class="detalle-info">
        <span class="genero"><?= e($pelicula["genero"]) ?></span>
        <h1><?= e($pelicula["titulo"]) ?></h1>
        <p style="color: #aaa; margin: 10px 0 20px;">
            <strong>Duracion:</strong> <?= e($pelicula["duracion"]) ?> &nbsp;|&nbsp; 
            <strong>Precio:</strong> S/ <?= number_format($pelicula["precio"], 2) ?>
        </p>

        <?php if (!empty($pelicula["sinopsis"])): ?>
            <p style="line-height: 1.7; color: #ccc; margin-bottom: 25px;">
                <?= nl2br(e($pelicula["sinopsis"])) ?>
            </p>
        <?php endif; ?>

        <?php if (!empty($pelicula["trailer"])): ?>
            <p style="margin-bottom: 25px;">
                <a href="<?= e($pelicula["trailer"]) ?>" target="_blank" rel="noopener noreferrer" class="btn-secundario" style="display: inline-flex; align-items: center; gap: 8px;">
                    ▶ Ver Trailer Oficial
                </a>
            </p>
        <?php endif; ?>

        <div class="horarios">
            <h3>Horarios y Funciones Disponibles</h3>
            <p style="margin-bottom: 15px;">Selecciona un horario para reservar tus entradas:</p>

            <?php if (empty($funciones)): ?>
                <p style="color: #888;">No hay funciones programadas para esta pelicula en este momento.</p>
            <?php else: ?>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php foreach ($funciones as $f): ?>
                        <a href="compra.php?id=<?= (int)$pelicula["id"] ?>&funcion_id=<?= (int)$f["id"] ?>" title="<?= e($f["sala"]) ?> - <?= e($f["fecha"]) ?>">
                            <strong><?= substr(e($f["hora"]), 0, 5) ?></strong>
                            <small style="display: block; font-size: 11px; color: #888;"><?= e($f["sala"]) ?> (<?= e($f["fecha"]) ?>)</small>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="acciones" style="margin-top: 30px;">
            <a href="compra.php?id=<?= (int)$pelicula["id"] ?>" class="boton">Reservar Entrada</a>
            <a href="index.php#cartelera" class="btn-secundario">Volver a Cartelera</a>
        </div>
    </div>
</section>

<?php include "includes/footer.php"; ?>