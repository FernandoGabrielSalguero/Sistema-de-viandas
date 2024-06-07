<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Colegios</title>
    <link rel="stylesheet" href="path/to/your/css/styles.css"> <!-- Asegúrate de poner la ruta correcta -->
</head>
<body>
    <h1>Gestionar Colegios</h1>

    <form id="formAgregarColegio">
        <input type="text" id="nombreColegio" name="nombre" placeholder="Nombre del Colegio" required>
        <button type="submit">Agregar Colegio</button>
    </form>

    <table id="tablaColegios">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Aquí se llenarán los datos de los colegios -->
        </tbody>
    </table>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
    const formAgregarColegio = document.getElementById("formAgregarColegio");
    const tablaColegios = document.getElementById("tablaColegios").getElementsByTagName("tbody")[0];

    formAgregarColegio.addEventListener("submit", function(e) {
        e.preventDefault();
        const nombre = document.getElementById("nombreColegio").value;
        fetch("php/gestionar_colegios.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ action: "create", nombre: nombre })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cargarColegios();
            } else {
                alert("Error al agregar el colegio.");
            }
        });
    });

    function cargarColegios() {
        fetch("php/gestionar_colegios.php?action=read")
        .then(response => response.json())
        .then(data => {
            tablaColegios.innerHTML = "";
            data.colegios.forEach(colegio => {
                const row = tablaColegios.insertRow();
                row.insertCell(0).textContent = colegio.id;
                row.insertCell(1).textContent = colegio.nombre;
                const acciones = row.insertCell(2);
                const btnEditar = document.createElement("button");
                btnEditar.textContent = "Editar";
                btnEditar.addEventListener("click", () => editarColegio(colegio.id, colegio.nombre));
                acciones.appendChild(btnEditar);
                const btnEliminar = document.createElement("button");
                btnEliminar.textContent = "Eliminar";
                btnEliminar.addEventListener("click", () => eliminarColegio(colegio.id));
                acciones.appendChild(btnEliminar);
            });
        });
    }

    function editarColegio(id, nombreActual) {
        const nuevoNombre = prompt("Nuevo nombre del colegio:", nombreActual);
        if (nuevoNombre) {
            fetch("php/gestionar_colegios.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ action: "update", id: id, nombre: nuevoNombre })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cargarColegios();
                } else {
                    alert("Error al actualizar el colegio.");
                }
            });
        }
    }

    function eliminarColegio(id) {
        if (confirm("¿Estás seguro de que deseas eliminar este colegio?")) {
            fetch("php/gestionar_colegios.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ action: "delete", id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    cargarColegios();
                } else {
                    alert("Error al eliminar el colegio.");
                }
            });
        }
    }

    // Cargar los colegios al inicio
    cargarColegios();
});

    </script> <!-- Asegúrate de poner la ruta correcta -->
</body>
</html>
