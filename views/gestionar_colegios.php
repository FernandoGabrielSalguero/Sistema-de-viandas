<?php include '../headers/header_admin.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Colegios y Cursos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background-color: #f4f4f4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        form {
            margin-bottom: 20px;
        }
        label, input, select, button {
            margin-top: 10px;
            display: block;
        }
    </style>
</head>
<body>
    <h1>Gestión de Colegios y Cursos</h1>
    <form id="colegioForm">
        <input type="hidden" id="colegio_id" name="colegio_id">
        <label for="colegio_nombre">Nombre del Colegio:</label>
        <input type="text" id="colegio_nombre" name="nombre" required>
        <button type="button" onclick="submitColegioForm()">Guardar Colegio</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Colegio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="colegiosTableBody">
            <!-- Los colegios se cargarán aquí -->
        </tbody>
    </table>

    <h2>Gestión de Cursos</h2>
    <form id="cursoForm">
        <input type="hidden" id="curso_id" name="curso_id">
        <label for="curso_colegio">Colegio:</label>
        <select id="curso_colegio" name="colegio_id" required></select>
        <label for="curso_nombre">Nombre del Curso:</label>
        <input type="text" id="curso_nombre" name="curso_nombre" required>
        <button type="button" onclick="submitCursoForm()">Guardar Curso</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Curso</th>
                <th>Colegio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="cursosTableBody">
            <!-- Los cursos se cargarán aquí -->
        </tbody>
    </table>

    <script>
        function loadColegios() {
            fetch('../php/gestionar_colegios.php')
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        console.error('Error:', data.message);
                        return;
                    }
                    const colegiosTableBody = document.getElementById('colegiosTableBody');
                    const cursoColegioSelect = document.getElementById('curso_colegio');
                    colegiosTableBody.innerHTML = '';
                    cursoColegioSelect.innerHTML = '';
                    data.forEach(colegio => {
                        const row = colegiosTableBody.insertRow();
                        row.insertCell(0).textContent = colegio.nombre;
                        const actionsCell = row.insertCell(1);
                        const editBtn = document.createElement('button');
                        editBtn.textContent = 'Modificar';
                        editBtn.onclick = () => editColegio(colegio);
                        const deleteBtn = document.createElement('button');
                        deleteBtn.textContent = 'Eliminar';
                        deleteBtn.onclick = () => deleteColegio(colegio.id);
                        actionsCell.appendChild(editBtn);
                        actionsCell.appendChild(deleteBtn);

                        const option = document.createElement('option');
                        option.value = colegio.id;
                        option.textContent = colegio.nombre;
                        cursoColegioSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    alert('Error al cargar los colegios: ' + error.message);
                    console.error('Error:', error);
                });
        }

        function submitColegioForm() {
            const formData = new FormData(document.getElementById('colegioForm'));
            fetch('../php/gestionar_colegios.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadColegios();
                    document.getElementById('colegioForm').reset();
                } else {
                    alert('Error al guardar el colegio: ' + data.message);
                    console.error('Error:', data.message);
                }
            })
            .catch(error => {
                alert('Error al guardar el colegio: ' + error.message);
                console.error('Error:', error);
            });
        }

        function deleteColegio(colegio_id) {
            fetch(`../php/gestionar_colegios.php?colegio_id=${colegio_id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadColegios();
                } else {
                    alert('Error al eliminar el colegio: ' + data.message);
                    console.error('Error:', data.message);
                }
            })
            .catch(error => {
                alert('Error al eliminar el colegio: ' + error.message);
                console.error('Error:', error);
            });
        }

        function editColegio(colegio) {
            document.getElementById('colegio_id').value = colegio.id;
            document.getElementById('colegio_nombre').value = colegio.nombre;
        }

        function loadCursos() {
            fetch('../php/gestionar_colegios.php?action=get_cursos')
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        alert(data.message);
                        console.error('Error:', data.message);
                        return;
                    }
                    const cursosTableBody = document.getElementById('cursosTableBody');
                    cursosTableBody.innerHTML = '';
                    data.forEach(curso => {
                        const row = cursosTableBody.insertRow();
                        row.insertCell(0).textContent = curso.nombre;
                        row.insertCell(1).textContent = curso.colegio_nombre;
                        const actionsCell = row.insertCell(2);
                        const editBtn = document.createElement('button');
                        editBtn.textContent = 'Modificar';
                        editBtn.onclick = () => editCurso(curso);
                        const deleteBtn = document.createElement('button');
                        deleteBtn.textContent = 'Eliminar';
                        deleteBtn.onclick = () => deleteCurso(curso.id);
                        actionsCell.appendChild(editBtn);
                        actionsCell.appendChild(deleteBtn);
                    });
                })
                .catch(error => {
                    alert('Error al cargar los cursos: ' + error.message);
                    console.error('Error:', error);
                });
        }

        function submitCursoForm() {
            const formData = new FormData(document.getElementById('cursoForm'));
            fetch('../php/gestionar_colegios.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCursos();
                    document.getElementById('cursoForm').reset();
                } else {
                    alert('Error al guardar el curso: ' + data.message);
                    console.error('Error:', data.message);
                }
            })
            .catch(error => {
                alert('Error al guardar el curso: ' + error.message);
                console.error('Error:', error);
            });
        }

        function deleteCurso(curso_id) {
            fetch(`../php/gestionar_colegios.php?curso_id=${curso_id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCursos();
                } else {
                    alert('Error al eliminar el curso: ' + data.message);
                    console.error('Error:', data.message);
                }
            })
            .catch(error => {
                alert('Error al eliminar el curso: ' + error.message);
                console.error('Error:', error);
            });
        }

        function editCurso(curso) {
            document.getElementById('curso_id').value = curso.id;
            document.getElementById('curso_nombre').value = curso.nombre;
            document.getElementById('curso_colegio').value = curso.colegio_id;
        }

        window.onload = () => {
            loadColegios();
            loadCursos();
        }
    </script>
</body>
</html>
