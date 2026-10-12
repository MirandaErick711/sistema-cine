/**
 * assets/js/reserva.js
 * 
 * Gestion de la seleccion interactiva de asientos y calculo dinamico de totales.
 * Mantiene la logica de JavaScript desacoplada del archivo PHP/HTML de compra.
 */

document.addEventListener('DOMContentLoaded', function () {
    const selectFuncion = document.getElementById('select_funcion');
    const selectTipo = document.getElementById('tipo_entrada');
    const txtAsientos = document.getElementById('txtAsientos');
    const txtTotal = document.getElementById('txtTotal');

    // Cambiar de funcion redirigiendo con el ID seleccionado
    if (selectFuncion) {
        selectFuncion.addEventListener('change', function () {
            const peliculaId = this.dataset.peliculaId || new URLSearchParams(window.location.search).get('id');
            window.location.href = `compra.php?id=${peliculaId}&funcion_id=${this.value}`;
        });
    }

    // Gestion de checkboxes de asientos
    const checkboxesAsientos = document.querySelectorAll('input[name="asientos[]"]');
    checkboxesAsientos.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const label = document.getElementById('label_' + this.value);
            if (label) {
                if (this.checked) {
                    label.classList.add('seleccionado');
                } else {
                    label.classList.remove('seleccionado');
                }
            }
            actualizarCalculos();
        });
    });

    // Recalcular si cambia el tipo de entrada (Normal vs Estudiante)
    if (selectTipo) {
        selectTipo.addEventListener('change', actualizarCalculos);
    }

    function actualizarCalculos() {
        const seleccionados = document.querySelectorAll('input[name="asientos[]"]:checked');
        const cantidad = seleccionados.length;
        const formReserva = document.getElementById('formReserva');
        const precioBase = formReserva ? parseFloat(formReserva.dataset.precioBase || 0) : 0;
        const esEstudiante = selectTipo && selectTipo.value === 'estudiante';

        // 20% de descuento si es estudiante
        const precioUnitario = esEstudiante ? (precioBase * 0.80) : precioBase;
        const total = (cantidad * precioUnitario).toFixed(2);

        if (txtAsientos) {
            const asientosElegidos = Array.from(seleccionados).map(cb => cb.value);
            txtAsientos.textContent = cantidad > 0
                ? `Asientos seleccionados (${cantidad}): ${asientosElegidos.join(', ')}`
                : 'Asientos seleccionados: Ninguno';
        }

        if (txtTotal) {
            txtTotal.textContent = `S/ ${total}`;
        }
    }
});
