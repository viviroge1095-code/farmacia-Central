<?php
require_once __DIR__ . '/helpers.php';
render_header('Buscar Venta', 'buscar_venta');
?>
<section class="panel">
    <div class="panel-header"><h2>Historial de ventas</h2></div>
    <div class="toolbar">
        <input type="date" id="ventaFechaInicio">
        <input type="date" id="ventaFechaFin">
        <input type="text" id="ventaFiltro" placeholder="Folio, cliente o usuario">
        <button type="button" id="buscarVentasBtn">Buscar</button>
    </div>
    <div id="tablaVentas"></div>
</section>
<section class="panel">
    <div class="panel-header"><h2>Detalle de venta</h2></div>
    <div id="detalleVenta"></div>
</section>
<?php render_footer(); ?>
