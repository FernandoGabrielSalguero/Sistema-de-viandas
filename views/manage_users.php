<?php include '../headers/header_admin.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
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
    <h1>Gestión de Usuarios</h1>
    <form id="userForm">
        <input type="hidden" id="userId" name="userId">
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password">
        <label for="role">Rol:</label>
        <select id="role" name="role" onchange="toggleSaldoInput()">
            <option value="admin">Administrador</option>
            <option value="colegio">Cliente Colegio</option>
            <option value="empresa">Cliente Empresa</option>
            <option value="turismo">Cliente Turismo</option>
            <option value="particular">Particular</option>
        </select>
        <label for="saldo" id="saldoLabel" style="display:none;">Saldo:</label>
        <input type="number" id="saldo" name="saldo" style="display:none;" min="0" step="0.01">
        <button type="button" onclick="submitForm()">Guardar Usuario</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="usersTableBody">
            <!-- Los usuarios se cargarán aquí -->
        </tbody>
    </table>

    <script>
        function loadUsers() {
            fetch('../php/manage_users.php')
                .then(response => response.json())
                .then(data => {
                    const tableBody = document.getElementById('usersTableBody');
                    tableBody.innerHTML = '';
                    data.forEach(user => {
                        const row = tableBody.insertRow();
                        row.insertCell(0).textContent = user.username;
                        row.insertCell(1).textContent = user.email;
                        row.insertCell(2).textContent = user.role;
                        const deleteBtn = document.createElement('button');
                        deleteBtn.textContent = 'Eliminar';
                        deleteBtn.onclick = () => deleteUser(user.id);
                        row.insertCell(3).appendChild(deleteBtn);
                    });
                });
        }

        function submitForm() {
            const formData = new FormData(document.getElementById('userForm'));
            fetch('../php/manage_users.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                loadUsers();
                document.getElementById('userForm').reset();
            });
        }

        function deleteUser(userId) {
            fetch(`../php/manage_users.php?userId=${userId}`, { method: 'DELETE' })
                .then(() => loadUsers());
        }

        function toggleSaldoInput() {
            var role = document.getElementById('role').value;
            var saldoInput = document.getElementById('saldo');
            var saldoLabel = document.getElementById('saldoLabel');
            if (role === 'colegio') {
                saldoInput.style.display = 'block';
                saldoLabel.style.display = 'block';
            } else {
                saldoInput.style.display = 'none';
                saldoLabel.style.display = 'none';
                saldoInput.value = ''; // Reset saldo when hiding
            }
        }

        window.onload = loadUsers;
    </script>
</body>
</html>
