CREATE TABLE Usuarios (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100),
    Usuario VARCHAR(100),
    Contraseña VARCHAR(255),
    Telefono VARCHAR(15),
    Correo VARCHAR(100),
    Pedidos_saldo TEXT,
    Saldo DECIMAL(10, 2) DEFAULT 0.00,
    Pedidos_comida TEXT,
    Rol ENUM('papas', 'cocina', 'representante', 'administrador'),
    Hijos TEXT
);

CREATE TABLE Hijos (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100),
    Colegio INT,
    Curso INT,
    Preferencias_Alimenticias TEXT
);

CREATE TABLE Colegios (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100),
    Dirección VARCHAR(255),
    Cursos TEXT,
    Representantes TEXT
);

CREATE TABLE Cursos (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100)
);

CREATE TABLE Menú (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100),
    Fecha_entrega DATE,
    Fecha_hora_compra DATETIME,
    Fecha_hora_cancelacion DATETIME,
    Precio DECIMAL(10, 2),
    Estado ENUM('En venta', 'Sin stock')
);

CREATE TABLE Pedidos_Comida (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre_menú VARCHAR(100),
    Colegio INT,
    Curso INT,
    Nombre_alumno VARCHAR(100),
    Fecha_entrega DATE,
    Estado_pedido ENUM('Procesando', 'Cancelado'),
    Preferencias_alimenticias TEXT
);

CREATE TABLE Pedidos_Saldo (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Saldo DECIMAL(10, 2),
    Estado ENUM('Pendiente de aprobación', 'Aprobado'),
    Comprobante VARCHAR(255),
    Fecha_pedido DATETIME
);

CREATE TABLE Preferencias_Alimenticias (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Nombre VARCHAR(100)
);
