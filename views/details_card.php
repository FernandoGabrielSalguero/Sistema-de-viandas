<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Detalles del Pago</title>
</head>
<body>
    <div class="details-card">
        <h4>Datos bancarios</h4>
        <p>CBU: <span id="cbu-text">0340300408300313721004</span></p>
        <p>A nombre de: Federico Figueroa</p>
        <p>Banco: BANCO PATAGONIA</p>
        <p>CUIT: 20273627651</p>
        <p>Alias: ROJO.GENIO.CASINO</p>
        <button onclick="copyCBU()">Copiar CBU</button>
        <script>
            function copyCBU() {
                const cbuText = document.getElementById('cbu-text').innerText;
                navigator.clipboard.writeText(cbuText).then(() => {
                    alert('CBU copiado al portapapeles');
                }, (err) => {
                    alert('Error al copiar: ', err);
                });
            }
        </script>
    </div>
</body>
</html>
