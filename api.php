<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/check_auth.php';

function json_response($data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function input_json(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : $_POST;
}

function fetch_all(string $sql, array $params = []): array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function fetch_one(string $sql, array $params = []): ?array {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch();
    return $row ?: null;
}

$action = $_GET['action'] ?? '';
$pdo = db();

try {
    switch ($action) {
        case 'dashboard':
            $stats = [
                'ventas_dia' => fetch_one("SELECT COALESCE(SUM(total),0) total FROM ventas WHERE DATE(fecha)=CURDATE()")['total'],
                'compras_mes' => fetch_one("SELECT COALESCE(SUM(total),0) total FROM compras WHERE YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE())")['total'],
                'productos' => fetch_one("SELECT COUNT(*) total FROM productos WHERE activo=1")['total'],
                'stock_bajo' => fetch_one("SELECT COUNT(*) total FROM productos WHERE activo=1 AND stock <= stock_minimo")['total'],
                'ultimas_ventas' => fetch_all("SELECT v.id, v.folio, v.fecha, v.total, COALESCE(c.nombre,'Público general') cliente FROM ventas v LEFT JOIN clientes c ON c.id=v.cliente_id ORDER BY v.id DESC LIMIT 8"),
                'productos_stock_bajo' => fetch_all("SELECT codigo_barras, nombre, stock, stock_minimo, imagen FROM productos WHERE activo=1 AND stock <= stock_minimo ORDER BY stock ASC, nombre ASC LIMIT 10"),
            ];
            json_response(['ok' => true, 'data' => $stats]);
            break;

        case 'list_clientes':
            $q = '%' . trim($_GET['q'] ?? '') . '%';
            $rows = fetch_all("SELECT * FROM clientes WHERE activo=1 AND (nombre LIKE ? OR telefono LIKE ? OR correo LIKE ?) ORDER BY nombre ASC", [$q,$q,$q]);
            json_response(['ok'=>true,'data'=>$rows]);
            break;

        case 'save_cliente':
            $data = input_json();
            if (!empty($data['id'])) {
                $stmt = $pdo->prepare("UPDATE clientes SET nombre=?, telefono=?, correo=?, direccion=?, notas=? WHERE id=?");
                $stmt->execute([$data['nombre'],$data['telefono'] ?? null,$data['correo'] ?? null,$data['direccion'] ?? null,$data['notas'] ?? null,$data['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO clientes(nombre,telefono,correo,direccion,notas) VALUES (?,?,?,?,?)");
                $stmt->execute([$data['nombre'],$data['telefono'] ?? null,$data['correo'] ?? null,$data['direccion'] ?? null,$data['notas'] ?? null]);
            }
            json_response(['ok'=>true,'message'=>'Cliente guardado correctamente']);
            break;

        case 'delete_cliente':
            $id = (int)($_POST['id'] ?? 0);
            $pdo->prepare("UPDATE clientes SET activo=0 WHERE id=?")->execute([$id]);
            json_response(['ok'=>true,'message'=>'Cliente desactivado']);
            break;

        case 'list_proveedores':
            $q = '%' . trim($_GET['q'] ?? '') . '%';
            $rows = fetch_all("SELECT * FROM proveedores WHERE activo=1 AND (nombre LIKE ? OR contacto LIKE ? OR telefono LIKE ?) ORDER BY nombre ASC", [$q,$q,$q]);
            json_response(['ok'=>true,'data'=>$rows]);
            break;

        case 'save_proveedor':
            $data = input_json();
            if (!empty($data['id'])) {
                $stmt = $pdo->prepare("UPDATE proveedores SET nombre=?, contacto=?, telefono=?, correo=?, direccion=?, notas=? WHERE id=?");
                $stmt->execute([$data['nombre'],$data['contacto'] ?? null,$data['telefono'] ?? null,$data['correo'] ?? null,$data['direccion'] ?? null,$data['notas'] ?? null,$data['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO proveedores(nombre,contacto,telefono,correo,direccion,notas) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$data['nombre'],$data['contacto'] ?? null,$data['telefono'] ?? null,$data['correo'] ?? null,$data['direccion'] ?? null,$data['notas'] ?? null]);
            }
            json_response(['ok'=>true,'message'=>'Proveedor guardado correctamente']);
            break;

        case 'delete_proveedor':
            $id = (int)($_POST['id'] ?? 0);
            $pdo->prepare("UPDATE proveedores SET activo=0 WHERE id=?")->execute([$id]);
            json_response(['ok'=>true,'message'=>'Proveedor desactivado']);
            break;

        case 'list_categorias':
            json_response(['ok'=>true,'data'=>fetch_all("SELECT * FROM categorias WHERE activo=1 ORDER BY nombre ASC")]);
            break;

        case 'save_categoria':
            $data = input_json();
            $pdo->prepare("INSERT INTO categorias(nombre) VALUES (?)")->execute([$data['nombre']]);
            json_response(['ok'=>true,'message'=>'Categoría agregada']);
            break;

        case 'upload_image':
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $filepath = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                    json_response(['ok' => true, 'url' => 'uploads/' . $filename]);
                } else {
                    json_response(['ok' => false, 'message' => 'Error al subir la imagen'], 500);
                }
            } else {
                json_response(['ok' => false, 'message' => 'No se recibió ninguna imagen'], 400);
            }
            break;

        case 'list_productos':
            $q = '%' . trim($_GET['q'] ?? '') . '%';
            $rows = fetch_all("SELECT p.*, c.nombre categoria FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.activo=1 AND (p.nombre LIKE ? OR p.codigo_barras LIKE ? OR p.clave LIKE ?) ORDER BY p.nombre ASC", [$q,$q,$q]);
            json_response(['ok'=>true,'data'=>$rows]);
            break;

        case 'save_producto':
            $data = input_json();
            if (!empty($data['id'])) {
                $stmt = $pdo->prepare("UPDATE productos SET codigo_barras=?, clave=?, nombre=?, categoria_id=?, unidad=?, precio_compra=?, precio_venta=?, stock=?, stock_minimo=?, fecha_caducidad=?, imagen=? WHERE id=?");
                $stmt->execute([
                    $data['codigo_barras'], $data['clave'] ?? null, $data['nombre'], $data['categoria_id'] ?: null,
                    $data['unidad'] ?? 'PZA', $data['precio_compra'], $data['precio_venta'], $data['stock'],
                    $data['stock_minimo'] ?? 5, $data['fecha_caducidad'] ?: null, $data['imagen'] ?? null, $data['id']
                ]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO productos(codigo_barras,clave,nombre,categoria_id,unidad,precio_compra,precio_venta,stock,stock_minimo,fecha_caducidad,imagen) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([
                    $data['codigo_barras'], $data['clave'] ?? null, $data['nombre'], $data['categoria_id'] ?: null,
                    $data['unidad'] ?? 'PZA', $data['precio_compra'], $data['precio_venta'], $data['stock'],
                    $data['stock_minimo'] ?? 5, $data['fecha_caducidad'] ?: null, $data['imagen'] ?? null
                ]);
            }
            json_response(['ok'=>true,'message'=>'Producto guardado correctamente']);
            break;

        case 'delete_producto':
            $id = (int)($_POST['id'] ?? 0);
            $pdo->prepare("UPDATE productos SET activo=0 WHERE id=?")->execute([$id]);
            json_response(['ok'=>true,'message'=>'Producto desactivado']);
            break;

        case 'buscar_producto':
            $q = '%' . trim($_GET['q'] ?? '') . '%';
            $rows = fetch_all("SELECT p.*, c.nombre categoria FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.activo=1 AND (p.nombre LIKE ? OR p.codigo_barras LIKE ? OR p.clave LIKE ?) ORDER BY CASE WHEN p.codigo_barras=? THEN 0 ELSE 1 END, p.nombre ASC LIMIT 20", [$q,$q,$q, trim($_GET['q'] ?? '')]);
            json_response(['ok'=>true,'data'=>$rows]);
            break;

        case 'save_venta':
            $data = input_json();
            $items = $data['items'] ?? [];
            if (!$items) json_response(['ok'=>false,'message'=>'No hay productos en la venta'], 422);
            $pdo->beginTransaction();
            $subtotal = 0;
            foreach ($items as $item) {
                $producto = fetch_one("SELECT * FROM productos WHERE id=? FOR UPDATE", [$item['id']]);
                if (!$producto) throw new Exception('Producto no encontrado');
                if ($producto['stock'] < $item['cantidad']) throw new Exception('Stock insuficiente para ' . $producto['nombre']);
                $subtotal += $item['cantidad'] * $item['precio_venta'];
            }
            $descuento = (float)($data['descuento'] ?? 0);
            $total = max($subtotal - $descuento, 0);
            $folio = 'V-' . date('YmdHis');
            $stmt = $pdo->prepare("INSERT INTO ventas(folio,cliente_id,subtotal,descuento,total,metodo_pago,observaciones,usuario_id) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$folio, $data['cliente_id'] ?: null, $subtotal, $descuento, $total, $data['metodo_pago'] ?? 'EFECTIVO', $data['observaciones'] ?? null, $_SESSION['user_id']]);
            $ventaId = (int)$pdo->lastInsertId();
            $ins = $pdo->prepare("INSERT INTO venta_detalle(venta_id,producto_id,cantidad,precio_unitario,importe) VALUES (?,?,?,?,?)");
            $upd = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id=?");
            foreach ($items as $item) {
                $importe = $item['cantidad'] * $item['precio_venta'];
                $ins->execute([$ventaId, $item['id'], $item['cantidad'], $item['precio_venta'], $importe]);
                $upd->execute([$item['cantidad'], $item['id']]);
            }
            $pdo->commit();
            json_response(['ok'=>true,'message'=>'Venta registrada','folio'=>$folio,'venta_id'=>$ventaId]);
            break;

        case 'list_ventas':
            $fi = $_GET['fi'] ?: date('Y-m-01');
            $ff = $_GET['ff'] ?: date('Y-m-d');
            $filtro = '%' . trim($_GET['q'] ?? '') . '%';
            $rows = fetch_all("SELECT v.*, COALESCE(c.nombre,'Público general') cliente, u.nombre usuario FROM ventas v LEFT JOIN clientes c ON c.id=v.cliente_id LEFT JOIN usuarios u ON u.id=v.usuario_id WHERE DATE(v.fecha) BETWEEN ? AND ? AND (v.folio LIKE ? OR COALESCE(c.nombre,'') LIKE ? OR COALESCE(u.nombre,'') LIKE ?) ORDER BY v.id DESC", [$fi,$ff,$filtro,$filtro,$filtro]);
            json_response(['ok'=>true,'data'=>$rows]);
            break;

        case 'venta_detalle':
            $id = (int)($_GET['id'] ?? 0);
            $venta = fetch_one("SELECT v.*, COALESCE(c.nombre,'Público general') cliente, u.nombre usuario FROM ventas v LEFT JOIN clientes c ON c.id=v.cliente_id LEFT JOIN usuarios u ON u.id=v.usuario_id WHERE v.id=?", [$id]);
            $detalle = fetch_all("SELECT d.*, p.nombre, p.codigo_barras FROM venta_detalle d INNER JOIN productos p ON p.id=d.producto_id WHERE d.venta_id=?", [$id]);
            json_response(['ok'=>true,'data'=>['venta'=>$venta,'detalle'=>$detalle]]);
            break;

        case 'save_compra':
            $data = input_json();
            $items = $data['items'] ?? [];
            if (!$items) json_response(['ok'=>false,'message'=>'No hay productos en la compra'], 422);
            $pdo->beginTransaction();
            $total = 0;
            foreach ($items as $item) {
                $total += $item['cantidad'] * $item['precio_compra'];
            }
            $folio = 'C-' . date('YmdHis');
            $stmt = $pdo->prepare("INSERT INTO compras(folio,proveedor_id,total,factura_referencia,observaciones,usuario_id) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$folio, $data['proveedor_id'] ?: null, $total, $data['factura'] ?? null, $data['observaciones'] ?? null, $_SESSION['user_id']]);
            $compraId = (int)$pdo->lastInsertId();
            $ins = $pdo->prepare("INSERT INTO compra_detalle(compra_id,producto_id,cantidad,precio_unitario,importe) VALUES (?,?,?,?,?)");
            $upd = $pdo->prepare("UPDATE productos SET stock = stock + ?, precio_compra = ? WHERE id=?");
            foreach ($items as $item) {
                $importe = $item['cantidad'] * $item['precio_compra'];
                $ins->execute([$compraId, $item['id'], $item['cantidad'], $item['precio_compra'], $importe]);
                $upd->execute([$item['cantidad'], $item['precio_compra'], $item['id']]);
            }
            $pdo->commit();
            json_response(['ok'=>true,'message'=>'Compra registrada','folio'=>$folio,'compra_id'=>$compraId]);
            break;

        case 'list_compras':
            $fi = $_GET['fi'] ?: date('Y-m-01');
            $ff = $_GET['ff'] ?: date('Y-m-d');
            $filtro = '%' . trim($_GET['q'] ?? '') . '%';
            $rows = fetch_all("SELECT c.*, COALESCE(p.nombre,'Sin proveedor') proveedor, u.nombre usuario FROM compras c LEFT JOIN proveedores p ON p.id=c.proveedor_id LEFT JOIN usuarios u ON u.id=c.usuario_id WHERE DATE(c.fecha) BETWEEN ? AND ? AND (c.folio LIKE ? OR COALESCE(p.nombre,'') LIKE ? OR COALESCE(c.factura_referencia,'') LIKE ?) ORDER BY c.id DESC", [$fi,$ff,$filtro,$filtro,$filtro]);
            json_response(['ok'=>true,'data'=>$rows]);
            break;

        case 'compra_detalle':
            $id = (int)($_GET['id'] ?? 0);
            $compra = fetch_one("SELECT c.*, COALESCE(p.nombre,'Sin proveedor') proveedor, u.nombre usuario FROM compras c LEFT JOIN proveedores p ON p.id=c.proveedor_id LEFT JOIN usuarios u ON u.id=c.usuario_id WHERE c.id=?", [$id]);
            $detalle = fetch_all("SELECT d.*, pr.nombre, pr.codigo_barras FROM compra_detalle d INNER JOIN productos pr ON pr.id=d.producto_id WHERE d.compra_id=?", [$id]);
            json_response(['ok'=>true,'data'=>['compra'=>$compra,'detalle'=>$detalle]]);
            break;

        case 'list_usuarios':
            json_response(['ok'=>true,'data'=>fetch_all("SELECT id,nombre,usuario,rol,activo,fecha_registro FROM usuarios ORDER BY id DESC")]);
            break;

        case 'save_usuario':
            $data = input_json();
            if (!empty($data['id'])) {
                if (!empty($data['password'])) {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, usuario=?, rol=?, password_hash=? WHERE id=?");
                    $stmt->execute([$data['nombre'],$data['usuario'],$data['rol'],password_hash($data['password'], PASSWORD_DEFAULT),$data['id']]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, usuario=?, rol=? WHERE id=?");
                    $stmt->execute([$data['nombre'],$data['usuario'],$data['rol'],$data['id']]);
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO usuarios(nombre,usuario,password_hash,rol) VALUES (?,?,?,?)");
                $stmt->execute([$data['nombre'],$data['usuario'],password_hash($data['password'] ?: '123456', PASSWORD_DEFAULT),$data['rol']]);
            }
            json_response(['ok'=>true,'message'=>'Usuario guardado correctamente']);
            break;

        case 'reportes':
            $data = [
                'resumen' => [
                    'ventas_hoy' => fetch_one("SELECT COALESCE(SUM(total),0) total FROM ventas WHERE DATE(fecha)=CURDATE()")['total'],
                    'ventas_mes' => fetch_one("SELECT COALESCE(SUM(total),0) total FROM ventas WHERE YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE())")['total'],
                    'compras_mes' => fetch_one("SELECT COALESCE(SUM(total),0) total FROM compras WHERE YEAR(fecha)=YEAR(CURDATE()) AND MONTH(fecha)=MONTH(CURDATE())")['total'],
                    'utilidad_estimada' => fetch_one("SELECT COALESCE(SUM((vd.precio_unitario - p.precio_compra) * vd.cantidad),0) total FROM venta_detalle vd INNER JOIN productos p ON p.id=vd.producto_id INNER JOIN ventas v ON v.id=vd.venta_id WHERE YEAR(v.fecha)=YEAR(CURDATE()) AND MONTH(v.fecha)=MONTH(CURDATE())")['total'],
                ],
                'top_productos' => fetch_all("SELECT p.nombre, SUM(vd.cantidad) cantidad, SUM(vd.importe) importe FROM venta_detalle vd INNER JOIN productos p ON p.id=vd.producto_id INNER JOIN ventas v ON v.id=vd.venta_id WHERE YEAR(v.fecha)=YEAR(CURDATE()) AND MONTH(v.fecha)=MONTH(CURDATE()) GROUP BY p.id, p.nombre ORDER BY cantidad DESC LIMIT 10"),
                'stock_bajo' => fetch_all("SELECT nombre, codigo_barras, stock, stock_minimo FROM productos WHERE activo=1 AND stock <= stock_minimo ORDER BY stock ASC, nombre ASC"),
                'metodos' => fetch_all("SELECT metodo_pago, COUNT(*) operaciones, SUM(total) total FROM ventas GROUP BY metodo_pago ORDER BY total DESC"),
                'ventas_diarias' => fetch_all("SELECT DATE(fecha) fecha, SUM(total) total FROM ventas WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(fecha) ORDER BY fecha ASC"),
                'categorias_ventas' => fetch_all("SELECT c.nombre, SUM(vd.importe) total FROM venta_detalle vd INNER JOIN productos p ON p.id=vd.producto_id LEFT JOIN categorias c ON c.id=p.categoria_id INNER JOIN ventas v ON v.id=vd.venta_id WHERE YEAR(v.fecha)=YEAR(CURDATE()) AND MONTH(v.fecha)=MONTH(CURDATE()) GROUP BY c.id, c.nombre ORDER BY total DESC"),
                
                // Inteligencia de Negocios
                'business_intelligence' => [
                    'productos_menos_vendidos' => fetch_all("
                        SELECT p.nombre, p.codigo_barras, COALESCE(SUM(vd.cantidad),0) total_vendido
                        FROM productos p
                        LEFT JOIN venta_detalle vd ON vd.producto_id = p.id
                        LEFT JOIN ventas v ON v.id = vd.venta_id AND YEAR(v.fecha) = YEAR(CURDATE()) AND MONTH(v.fecha) = MONTH(CURDATE())
                        WHERE p.activo = 1
                        GROUP BY p.id
                        ORDER BY total_vendido ASC
                        LIMIT 5
                    "),
                    'mejor_cliente' => fetch_one("
                        SELECT c.nombre, COUNT(v.id) compras, SUM(v.total) total_gastado
                        FROM clientes c
                        INNER JOIN ventas v ON v.cliente_id = c.id
                        WHERE YEAR(v.fecha) = YEAR(CURDATE()) AND MONTH(v.fecha) = MONTH(CURDATE())
                        GROUP BY c.id
                        ORDER BY total_gastado DESC
                        LIMIT 1
                    "),
                    'dia_menor_venta' => fetch_one("
                        SELECT DAYNAME(fecha) dia, AVG(total) promedio_venta
                        FROM ventas
                        WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                        GROUP BY DAYOFWEEK(fecha), DAYNAME(fecha)
                        ORDER BY promedio_venta ASC
                        LIMIT 1
                    "),
                    'hora_pico' => fetch_one("
                        SELECT HOUR(fecha) hora, COUNT(*) total_ventas, SUM(total) monto
                        FROM ventas
                        WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        GROUP BY HOUR(fecha)
                        ORDER BY total_ventas DESC
                        LIMIT 1
                    "),
                    'productos_caducidad_proxima' => fetch_all("
                        SELECT nombre, fecha_caducidad, DATEDIFF(fecha_caducidad, CURDATE()) dias_restantes
                        FROM productos
                        WHERE fecha_caducidad IS NOT NULL 
                        AND fecha_caducidad BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)
                        AND activo = 1
                        ORDER BY fecha_caducidad ASC
                        LIMIT 5
                    "),
                    'utilidad_por_categoria' => fetch_all("
                        SELECT c.nombre, SUM((vd.precio_unitario - p.precio_compra) * vd.cantidad) utilidad
                        FROM venta_detalle vd
                        INNER JOIN productos p ON p.id = vd.producto_id
                        LEFT JOIN categorias c ON c.id = p.categoria_id
                        INNER JOIN ventas v ON v.id = vd.venta_id
                        WHERE YEAR(v.fecha) = YEAR(CURDATE()) AND MONTH(v.fecha) = MONTH(CURDATE())
                        GROUP BY c.id
                        ORDER BY utilidad DESC
                    ")
                ]
            ];
            json_response(['ok'=>true,'data'=>$data]);
            break;

        default:
            json_response(['ok'=>false,'message'=>'Acción no válida'], 400);
    }
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    json_response(['ok'=>false,'message'=>$e->getMessage()], 500);
}