<?php
session_start();
include '../includes/header_papas.php';
include '../includes/db.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'papas') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['usuario_id'];
    $monto = $_POST['monto'];
    $comprobante = $_FILES['comprobante'];

    // Validar monto
    $montos_validos = [3000, 5000, 10000, 15000, 20000, 50000, 100000, 120000, 150000, 200000];
    if (!in_array($monto, $montos_validos)) {
        $error = "Monto no válido.";
    } else {
        // Validar y mover el archivo de comprobante
        $comprobante_nombre = $usuario_id . "_" . time() . "_" . basename($comprobante["name"]);
        $target_dir = "../uploads/";
        $target_file = $target_dir . $comprobante_nombre;
        if (move_uploaded_file($comprobante["tmp_name"], $target_file)) {
            // Insertar el pedido de saldo
            $stmt = $pdo->prepare("INSERT INTO Pedidos_Saldo (Usuario_Id, Saldo, Estado, Comprobante, Fecha_pedido) VALUES (?, ?, 'Pendiente de aprobación', ?, NOW())");
            if ($stmt->execute([$usuario_id, $monto, $comprobante_nombre])) {
                $success = "Pedido de saldo realizado con éxito. La acreditación puede demorar hasta 72hs";
            } else {
                $error = "Error al realizar el pedido de saldo.";
            }
        } else {
            $error = "Error al subir el comprobante.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cargar Saldo</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .bank-info {
            display: flex;
            flex-direction: column;
        }
        .bank-info-item {
            display: flex;
            align-items: center;
        }
        .bank-info-label {
            margin-right: 10px;
        }
        .copy-button {
            margin-left: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Cargar Saldo</h1>
    <?php
    if (isset($error)) {
        echo "<p class='error'>$error</p>";
    }
    if (isset($success)) {
        echo "<p class='success'>$success</p>";
    }
    ?>
    <form method="post" enctype="multipart/form-data" action="cargar_saldo.php">
        <label for="monto">Monto a recargar:</label>
        <select id="monto" name="monto" required>
            <option value="">Seleccione un monto</option>
            <?php foreach ([3000, 5000, 10000, 15000, 20000, 50000, 100000, 120000, 150000, 200000] as $monto) : ?>
                <option value="<?php echo $monto; ?>"><?php echo number_format($monto, 2); ?> ARS</option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="comprobante">Comprobante:</label>
        <input type="file" id="comprobante" name="comprobante" accept=".jpg, .png" required>
        <br>
        <button type="submit">Cargar Saldo</button>
    </form>
    <div class="bank-info">
        <div class="bank-info-item">
            <span class="bank-info-label">CUIT:</span>
            <span>20273627651</span>
        </div>
        <div class="bank-info-item">
            <span class="bank-info-label">CBU:</span>
            <span id="cbu">0340300408300313721004</span>
            <button class="copy-button" onclick="copiarCBU()">Copiar</button>
        </div>
        <div class="bank-info-item">
            <span class="bank-info-label">Banco:</span>
            <span>BANCO PATAGONIA</span>
        </div>
        <div class="bank-info-item">
            <span class="bank-info-label">Titular de la cuenta:</span>
            <span>Federico Figueroa</span>
        </div>
        <div class="bank-info-item">
            <span class="bank-info-label">Alias:</span>
            <span>ROJO.GENIO.CASINO</span>
        </div>
    </div>
    <script>
        function copiarCBU() {
            const cbu = document.getElementById('cbu').innerText;
            navigator.clipboard.writeText(cbu).then(() => {
                alert("CBU copiado al portapapeles");
            });
        }
    </script>
</body>
</html>
