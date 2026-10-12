<?php
/**
 * index.php
 * 
 * Pagina de inicio del Cine UNS.
 * Consulta las peliculas activas directamente desde la base de datos MySQL (POO Pelicula).
 * Mantiene intacto el diseño, estructura, clases CSS y elementos visuales originales.
 */
require_once "funciones.php";

$buscar = $_GET["buscar"] ?? "";

// Consultar peliculas activas desde la base de datos
$peliculaModel = new Pelicula();
$peliculas = $peliculaModel->obtenerActivas($buscar);

$usuario = usuarioActual();
$tituloPagina = "Inicio";
$paginaActiva = "inicio";

include "includes/header.php";
?>

<section class="hero">
    <div>
        <p class="rojo">CINE UNS</p>
        <h1>Tu pelicula<br>Tu Momento</h1>
        <p>Descubre nuestra cartelera y disfruta
            de las mejores historias.</p>
        <a href="#cartelera" class="boton">Ver cartelera</a>
    </div>
</section>

<section class="seccion" id="cartelera">
    <div class="titulo">
        <div>
            <p class="rojo">AHORA EN CARTELERA</p>
            <h2>Peliculas</h2>
        </div>

        <!-- Buscador con metodo GET conectado a base de datos -->
        <form method="GET" action="index.php#cartelera">
            <input
                type="text"
                name="buscar"
                placeholder="Buscar pelicula..."
                value="<?= e($buscar) ?>"
            >
            <button type="submit">Buscar</button>
        </form>
    </div>

    <div class="peliculas">
        <?php if (empty($peliculas)): ?>
            <p style="grid-column: 1 / -1; color: #aaa; text-align: center; padding: 40px 0;">
                No se encontraron peliculas disponibles en este momento.
            </p>
        <?php else: ?>
            <?php foreach ($peliculas as $p): ?>
                <article class="card">
                    <img src="<?= e($p["imagen"]) ?>" alt="<?= e($p["titulo"]) ?>">
                    <div class="card-body">
                        <span class="genero"><?= e($p["genero"]) ?></span>
                        <h3><?= e($p["titulo"]) ?></h3>
                        <p><?= e($p["duracion"]) ?> · S/ <?= number_format($p["precio"], 2) ?></p>

                        <div class="acciones">
                            <!-- Enlace a los detalles de la pelicula -->
                            <a
                                href="pelicula.php?id=<?= (int)$p["id"] ?>"
                                class="btn-secundario"
                            >
                                Detalles
                            </a>

                            <!-- Enlace a la reserva/compra de entradas -->
                            <a
                                href="compra.php?id=<?= (int)$p["id"] ?>"
                                class="boton"
                            >
                                Comprar
                            </a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<section class="extra">
    <div>
        <p class="rojo">EXPERIENCIA CINE UNS</p>
        <h2>Mas que una pelicula.</h2>
        <p>Revisa nuestra cartelera y comparte
            tu experiencia.</p>
    </div>

    <div class="extra-botones">
        <a href="#cartelera">Ver cartelera</a>
        <a href="contact.php">Escribenos</a>
    </div>
</section>

<?php include "includes/footer.php"; ?>