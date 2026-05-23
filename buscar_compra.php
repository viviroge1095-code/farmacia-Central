<?php
require_once __DIR__ . '/helpers.php';
render_header('Buscar Compra', 'buscar_compra');
?>
<section class="panel">
    <div class="panel-header"><h2>Historial de compras</h2></div>
    <div class="toolbar">
        <input type="date" id="compraFechaInicio">
        <input type="date" id="compraFechaFin">
        <input type="text" id="compraFiltro" placeholder="Folio, proveedor o factura">
        <button type="button" id="buscarComprasBtn">Buscar</button>
    </div>
    <div id="tablaCompras"></div>
</section>
<section class="panel">
    <div class="panel-header"><h2>Detalle de compra</h2></div>
    <div id="detalleCompra"></div>
</section>
<?php render_footer(); ?>
