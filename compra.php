<?php
include "funciones.php";

// GET: recibe el ID de la pelicula desde la URL.
// Ejemplo: comprar.php?id=2
$id = $_GET["id"] ?? 0;
$pelicula = obtenerPelicula($id);

if (!$pelicula) {
    die("Pelicula no encontrada");
}

// GET: recibe la hora desde la URL.
// Ejemplo: comprar.php?id=2&hora=20:00
$hora = $_GET["hora"] ?? "20:00";
$mensaje = "";

// POST: se ejecuta cuando se envia el formulario.
// Los datos no aparecen en la URL.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibimos los datos enviados por el formulario.
    $nombre = $_POST["nombre"];
    $dni = $_POST["dni"];
    $asiento = $_POST["asiento"];
    $tipo = $_POST["tipo"];

    $precio = $pelicula["precio"];

    // Aplicamos 20% de descuento si es estudiante.
    if ($tipo == "estudiante") {
        $precio = $precio * 0.8;
    }

    $mensaje = "Reserva realizada correctamente";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprar entrada</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<header>
    <div class="logo">
        CINE<span>UNS</span>
    </div>
    <nav>
        <a href="index.php">Inicio</a>
        <a href="index.php#cartelera">Cartelera</a>
    </nav>
</header>

<section class="compra">
    <div class="compra-info">
        <img src="<?= $pelicula["imagen"] ?>">
        <div>
            <span class="genero"><?= $pelicula["genero"] ?></span>
            <h1><?= $pelicula["titulo"] ?></h1>
            <p>Funcion: <strong><?= $hora ?></strong></p>
            <p>Precio: S/ <?= $pelicula["precio"] ?></p>
        </div>
    </div>

    <?php if ($mensaje != ""): ?>
        <!-- Si existe mensaje, significa que el POST ya fue procesado. -->
        <div class="mensaje">
            <h2><?= $mensaje ?></h2>
            <p>Cliente: <?= htmlspecialchars($nombre) ?></p>
            <p>DNI: <?= htmlspecialchars($dni) ?></p>
            <p>Asiento: <?= $asiento ?></p>
            <p>Tipo: <?= $tipo ?></p>
            <a href="index.php" class="boton">Volver al inicio</a>
        </div>
    <?php else: ?>

        <!-- POST: envia los datos del formulario al mismo archivo. -->
        <form method="POST" class="formulario">
            <h2>Datos de la reserva</h2>

            <label>Nombre</label>
            <input type="text" name="nombre" required>

            <label>DNI</label>
            <input type="text" name="dni" maxlength="8" required>

            <label>Tipo de entrada</label>
            <select name="tipo">
                <option value="normal">Entrada normal</option>
                <option value="estudiante">Estudiante - 20% descuento</option>
            </select>

            <label>Asiento</label>
            <div class="asientos">
                <label><input type="radio" name="asiento" value="A1" required>A1</label>
                <label><input type="radio" name="asiento" value="A2">A2</label>
                <label><input type="radio" name="asiento" value="A3">A3</label>
                <label><input type="radio" name="asiento" value="B1">B1</label>
                <label><input type="radio" name="asiento" value="B2">B2</label>
                <label><input type="radio" name="asiento" value="B3">B3</label>
            </div>

            <!-- Al presionar el boton, se envia el formulario mediante POST. -->
            <button type="submit">Confirmar reserva</button>
        </form>
    <?php endif; ?>
</section>
</body>
</html>