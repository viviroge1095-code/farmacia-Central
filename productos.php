<?php
require_once __DIR__ . '/helpers.php';
// No incluir check_auth.php porque helpers.php ya maneja la autenticación
render_header('Gestión de Inventario', 'productos');
?>

<div class="grid-2">
    <section class="panel">
        <div class="panel-header">
            <h3>Registrar Nuevo Producto</h3>
            <span class="material-icons-round" style="color: var(--primary);">add_box</span>
        </div>
        <form id="formProducto" class="form-grid">
            <input type="hidden" name="id" id="producto_id">
            <input type="hidden" name="imagen" id="imagen_url">
            <label>Código de Barras<input type="text" name="codigo_barras" required placeholder="Escanear o digitar"></label>
            <label>Clave Interna<input type="text" name="clave" placeholder="SKU-001"></label>
            <label style="grid-column: span 2;">Nombre del Producto<input type="text" name="nombre" required placeholder="Ej. Paracetamol 500mg"></label>
            <label>Categoría<select name="categoria_id" id="producto_categoria"></select></label>
            <label>Unidad de Medida<input type="text" name="unidad" value="PZA" placeholder="PZA, CAJA, ML"></label>
            <label>Precio Compra ($)<input type="number" step="0.01" name="precio_compra" required></label>
            <label>Precio Venta ($)<input type="number" step="0.01" name="precio_venta" required></label>
            <label>Stock Actual<input type="number" step="1" name="stock" required></label>
            <label>Stock Mínimo (Alerta)<input type="number" step="1" name="stock_minimo" value="5"></label>
            <label style="grid-column: span 2;">Fecha de Caducidad<input type="date" name="fecha_caducidad"></label>
            <label style="grid-column: span 2;">
                Imagen del Producto
                <input type="file" id="producto_imagen" accept="image/*">
                <div style="margin-top: 0.5rem;">
                    <img id="imagePreview" style="max-width: 100px; max-height: 100px; border-radius: 8px; display: none;">
                </div>
            </label>
            
            <div class="form-actions" style="margin-top: 1rem;">
                <button type="submit" class="btn-primary" style="flex: 2;">
                    <span class="material-icons-round">save</span> GUARDAR PRODUCTO
                </button>
                <button type="button" class="btn-secondary" id="resetProducto" style="flex: 1;">
                    <span class="material-icons-round">refresh</span>
                </button>
            </div>
        </form>
        
        <div style="margin-top: 2rem; padding: 1.25rem; background: #f0f9ff; border-radius: var(--radius-md); border: 1px dashed var(--primary);">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <span class="material-icons-round" style="color: var(--primary);">qr_code_scanner</span>
                <h4 style="margin: 0; font-size: 0.9rem; color: var(--primary);">Prueba de Lector USB</h4>
            </div>
            <input type="text" id="barcodeTest" placeholder="Haz clic aquí y escanea un código" style="background: white;">
        </div>
        <div id="productoMsg" class="message"></div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h3>Listado de Existencias</h3>
            <div style="position: relative; width: 250px;">
                <span class="material-icons-round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.1rem;">search</span>
                <input type="text" id="buscarProducto" placeholder="Filtrar inventario..." style="padding-left: 2.2rem; font-size: 0.85rem;">
            </div>
        </div>
        <div id="tablaProductos"></div>
    </section>
</div>

<?php render_footer(); ?>