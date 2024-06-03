<div id="popup" class="popup" style="display:none;">
    <div class="popup-content">
        <h4>Resumen del Pedido</h4>
        <p id="resumen-pedido"></p>
        <p>Gracias por confiar en nosotros! Tu pedido se encuentra en estado "En espera de aprobación" eso significa que estamos esperando la transferencia del saldo para poder aprobar el encargo. Recordá que podes hacerlo al siguiente CBU: 0340300408300313721004 a nombre de: Federico Figueroa en el banco: BANCO PATAGONIA, CUIT: 20273627651 Alias: ROJO.GENIO.CASINO. La aprobación puede demorar hasta 48 hs en efectuarse. Cuando esté aprobada, el estado de tu pedido será: APROBADO</p>
        <button id="popup-close">Cerrar</button>
    </div>
</div>
<script>
    document.getElementById('popup-close').addEventListener('click', function() {
        document.getElementById('popup').style.display = 'none';
    });
</script>
