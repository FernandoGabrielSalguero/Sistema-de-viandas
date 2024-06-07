USE u437094107_viandas_sch00l;

-- Crear tabla para los Usuarios
CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'school_client', 'company_client', 'tourism_client', 'private_client') NOT NULL
);

-- Crear tabla para los Menús
CREATE TABLE IF NOT EXISTS Menus (
    menu_id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('school', 'company', 'tourism') NOT NULL,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    delivery_date DATE NOT NULL
);
