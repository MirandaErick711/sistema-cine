<?php
include "funciones.php";

// GET: recibe lo que el usuario escribe en el buscador.
// Ejemplo: index.php?buscar=avengers
$buscar = $_GET["buscar"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cine UNS</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>
<header>
    <div class="logo">
        CINE<span>UNS</span>
    </div>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="#cartelera">Cartelera</a>
        <a href="contact.php">Contacto</a>
    </nav>
</header>

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

        <!-- GET: envia el texto del buscador mediante la URL. -->
        <form method="GET">
            <input
                type="text"
                name="buscar"
                placeholder="Buscar pelicula..."
                value="<?= htmlspecialchars($buscar) ?>"
            >
            <button type="submit">Buscar</button>
        </form>
    </div>

    <div class="peliculas">
        <?php foreach ($peliculas as $id => $pelicula): ?>
            <?php
            // Si se escribio una busqueda, comprobamos el titulo.
            // stripos() busca el texto sin importar mayusculas/minusculas.
            if (
                $buscar != "" &&
                stripos($pelicula["titulo"], $buscar) === false
            ) {
                continue;
            }
            ?>

            <article class="card">
                <img src="<?= $pelicula["imagen"] ?>">
                <div class="card-body">
                    <span class="genero"><?= $pelicula["genero"] ?></span>
                    <h3><?= $pelicula["titulo"] ?></h3>
                    <p><?= $pelicula["duracion"] ?> · S/ <?= $pelicula["precio"] ?></p>

                    <div class="acciones">
                        <!-- GET: envia el ID de la pelicula mediante la URL. -->
                        <a
                            href="pelicula.php?id=<?= $id ?>"
                            class="btn-secundario"
                        >
                            Detalles
                        </a>

                        <!-- GET: envia el ID a compra.php. -->
                        <a
                            href="compra.php?id=<?= $id ?>"
                            class="boton"
                        >
                            Comprar
                        </a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
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

<footer>
    CINE UNS · Proyecto PHP
</footer>
</body>
</html>