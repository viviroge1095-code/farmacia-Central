<?php
require_once __DIR__ . '/helpers.php';
render_header('Clientes', 'clientes');
?>
<section class="grid-2">
    <section class="panel">
        <div class="panel-header"><h2>Registrar cliente</h2></div>
        <form id="formCliente" class="form-grid">
            <input type="hidden" name="id" id="cliente_id">
            <label>Nombre completo<input type="text" name="nombre" required></label>
            <label>Teléfono<input type="text" name="telefono"></label>
            <label>Correo<input type="email" name="correo"></label>
            <label>Dirección<input type="text" name="direccion"></label>
            <label>Notas<textarea name="notas"></textarea></label>
            <div class="form-actions">
                <button type="submit">Guardar cliente</button>
                <button type="button" class="secondary" id="resetCliente">Limpiar</button>
            </div>
        </form>
        <div id="clienteMsg" class="message"></div>
    </section>
    <section class="panel">
        <div class="panel-header"><h2>Listado de clientes</h2></div>
        <input type="text" id="buscarCliente" placeholder="Buscar cliente por nombre o teléfono">
        <div id="tablaClientes"></div>
    </section>
</section>
<?php render_footer(); ?>
