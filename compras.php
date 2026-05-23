<?php
require_once __DIR__ . '/helpers.php';
render_header('Compras', 'compras');
?>
<section class="grid-2-ventas">
    <section class="panel">
        <div class="panel-header"><h2>Nueva compra</h2></div>
        <div class="venta-tools">
            <label>Proveedor<select id="compraProveedor"></select></label>
            <label>Buscar producto<input type="text" id="compraBuscarProducto" placeholder="Código, clave o nombre"></label>
        </div>
        <div id="compraResultados"></div>
    </section>
    <section class="panel">
        <div class="panel-header"><h2>Orden actual</h2></div>
        <div id="ticketCompra"></div>
        <form id="formCompraFinalizar" class="form-grid compact">
            <label>Factura / referencia<input type="text" id="compraFactura"></label>
            <label>Observaciones<textarea id="compraObservaciones"></textarea></label>
            <div class="totals">
                <div>Total compra: <strong id="compraTotal">$0.00</strong></div>
            </div>
            <div class="form-actions">
                <button type="submit">Registrar compra</button>
                <button type="button" class="danger" id="vaciarCompra">Vaciar</button>
            </div>
        </form>
        <div id="compraMsg" class="message"></div>
    </section>
</section>
<?php render_footer(); ?>
