<?php
/**
 * admin/pelicula_form.php
 * 
 * Formulario para registrar una nueva pelicula o editar una existente
 * Valida campos, permite subir poster seguro a la carpeta img/ y gestiona estados
 */
require_once __DIR__ . '/../funciones.php';
requerirAdmin();

$peliculaModel = new Pelicula();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pelicula = null;
$esEdicion = false;

if ($id > 0) {
    $pelicula = $peliculaModel->obtenerPorId($id);
    if ($pelicula) {
        $esEdicion = true;
    }
}

$error = $_SESSION['mensaje_error'] ?? "";
unset($_SESSION['mensaje_error']);

$tituloPagina = ($esEdicion ? 'Editar' : 'Agregar') . ' Pelicula';
$paginaActiva = "peliculas";

include __DIR__ . "/../includes/admin_header.php";
?>

<section class="seccion" style="max-width: 800px; margin: 0 auto;">
    <div class="titulo">
        <div>
            <p class="rojo">ADMINISTRACION</p>
            <h2><?= $esEdicion ? 'Editar Pelicula' : 'Registrar Nueva Pelicula' ?></h2>
        </div>
        <div>
            <a href="peliculas.php" class="btn-secundario">Volver al listado</a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alerta alerta-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="guardar_pelicula.php" enctype="multipart/form-data" class="formulario">
        <?php csrf_field(); ?>
        
        <?php if ($esEdicion): ?>
            <input type="hidden" name="id" value="<?= (int)$pelicula['id'] ?>">
        <?php endif; ?>

        <label for="titulo"><strong>Titulo de la pelicula:</strong></label>
        <input 
            type="text" 
            id="titulo" 
            name="titulo" 
            required 
            placeholder="Ej. Avengers: Doomsday"
            value="<?= $esEdicion ? e($pelicula['titulo']) : '' ?>"
        >

        <label for="genero"><strong>Genero:</strong></label>
        <input 
            type="text" 
            id="genero" 
            name="genero" 
            required 
            placeholder="Ej. Accion / Aventura"
            value="<?= $esEdicion ? e($pelicula['genero']) : '' ?>"
        >

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label for="duracion"><strong>Duracion:</strong></label>
                <input 
                    type="text" 
                    id="duracion" 
                    name="duracion" 
                    required 
                    placeholder="Ej. 145 min"
                    value="<?= $esEdicion ? e($pelicula['duracion']) : '' ?>"
                >
            </div>
            <div>
                <label for="precio"><strong>Precio entrada (S/):</strong></label>
                <input 
                    type="number" 
                    step="0.50" 
                    id="precio" 
                    name="precio" 
                    required 
                    placeholder="Ej. 18.00"
                    value="<?= $esEdicion ? e($pelicula['precio']) : '18.00' ?>"
                >
            </div>
        </div>

        <label for="sinopsis"><strong>Sinopsis:</strong></label>
        <textarea 
            id="sinopsis" 
            name="sinopsis" 
            rows="4" 
            placeholder="Resumen o descripcion de la trama..."
        ><?= $esEdicion ? e($pelicula['sinopsis']) : '' ?></textarea>

        <label for="trailer"><strong>Enlace de YouTube (Trailer):</strong></label>
        <input 
            type="url" 
            id="trailer" 
            name="trailer" 
            placeholder="https://www.youtube.com/watch?v=..."
            value="<?= $esEdicion ? e($pelicula['trailer']) : '' ?>"
        >

        <!-- Administracion de Poster / Imagen -->
        <label for="imagen_archivo"><strong>Poster / Imagen de la pelicula:</strong></label>
        <?php if ($esEdicion && !empty($pelicula['imagen'])): ?>
            <div style="margin-bottom: 15px; display: flex; align-items: center; gap: 15px;">
                <img src="../<?= e($pelicula['imagen']) ?>" alt="Poster actual" style="width: 70px; height: 100px; object-fit: cover; border-radius: 4px;">
                <span style="font-size: 13px; color: #aaa;">Imagen actual: <code><?= e($pelicula['imagen']) ?></code></span>
            </div>
        <?php endif; ?>
        
        <input 
            type="file" 
            id="imagen_archivo" 
            name="imagen_archivo" 
            accept="image/jpeg,image/png,image/webp"
            style="margin-bottom: 8px;"
        >
        <small style="color: #888; display: block; margin-bottom: 15px;">
            <?= $esEdicion ? 'Deje vacio si no desea cambiar la imagen actual.' : 'Formatos permitidos: JPG, PNG o WEBP (maximo 2MB).' ?>
        </small>

        <label for="estado"><strong>Estado en Cartelera:</strong></label>
        <select name="estado" id="estado">
            <option value="activa" <?= ($esEdicion && $pelicula['estado'] === 'activa') ? 'selected' : '' ?>>Activa (Visible en cartelera)</option>
            <option value="inactiva" <?= ($esEdicion && $pelicula['estado'] === 'inactiva') ? 'selected' : '' ?>>Inactiva (Oculta de cartelera)</option>
        </select>

        <div style="margin-top: 30px; display: flex; gap: 15px;">
            <button type="submit" class="boton" style="flex: 2;">
                <?= $esEdicion ? 'Guardar Cambios' : 'Registrar Pelicula' ?>
            </button>
            <a href="peliculas.php" class="btn-secundario" style="flex: 1; text-align: center;">Cancelar</a>
        </div>
    </form>
</section>

<?php include __DIR__ . "/../includes/admin_footer.php"; ?>
