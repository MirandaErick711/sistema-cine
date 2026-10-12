<?php
/**
 * contact.php
 * 
 * Formulario de contacto de Cine UNS.
 * Mantiene la funcionalidad existente con validacion y sanitizacion segura.
 */
require_once "funciones.php";

$mensaje = "";
$nombre = "";
$usuario = usuarioActual();

// POST: se ejecuta cuando el usuario envia el formulario.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_validar();

    // Recibimos los datos enviados mediante POST y los limpiamos
    $nombre  = trim($_POST["nombre"] ?? "");
    $correo  = trim($_POST["correo"] ?? "");
    $motivo  = trim($_POST["motivo"] ?? "");
    $texto   = trim($_POST["mensaje"] ?? "");

    if (!empty($nombre) && !empty($correo) && !empty($texto)) {
        // Mostramos un mensaje indicando que se recibieron los datos.
        $mensaje = "Mensaje enviado correctamente";
    }
}

$tituloPagina = "Contacto";
$paginaActiva = "contacto";

include "includes/header.php";
?>

<section class="contacto-pagina">
    <div>
        <p class="rojo">CONTACTANOS</p>
        <h1>¿Tienes alguna pregunta?</h1>
        <p>Escribenos y te responderemos a la brevedad.</p>
    </div>

    <?php if ($mensaje != ""): ?>
        <!-- Si el POST fue enviado, mostramos el mensaje de confirmacion. -->
        <div class="mensaje">
            <?= e($mensaje) ?>, <?= e($nombre) ?>. Nos pondremos en contacto contigo pronto.
        </div>
    <?php endif; ?>

    <!-- POST: envia los datos del formulario a este mismo archivo. -->
    <form method="POST" class="formulario" action="contact.php">
        <?php csrf_field(); ?>

        <!-- El atributo name permite recibir el dato con $_POST. -->
        <label for="nombre">Nombre completo</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            placeholder="Tu nombre"
            value="<?= $usuario ? e($usuario['nombre']) : '' ?>"
            required
        >

        <label for="correo">Correo electronico</label>
        <input
            type="email"
            id="correo"
            name="correo"
            placeholder="Tu correo"
            value="<?= $usuario ? e($usuario['correo']) : '' ?>"
            required
        >

        <label for="motivo">Motivo de contacto</label>
        <select name="motivo" id="motivo">
            <option>Consulta general</option>
            <option>Problema con reserva</option>
            <option>Promociones y convenios</option>
            <option>Sugerencias</option>
        </select>

        <label for="mensaje_text">Tu mensaje</label>
        <textarea
            id="mensaje_text"
            name="mensaje"
            placeholder="Escribe tu mensaje..."
            rows="5"
            required
        ></textarea>

        <!-- Al presionar el boton, se envia el formulario mediante POST. -->
        <button type="submit">
            Enviar mensaje
        </button>
    </form>
</section>

<?php include "includes/footer.php"; ?>