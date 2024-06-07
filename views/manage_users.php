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
        .filter-input {
            width: 100%;
            box-sizing: border-box;
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
        
        <div id="childrenContainer" style="display: none;">
            <h3>Hijos</h3>
            <button type="button" onclick="addChild()">Agregar Hijo</button>
            <div id="childrenFields"></div>
        </div>
        
        <button type="button" onclick="submitForm()">Guardar Usuario</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Usuario <input type="text" id="filterUsername" class="filter-input" oninput="filterTable()" placeholder="Buscar por usuario"></th>
                <th>Email <input type="text" id="filterEmail" class="filter-input" oninput="filterTable()" placeholder="Buscar por email"></th>
                <th>Rol <input type="text" id="filterRole" class="filter-input" oninput="filterTable()" placeholder="Buscar por rol"></th>
                <th>Saldo <input type="text" id="filterSaldo" class="filter-input" oninput="filterTable()" placeholder="Buscar por saldo"></th>
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
                        row.insertCell(3).textContent = user.saldo || '';
                        const actionsCell = row.insertCell(4);
                        const editBtn = document.createElement('button');
                        editBtn.textContent = 'Modificar';
                        editBtn.onclick = () => editUser(user);
                        const deleteBtn = document.createElement('button');
                        deleteBtn.textContent = 'Eliminar';
                        deleteBtn.onclick = () => deleteUser(user.id);
                        actionsCell.appendChild(editBtn);
                        actionsCell.appendChild(deleteBtn);
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
                document.getElementById('childrenFields').innerHTML = ''; // Clear children fields
                toggleSaldoInput();
            });
        }

        function deleteUser(userId) {
            fetch(`../php/manage_users.php?userId=${userId}`, {
                method: 'DELETE'
            }).then(() => loadUsers());
        }

        function editUser(user) {
            document.getElementById('userId').value = user.id;
            document.getElementById('username').value = user.username;
            document.getElementById('email').value = user.email;
            document.getElementById('role').value = user.role;
            document.getElementById('saldo').value = user.saldo || '';
            toggleSaldoInput();
            if (user.role === 'colegio') {
                loadChildren(user.id);
            }
        }

        function loadChildren(userId) {
            fetch(`../php/manage_users.php?action=get_children&userId=${userId}`)
                .then(response => response.json())
                .then(children => {
                    const childrenFields = document.getElementById('childrenFields');
                    childrenFields.innerHTML = '';
                    children.forEach(child => {
                        addChild(child);
                    });
                });
        }

        function addChild(child = {}) {
            let container = document.getElementById('childrenFields');
            let childDiv = document.createElement('div');
            childDiv.innerHTML = `
                <input type="hidden" name="childId[]" value="${child.id || ''}">
                <input type="text" name="childName[]" placeholder="Nombre del hijo" value="${child.name || ''}" required>
                <input type="text" name="school[]" placeholder="Escuela" value="${child.school || ''}" required>
                <input type="text" name="course[]" placeholder="Curso" value="${child.course || ''}" required>
            `;
            container.appendChild(childDiv);
        }

        function toggleSaldoInput() {
            var role = document.getElementById('role').value;
            var saldoInput = document.getElementById('saldo');
            var saldoLabel = document.getElementById('saldoLabel');
            var childrenContainer = document.getElementById('childrenContainer');
            if (role === 'colegio') {
                saldoInput.style.display = 'block';
                saldoLabel.style.display = 'block';
                childrenContainer.style.display = 'block';
            } else {
                saldoInput.style.display = 'none';
                saldoLabel.style.display = 'none';
                saldoInput.value = ''; // Reset saldo when hiding
                childrenContainer.style.display = 'none';
                document.getElementById('childrenFields').innerHTML = ''; // Clear children fields
            }
        }

        function filterTable() {
            const filterUsername = document.getElementById('filterUsername').value.toLowerCase();
            const filterEmail = document.getElementById('filterEmail').value.toLowerCase();
            const filterRole = document.getElementById('filterRole').value.toLowerCase();
            const filterSaldo = document.getElementById('filterSaldo').value.toLowerCase();
            const rows = document.querySelectorAll('#usersTableBody tr');

            rows.forEach(row => {
                const username = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                const role = row.cells[2].textContent.toLowerCase();
                const saldo = row.cells[3].textContent.toLowerCase();
                const match = (!filterUsername || username.includes(filterUsername)) &&
                              (!filterEmail || email.includes(filterEmail)) &&
                              (!filterRole || role.includes(filterRole)) &&
                              (!filterSaldo || saldo.includes(filterSaldo));
                row.style.display = match ? '' : 'none';
            });
        }

        window.onload = loadUsers;
    </script>
</body>
</html>
