<?php
session_start();

// Asegúrate de tener la información necesaria en la sesión o pasada por algún método.
$pedido = $_SESSION['pedido_actual'] ?? null;

if (!$pedido) {
    echo "<p>No hay información de pedido disponible.</p>";
    exit;
}
?>

<div class="popup" id="order-summary-popup" style="display:none;">
    <div class="popup-content">
        <h4>Resumen del Pedido</h4>
        <p>Nombre del alumno: <?= $pedido['nombre_alumno']; ?></p>
        <p>Total del Pedido: $<?= number_format($pedido['total'], 2); ?></p>
        <p>Estado del pedido: <?= $pedido['estado']; ?></p>
        <button onclick="closePopup()">Cerrar</button>
    </div>
</div>

<script>
function closePopup() {
    document.getElementById('order-summary-popup').style.display = 'none';
}
</script>
