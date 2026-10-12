<?php
$mensaje = "";
// POST: se ejecuta cuando el usuario envia el formulario.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibimos los datos enviados mediante POST.
    $nombre = $_POST["nombre"];
    $correo = $_POST["correo"];
    $motivo = $_POST["motivo"];
    $texto = $_POST["mensaje"];

    // Mostramos un mensaje indicando que se recibieron los datos.
    $mensaje = "Mensaje enviado correctamente";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto</title>
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

<section class="contacto-pagina">
    <div>
        <p class="rojo">CONTACTANOS</p>
        <h1>¿Tienes alguna pregunta?</h1>
        <p>Escribenos y te responderemos.</p>
    </div>

    <?php if ($mensaje != ""): ?>
        <!-- Si el POST fue enviado, mostramos el mensaje de confirmacion. -->
        <div class="mensaje">
            <?= $mensaje ?>,
            <?= htmlspecialchars($nombre) ?>.
        </div>
    <?php endif; ?>

    <!-- POST: envia los datos del formulario a este mismo archivo. -->
    <form method="POST" class="formulario">

        <!-- El atributo name permite recibir el dato con $_POST. -->
        <input
            type="text"
            name="nombre"
            placeholder="Tu nombre"
            required
        >

        <input
            type="email"
            name="correo"
            placeholder="Tu correo"
            required
        >

        <select name="motivo">
            <option>Consulta general</option>
            <option>Problema con reserva</option>
            <option>Promociones</option>
        </select>

        <textarea
            name="mensaje"
            placeholder="Escribe tu mensaje"
            rows="5"
            required
        ></textarea>

        <!-- Al presionar el boton, se envia el formulario mediante POST. -->
        <button type="submit">
            Enviar mensaje
        </button>
    </form>
</section>
</body>
</html>