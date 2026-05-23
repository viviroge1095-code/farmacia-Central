<?php
require_once __DIR__ . '/helpers.php';
render_header('Punto de Venta', 'ventas');
?>

<div class="grid-2-ventas">
    <section class="panel">
        <div class="panel-header">
            <h3>Terminal de Venta</h3>
            <div style="display: flex; gap: 0.5rem;">
                <button type="button" id="btnScanCamera" class="btn-secondary" style="padding: 0.5rem 1rem;">
                    <span class="material-icons-round">videocam</span> Cámara
                </button>
            </div>
        </div>
        
        <div class="form-grid" style="margin-bottom: 1.5rem;">
            <label>
                Cliente
                <select id="ventaCliente"></select>
            </label>
            <label>
                Escanear / Buscar Producto
                <div style="position: relative;">
                    <span class="material-icons-round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.2rem;">search</span>
                    <input type="text" id="ventaBuscarProducto" placeholder="Código, nombre..." style="padding-left: 2.5rem;">
                </div>
            </label>
        </div>

        <div id="cameraWrap" class="camera-wrap hidden" style="margin-bottom: 1rem; border-radius: var(--radius-md); overflow: hidden; border: 2px solid var(--primary);">
            <video id="cameraPreview" autoplay playsinline style="width: 100%; height: 200px; object-fit: cover;"></video>
            <button type="button" id="btnStopCamera" class="btn-danger" style="width: 100%; border-radius: 0;">Detener Cámara</button>
        </div>

        <div id="ventaResultados"></div>
    </section>

    <section class="panel" style="border-top: 4px solid var(--primary);">
        <div class="panel-header">
            <h3>Ticket de Venta</h3>
            <span class="material-icons-round" style="color: var(--primary);">receipt_long</span>
        </div>
        
        <div id="ticketActual" style="min-height: 200px;"></div>
        
        <form id="formVentaFinalizar" style="margin-top: 1.5rem;">
            <div class="form-grid compact" style="gap: 0.75rem;">
                <label>Descuento ($)<input type="number" id="ventaDescuento" step="0.01" min="0" value="0"></label>
                <label>Método de Pago
                    <select id="ventaMetodoPago">
                        <option value="EFECTIVO">💵 Efectivo</option>
                        <option value="TARJETA">💳 Tarjeta</option>
                        <option value="TRANSFERENCIA">🏦 Transferencia</option>
                    </select>
                </label>
                <label>Notas / Observaciones<textarea id="ventaObservaciones" style="min-height: 60px;"></textarea></label>
            </div>

            <div class="totals" style="background: #f8fafc; border: 1px solid var(--border); padding: 1.5rem; border-radius: var(--radius-md); margin: 1.5rem 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--text-muted); font-weight: 600;">
                    <span>Subtotal</span>
                    <span id="ventaSubtotal">$0.00</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 1.5rem; font-weight: 800; color: var(--text-main);">
                    <span>Total</span>
                    <span id="ventaTotal" style="color: var(--primary);">$0.00</span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                <button type="submit" class="btn-primary" style="padding: 1rem;">
                    <span class="material-icons-round">check_circle</span> FINALIZAR VENTA
                </button>
                <button type="button" class="btn-danger" id="vaciarTicket">
                    <span class="material-icons-round">delete_sweep</span>
                </button>
            </div>
        </form>
        <div id="ventaMsg" class="message"></div>
    </section>
</div>

<?php render_footer(); ?>