-- =====================================================
-- BASE DE DATOS FARMACIA CENTRAL - VERSIÓN FINAL
-- =====================================================

DROP DATABASE IF EXISTS farmacia_central;
CREATE DATABASE farmacia_central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE farmacia_central;

-- =====================================================
-- TABLA: categorias
-- =====================================================
CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL UNIQUE,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: clientes
-- =====================================================
CREATE TABLE clientes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  telefono VARCHAR(20) NULL,
  correo VARCHAR(120) NULL,
  direccion VARCHAR(255) NULL,
  notas TEXT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: proveedores
-- =====================================================
CREATE TABLE proveedores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  contacto VARCHAR(120) NULL,
  telefono VARCHAR(20) NULL,
  correo VARCHAR(120) NULL,
  direccion VARCHAR(255) NULL,
  notas TEXT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: usuarios (con password en texto plano)
-- =====================================================
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  usuario VARCHAR(60) NOT NULL UNIQUE,
  password VARCHAR(100) NOT NULL,
  rol ENUM('ADMIN','CAJERO','ALMACEN') NOT NULL DEFAULT 'ADMIN',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA: productos (con columna imagen)
-- =====================================================
CREATE TABLE productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo_barras VARCHAR(50) NOT NULL UNIQUE,
  clave VARCHAR(50) NULL UNIQUE,
  nombre VARCHAR(180) NOT NULL,
  categoria_id INT NULL,
  unidad VARCHAR(20) NOT NULL DEFAULT 'PZA',
  precio_compra DECIMAL(10,2) NOT NULL DEFAULT 0,
  precio_venta DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  stock_minimo INT NOT NULL DEFAULT 5,
  fecha_caducidad DATE NULL,
  imagen VARCHAR(500) NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- =====================================================
-- TABLA: ventas
-- =====================================================
CREATE TABLE ventas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(30) NOT NULL UNIQUE,
  cliente_id INT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  descuento DECIMAL(10,2) NOT NULL DEFAULT 0,
  total DECIMAL(10,2) NOT NULL,
  metodo_pago ENUM('EFECTIVO','TARJETA','TRANSFERENCIA') NOT NULL DEFAULT 'EFECTIVO',
  observaciones TEXT NULL,
  usuario_id INT NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ventas_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id),
  CONSTRAINT fk_ventas_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- =====================================================
-- TABLA: venta_detalle
-- =====================================================
CREATE TABLE venta_detalle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  venta_id INT NOT NULL,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  importe DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_venta_detalle_venta FOREIGN KEY (venta_id) REFERENCES ventas(id) ON DELETE CASCADE,
  CONSTRAINT fk_venta_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- =====================================================
-- TABLA: compras
-- =====================================================
CREATE TABLE compras (
  id INT AUTO_INCREMENT PRIMARY KEY,
  folio VARCHAR(30) NOT NULL UNIQUE,
  proveedor_id INT NULL,
  total DECIMAL(10,2) NOT NULL,
  factura_referencia VARCHAR(80) NULL,
  observaciones TEXT NULL,
  usuario_id INT NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_compras_proveedor FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
  CONSTRAINT fk_compras_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- =====================================================
-- TABLA: compra_detalle
-- =====================================================
CREATE TABLE compra_detalle (
  id INT AUTO_INCREMENT PRIMARY KEY,
  compra_id INT NOT NULL,
  producto_id INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  importe DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_compra_detalle_compra FOREIGN KEY (compra_id) REFERENCES compras(id) ON DELETE CASCADE,
  CONSTRAINT fk_compra_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- =====================================================
-- DATOS INICIALES
-- =====================================================

-- Categorías
INSERT INTO categorias (nombre) VALUES
('Analgésicos'),
('Antibióticos'),
('Vitaminas'),
('Jarabes'),
('Curación'),
('Higiene personal');

-- Clientes
INSERT INTO clientes (nombre, telefono, correo, direccion, notas) VALUES
('Público general', '0000000000', NULL, NULL, 'Cliente genérico'),
('María López', '9631234567', 'maria@example.com', 'Barrio Centro', 'Cliente frecuente'),
('Juan Pérez', '9637654321', 'juan@example.com', 'Colonia Miguel Alemán', 'Compra medicamentos para presión');

-- Proveedores
INSERT INTO proveedores (nombre, contacto, telefono, correo, direccion, notas) VALUES
('Distribuidora Médica del Sur', 'Ernesto Gómez', '9611112233', 'ventas@dmsur.com', 'Tuxtla Gutiérrez, Chiapas', 'Entrega semanal'),
('Farmainsumos Nacional', 'Laura Díaz', '9612223344', 'contacto@farmainsumos.mx', 'Tapachula, Chiapas', 'Maneja mayoreo'),
('Botica Mayorista Centro', 'Carlos Ruiz', '9613334455', 'pedidos@boticamayorista.com', 'San Cristóbal de Las Casas', 'Entrega bajo pedido');

-- Usuarios (contraseñas en texto plano)
INSERT INTO usuarios (nombre, usuario, password, rol) VALUES
('Administrador', 'admin', 'admin123', 'ADMIN'),
('Cajero Principal', 'cajero', 'cajero123', 'CAJERO'),
('Almacenista', 'almacen', 'almacen123', 'ALMACEN');

-- Productos
INSERT INTO productos (codigo_barras, clave, nombre, categoria_id, unidad, precio_compra, precio_venta, stock, stock_minimo, fecha_caducidad) VALUES
('7501000000011', 'PARA500', 'Paracetamol 500 mg 10 tabletas', 1, 'CAJA', 12.50, 18.00, 50, 8, '2027-01-31'),
('7501000000012', 'IBU400', 'Ibuprofeno 400 mg 10 tabletas', 1, 'CAJA', 14.80, 22.00, 40, 8, '2027-02-28'),
('7501000000013', 'AMOX500', 'Amoxicilina 500 mg 12 cápsulas', 2, 'CAJA', 42.00, 58.00, 25, 5, '2026-12-31'),
('7501000000014', 'VITC1G', 'Vitamina C 1 g efervescente', 3, 'TUBO', 28.00, 39.00, 30, 5, '2027-05-31'),
('7501000000015', 'JRCF120', 'Jarabe para la tos 120 ml', 4, 'FRASCO', 36.00, 49.00, 20, 4, '2026-11-30'),
('7501000000016', 'GASA10', 'Gasas estériles paquete 10 pzas', 5, 'PAQ', 10.00, 16.50, 60, 10, '2028-03-31'),
('7501000000017', 'ALC250', 'Alcohol 250 ml', 5, 'FRASCO', 17.00, 25.00, 35, 6, '2028-01-31'),
('7501000000018', 'JABANT', 'Jabón antibacterial 250 ml', 6, 'FRASCO', 19.00, 29.00, 28, 5, '2027-09-30');

-- =====================================================
-- VERIFICACIÓN FINAL
-- =====================================================
SELECT '=== BASE DE DATOS CREADA CORRECTAMENTE ===' as '';
SELECT 'Categorías: ' as '', COUNT(*) as '' FROM categorias;
SELECT 'Clientes: ' as '', COUNT(*) as '' FROM clientes;
SELECT 'Proveedores: ' as '', COUNT(*) as '' FROM proveedores;
SELECT 'Usuarios: ' as '', COUNT(*) as '' FROM usuarios;
SELECT 'Productos: ' as '', COUNT(*) as '' FROM productos;

SELECT '=== CREDENCIALES DE ACCESO ===' as '';
SELECT 'Administrador: admin / admin123' as '';
SELECT 'Cajero: cajero / cajero123' as '';
SELECT 'Almacén: almacen / almacen123' as '';