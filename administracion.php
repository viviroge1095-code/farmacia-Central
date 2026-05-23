<?php
require_once __DIR__ . '/helpers.php';
render_header('Administración', 'administracion');
?>
<section class="grid-2">
    <section class="panel">
        <div class="panel-header"><h2>Usuarios del sistema</h2></div>
        <form id="formUsuario" class="form-grid">
            <input type="hidden" name="id" id="usuario_id">
            <label>Nombre<input type="text" name="nombre" required></label>
            <label>Usuario<input type="text" name="usuario" required></label>
            <label>Rol<select name="rol"><option>ADMIN</option><option>CAJERO</option><option>ALMACEN</option></select></label>
            <label>Contraseña<input type="password" name="password" placeholder="Solo llena para crear o cambiar"></label>
            <div class="form-actions">
                <button type="submit">Guardar usuario</button>
                <button type="button" class="secondary" id="resetUsuario">Limpiar</button>
            </div>
        </form>
        <div id="usuarioMsg" class="message"></div>
    </section>
    <section class="panel">
        <div class="panel-header"><h2>Categorías</h2></div>
        <form id="formCategoria" class="toolbar-form">
            <input type="text" name="nombre" placeholder="Nueva categoría" required>
            <button type="submit">Agregar</button>
        </form>
        <div id="categoriaMsg" class="message"></div>
        <div id="tablaCategorias"></div>
        <hr>
        <h3>Usuarios registrados</h3>
        <div id="tablaUsuarios"></div>
    </section>
</section>
<?php render_footer(); ?>
