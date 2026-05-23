<?php
require_once __DIR__ . '/helpers.php';
render_header('Dashboard', 'index');
?>

<div class="stats-grid" id="dashboardStats">
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: #eff6ff; color: var(--primary);">
            <span class="material-icons-round">payments</span>
        </div>
        <div class="stat-info">
            <h4>Ventas del día</h4>
            <strong id="statVentasDia">$0.00</strong>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: #ecfdf5; color: var(--secondary);">
            <span class="material-icons-round">shopping_bag</span>
        </div>
        <div class="stat-info">
            <h4>Compras del mes</h4>
            <strong id="statComprasMes">$0.00</strong>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: #f8fafc; color: var(--text-muted);">
            <span class="material-icons-round">inventory</span>
        </div>
        <div class="stat-info">
            <h4>Productos activos</h4>
            <strong id="statProductos">0</strong>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background: #fef2f2; color: var(--danger);">
            <span class="material-icons-round">notification_important</span>
        </div>
        <div class="stat-info">
            <h4>Stock bajo</h4>
            <strong id="statStockBajo">0</strong>
        </div>
    </div>
</div>

<div class="grid-2">
    <section class="panel">
        <div class="panel-header">
            <h3>Inventario Crítico</h3>
            <span class="badge badge-danger">Atención requerida</span>
        </div>
        <div id="dashboardStockBajo"></div>
    </section>
    <section class="panel">
        <div class="panel-header">
            <h3>Actividad Reciente</h3>
            <span class="badge badge-success">En vivo</span>
        </div>
        <div id="dashboardVentas"></div>
    </section>
</div>

<?php render_footer(); ?>
