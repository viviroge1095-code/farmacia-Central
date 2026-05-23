<?php
// NO poner session_start() aquí porque ya se inicia en check_auth.php
// Solo si no está incluido check_auth, entonces iniciamos sesión
if (basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'api.php') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function render_header(string $title, string $page): void {
    $items = [
        'index' => ['Dashboard', 'dashboard', 'index.php'],
        'ventas' => ['Nueva Venta', 'point_of_sale', 'ventas.php'],
        'productos' => ['Inventario', 'inventory_2', 'productos.php'],
        'clientes' => ['Clientes', 'people', 'clientes.php'],
        'compras' => ['Compras', 'shopping_cart', 'compras.php'],
        'reportes' => ['Reportes', 'bar_chart', 'reportes.php'],
        'buscar_venta' => ['Historial Ventas', 'receipt', 'buscar_venta.php'],
        'buscar_compra' => ['Historial Compras', 'history_edu', 'buscar_compra.php'],
        'proveedores' => ['Proveedores', 'local_shipping', 'proveedores.php'],
        'administracion' => ['Configuración', 'settings', 'administracion.php'],
    ];
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - Farmacia Central</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body data-page="<?= e($page) ?>">
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-box">
                <span class="material-icons-round">medical_services</span>
            </div>
            <div class="logo-text">
                <h1>FARMACIA</h1>
                <p>CENTRAL PRO</p>
            </div>
        </div>
        <nav class="sidebar-nav">
            <?php foreach ($items as $key => [$label, $icon, $href]): ?>
                <a class="nav-link <?= $key === $page ? 'active' : '' ?>" href="<?= e($href) ?>">
                    <span class="material-icons-round"><?= $icon ?></span>
                    <span><?= e($label) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div style="padding: 1.5rem; border-top: 1px solid var(--border);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name'] ?? 'Usuario') ?>&background=0ea5e9&color=fff" style="width: 38px; height: 38px; border-radius: 10px;">
                    <div style="overflow: hidden;">
                        <p style="margin: 0; font-size: 0.85rem; font-weight: 700; color: var(--text-main);"><?= e($_SESSION['user_name'] ?? 'Usuario') ?></p>
                        <p style="margin: 0; font-size: 0.7rem; color: var(--text-muted);"><?= e($_SESSION['user_rol'] ?? '') ?></p>
                    </div>
                </div>
                <a href="logout.php" style="color: var(--danger); text-decoration: none;" title="Cerrar sesión">
                    <span class="material-icons-round">logout</span>
                </a>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="page-header">
            <div class="page-title">
                <h2><?= e($title) ?></h2>
                <p>Bienvenido de nuevo al sistema de gestión.</p>
            </div>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div style="background: white; padding: 0.5rem 1rem; border-radius: var(--radius-md); border: 1px solid var(--border); font-size: 0.85rem; font-weight: 700;">
                    <span class="material-icons-round" style="font-size: 1rem; vertical-align: middle; margin-right: 0.5rem; color: var(--primary);">calendar_today</span>
                    <?= date('d M, Y') ?>
                </div>
            </div>
        </header>
<?php
}

function render_footer(): void {
    ?>
    </main>
    <script src="app.js"></script>
</body>
</html>
<?php
}