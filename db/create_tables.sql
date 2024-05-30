CREATE DATABASE gestion_viandas;

USE gestion_viandas;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    apellido VARCHAR(50),
    usuario VARCHAR(50) UNIQUE,
    contrasena VARCHAR(50),
    telefono VARCHAR(15),
    correo VARCHAR(100),
    rol ENUM('Administrador', 'Usuario') DEFAULT 'Usuario',
    saldo DECIMAL(10, 2) DEFAULT 0.00
);

-- Tabla de hijos
CREATE TABLE hijos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    nombre VARCHAR(50),
    curso VARCHAR(50),
    notas TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de menú
CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE,
    nombre VARCHAR(100),
    precio DECIMAL(10, 2),
    descuento_semanal INT DEFAULT 0
);

-- Tabla de pedidos
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    hijo_id INT,
    menu_id INT,
    estado ENUM('Procesando', 'Cancelado', 'Aprobado') DEFAULT 'Procesando',
    fecha_pedido DATE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (hijo_id) REFERENCES hijos(id),
    FOREIGN KEY (menu_id) REFERENCES menu(id)
);

-- Tabla de notificaciones
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    mensaje TEXT,
    leido BOOLEAN DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);