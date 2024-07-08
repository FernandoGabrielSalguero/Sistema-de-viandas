<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Menús Escolares</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Alta de Menús Escolares</h2>
        <form action="../controllers/menuController.php" method="POST">
            <input type="hidden" name="action" value="addMenu">
            <div>
                <label>Nombre del Menú:</label>
                <input type="text" name="menuName" required>
            </div>
            <div>
                <label>Fecha de Entrega:</label>
                <input type="date" name="deliveryDate" required>
            </div>
            <div>
                <label>Precio:</label>
                <input type="number" name="price" step="0.01" required>
            </div>
            <div>
                <label>Estado:</label>
                <select name="status" required>
                    <option value="En venta">En venta</option>
                    <option value="Sin stock">Sin stock</option>
                </select>
            </div>
            <button type="submit">Añadir Menú</button>
        </form>
    </div>
</body>
</html>
