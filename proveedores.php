<?php
require_once __DIR__ . '/helpers.php';
render_header('Proveedores', 'proveedores');
?>
<section class="grid-2">
    <section class="panel">
        <div class="panel-header"><h2>Registrar proveedor</h2></div>
        <form id="formProveedor" class="form-grid">
            <input type="hidden" name="id" id="proveedor_id">
            <label>Nombre / razón social<input type="text" name="nombre" required></label>
            <label>Contacto<input type="text" name="contacto"></label>
            <label>Teléfono<input type="text" name="telefono"></label>
            <label>Correo<input type="email" name="correo"></label>
            <label>Dirección<input type="text" name="direccion"></label>
            <label>Notas<textarea name="notas"></textarea></label>
            <div class="form-actions">
                <button type="submit">Guardar proveedor</button>
                <button type="button" class="secondary" id="resetProveedor">Limpiar</button>
            </div>
        </form>
        <div id="proveedorMsg" class="message"></div>
    </section>
    <section class="panel">
        <div class="panel-header"><h2>Listado de proveedores</h2></div>
        <input type="text" id="buscarProveedor" placeholder="Buscar proveedor">
        <div id="tablaProveedores"></div>
    </section>
</section>
<?php render_footer(); ?>
