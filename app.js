const $ = (sel) => document.querySelector(sel);
const $$ = (sel) => Array.from(document.querySelectorAll(sel));
const money = (n) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(Number(n || 0));
const page = document.body.dataset.page;
let ventaCart = [];
let compraCart = [];
let mediaStream = null;
let scanTimer = null;

async function api(action, options = {}) {
  const config = { headers: {} };
  if (options.method) config.method = options.method;
  if (options.body) {
    config.method = options.method || 'POST';
    config.headers['Content-Type'] = 'application/json';
    config.body = JSON.stringify(options.body);
  }
  if (options.formData) {
    config.method = 'POST';
    config.body = options.formData;
  }
  const res = await fetch(`api.php?action=${action}`, config);
  const data = await res.json();
  if (!data.ok) throw new Error(data.message || 'Error');
  return data.data ?? data;
}

function showMessage(id, text, ok = true) {
  const el = $(id);
  if (!el) return;
  el.innerHTML = `<div style="display:flex; align-items:center; gap:0.5rem;">
    <span class="material-icons-round">${ok ? 'check_circle' : 'error'}</span>
    <span>${text}</span>
  </div>`;
  el.className = `message ${ok ? 'success' : 'error'}`;
  el.style.display = 'block';
  setTimeout(() => {
    el.style.display = 'none';
    el.innerHTML = '';
  }, 4000);
}

function table(headers, rowsHtml) {
  return `<div class="table-container"><table><thead><tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr></thead><tbody>${rowsHtml || `<tr><td colspan="${headers.length}" style="text-align:center; padding: 2rem; color: var(--text-muted);">No se encontraron registros</td></tr>`}</tbody></table></div>`;
}

async function loadCategorias(selectId = '#producto_categoria') {
  const data = await api('list_categorias');
  const sel = $(selectId);
  if (!sel) return data;
  sel.innerHTML = '<option value="">Sin categoría</option>' + data.map(r => `<option value="${r.id}">${r.nombre}</option>`).join('');
  return data;
}

async function loadClientesSelect() {
  const data = await api('list_clientes&q=');
  const sel = $('#ventaCliente');
  if (!sel) return;
  sel.innerHTML = '<option value="">Público general</option>' + data.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
}

async function loadProveedoresSelect() {
  const data = await api('list_proveedores&q=');
  const sel = $('#compraProveedor');
  if (!sel) return;
  sel.innerHTML = '<option value="">Selecciona proveedor</option>' + data.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
}

async function initDashboard() {
  const data = await api('dashboard');
  $('#statVentasDia').textContent = money(data.ventas_dia);
  $('#statComprasMes').textContent = money(data.compras_mes);
  $('#statProductos').textContent = data.productos;
  $('#statStockBajo').textContent = data.stock_bajo;
  
  const stockRows = data.productos_stock_bajo.map(r => `
    <tr>
      <td><div style="display: flex; align-items: center; gap: 0.5rem;">${r.imagen ? `<img src="${r.imagen}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">` : ''}<span>${r.codigo_barras}</span></div></td>
      <td>${r.nombre}</td>
      <td>${r.stock}</td>
      <td>${r.stock_minimo}</td>
    </tr>
  `).join('');
  $('#dashboardStockBajo').innerHTML = table(['Código', 'Producto', 'Stock', 'Mínimo'], stockRows);
  $('#dashboardVentas').innerHTML = table(['Folio', 'Fecha', 'Cliente', 'Total'], data.ultimas_ventas.map(r => `<tr><td>${r.folio}</td><td>${r.fecha}</td><td>${r.cliente}</td><td>${money(r.total)}</td></tr>`).join(''));
}

async function initClientes() {
  const form = $('#formCliente');
  const search = $('#buscarCliente');
  const resetBtn = $('#resetCliente');
  async function load(q = '') {
    const data = await api(`list_clientes&q=${encodeURIComponent(q)}`);
    $('#tablaClientes').innerHTML = table(['Nombre', 'Teléfono', 'Correo', 'Acciones'], data.map(r => `
      <tr>
        <td>${r.nombre}</td>
        <td>${r.telefono || ''}</td>
        <td>${r.correo || ''}</td>
        <td>
          <button class="secondary btn-edit" data-entity="cliente" data-row='${JSON.stringify(r)}'>Editar</button>
          <button class="danger btn-delete" data-action="delete_cliente" data-id="${r.id}">Desactivar</button>
        </td>
      </tr>`).join(''));
    bindEditDelete();
  }
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(form).entries());
    try {
      await api('save_cliente', { body });
      form.reset(); $('#cliente_id').value = '';
      showMessage('#clienteMsg', 'Cliente guardado correctamente');
      load(search.value);
    } catch (err) { showMessage('#clienteMsg', err.message, false); }
  });
  resetBtn.addEventListener('click', () => { form.reset(); $('#cliente_id').value=''; });
  search.addEventListener('input', () => load(search.value));
  load();
}

async function initProveedores() {
  const form = $('#formProveedor');
  const search = $('#buscarProveedor');
  $('#resetProveedor').addEventListener('click', () => { form.reset(); $('#proveedor_id').value=''; });
  async function load(q = '') {
    const data = await api(`list_proveedores&q=${encodeURIComponent(q)}`);
    $('#tablaProveedores').innerHTML = table(['Nombre', 'Contacto', 'Teléfono', 'Acciones'], data.map(r => `
      <tr>
        <td>${r.nombre}</td>
        <td>${r.contacto || ''}</td>
        <td>${r.telefono || ''}</td>
        <td>
          <button class="secondary btn-edit" data-entity="proveedor" data-row='${JSON.stringify(r)}'>Editar</button>
          <button class="danger btn-delete" data-action="delete_proveedor" data-id="${r.id}">Desactivar</button>
        </td>
      </tr>`).join(''));
    bindEditDelete();
  }
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(form).entries());
    try {
      await api('save_proveedor', { body });
      form.reset(); $('#proveedor_id').value='';
      showMessage('#proveedorMsg', 'Proveedor guardado correctamente');
      load(search.value);
    } catch (err) { showMessage('#proveedorMsg', err.message, false); }
  });
  search.addEventListener('input', () => load(search.value));
  load();
}

async function initProductos() {
  await loadCategorias();
  const form = $('#formProducto');
  const search = $('#buscarProducto');
  const imageInput = $('#producto_imagen');
  const imagePreview = $('#imagePreview');
  
  $('#resetProducto').addEventListener('click', () => { 
    form.reset(); 
    $('#producto_id').value='';
    if(imagePreview) imagePreview.style.display = 'none';
  });
  
  // Subir imagen
  if(imageInput) {
    imageInput.addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if(file) {
        const formData = new FormData();
        formData.append('imagen', file);
        try {
          const result = await api('upload_image', { formData });
          $('#imagen_url').value = result.url;
          imagePreview.src = result.url;
          imagePreview.style.display = 'block';
        } catch(err) {
          showMessage('#productoMsg', err.message, false);
        }
      }
    });
  }
  
  async function load(q = '') {
    const data = await api(`list_productos&q=${encodeURIComponent(q)}`);
    $('#tablaProductos').innerHTML = table(['Imagen', 'Código', 'Clave', 'Nombre', 'Categoría', 'Venta', 'Stock', 'Acciones'], data.map(r => `
      <tr>
        <td>${r.imagen ? `<img src="${r.imagen}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">` : '<span style="color:#999;">Sin imagen</span>'}</td>
        <td>${r.codigo_barras}</td>
        <td>${r.clave || ''}</td>
        <td>${r.nombre}</td>
        <td>${r.categoria || ''}</td>
        <td>${money(r.precio_venta)}</td>
        <td>${r.stock}</td>
        <td>
          <button class="secondary btn-edit" data-entity="producto" data-row='${JSON.stringify(r)}'>Editar</button>
          <button class="danger btn-delete" data-action="delete_producto" data-id="${r.id}">Desactivar</button>
        </td>
      </tr>`).join(''));
    bindEditDelete();
  }
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = Object.fromEntries(new FormData(form).entries());
    try {
      await api('save_producto', { body });
      form.reset(); $('#producto_id').value='';
      if(imagePreview) imagePreview.style.display = 'none';
      $('#imagen_url').value = '';
      showMessage('#productoMsg', 'Producto guardado correctamente');
      load(search.value);
    } catch (err) { showMessage('#productoMsg', err.message, false); }
  });
  search.addEventListener('input', () => load(search.value));
  load();
}

function addToVenta(product) {
  const found = ventaCart.find(i => i.id == product.id);
  if (found) {
    if (found.cantidad < found.stock) {
      found.cantidad += 1;
    } else {
      alert('Stock insuficiente para agregar más de este producto');
    }
  } else {
    ventaCart.push({ 
      id: product.id, 
      nombre: product.nombre, 
      codigo_barras: product.codigo_barras, 
      precio_venta: Number(product.precio_venta), 
      cantidad: 1, 
      stock: Number(product.stock),
      imagen: product.imagen
    });
  }
  renderVentaCart();
}

function renderVentaCart() {
  const subtotal = ventaCart.reduce((a, i) => a + i.cantidad * i.precio_venta, 0);
  const descuento = Number($('#ventaDescuento')?.value || 0);
  const total = Math.max(subtotal - descuento, 0);
  
  if ($('#ventaSubtotal')) $('#ventaSubtotal').textContent = money(subtotal);
  if ($('#ventaTotal')) $('#ventaTotal').textContent = money(total);
  
  $('#ticketActual').innerHTML = table(['Producto', 'Cant.', 'Importe', ''], ventaCart.map((i, idx) => `
    <tr>
      <td>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
          ${i.imagen ? `<img src="${i.imagen}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">` : ''}
          <div>
            <div style="font-weight:700;">${i.nombre}</div>
            <div style="font-size:0.75rem; color:var(--text-muted);">${i.codigo_barras}</div>
          </div>
        </div>
      </td>
      <td><input type="number" min="1" max="${i.stock}" value="${i.cantidad}" data-idx="${idx}" class="venta-cantidad" style="width:60px; padding:0.25rem;"></td>
      <td style="font-weight:700;">${money(i.cantidad * i.precio_venta)}</td>
      <td><button class="btn-danger remove-venta" data-idx="${idx}" style="padding:0.4rem;"><span class="material-icons-round" style="font-size: 1.1rem;">delete_outline</span></button></td>
    </tr>`).join(''));
    
  $$('.venta-cantidad').forEach(inp => inp.addEventListener('change', (e) => {
    const idx = Number(e.target.dataset.idx); 
    const v = Math.max(1, Math.min(Number(e.target.value), ventaCart[idx].stock));
    ventaCart[idx].cantidad = v; 
    renderVentaCart();
  }));
  
  $$('.remove-venta').forEach(btn => btn.addEventListener('click', (e) => { 
    const btnTarget = e.target.closest('.remove-venta');
    ventaCart.splice(Number(btnTarget.dataset.idx), 1); 
    renderVentaCart(); 
  }));
}

function printTicket(ventaData, items) {
  const ventana = window.open('', '_blank');
  ventana.document.write(`
    <html>
    <head>
      <title>Ticket de Venta - Farmacia Central</title>
      <style>
        body { font-family: monospace; padding: 20px; max-width: 300px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .logo { font-size: 1.5em; font-weight: bold; }
        .items { width: 100%; margin: 10px 0; }
        .items th, .items td { text-align: left; }
        .total { font-size: 1.2em; font-weight: bold; text-align: right; margin-top: 10px; border-top: 1px dashed #000; padding-top: 10px; }
        .footer { text-align: center; margin-top: 20px; font-size: 0.8em; border-top: 1px dashed #000; padding-top: 10px; }
      </style>
    </head>
    <body>
      <div class="header">
        <div class="logo">🏥 FARMACIA CENTRAL</div>
        <div>Av. Principal #123</div>
        <div>Tel: 961-123-4567</div>
        <div>RFC: FAC-123456</div>
      </div>
      <div>
        <p><strong>Folio:</strong> ${ventaData.folio}</p>
        <p><strong>Fecha:</strong> ${new Date().toLocaleString()}</p>
        <p><strong>Cliente:</strong> ${ventaData.cliente || 'Público general'}</p>
      </div>
      <table class="items">
        <thead><tr><th>Producto</th><th>Cant</th><th>Precio</th><th>Total</th></tr></thead>
        <tbody>
          ${items.map(item => `<tr><td>${item.nombre}</td><td>${item.cantidad}</td><td>${money(item.precio_unitario)}</td><td>${money(item.importe)}</td></tr>`).join('')}
        </tbody>
      </table>
      <div class="total">
        Subtotal: ${money(ventaData.subtotal)}<br>
        Descuento: ${money(ventaData.descuento)}<br>
        <strong>TOTAL: ${money(ventaData.total)}</strong>
      </div>
      <div class="footer">
        <p>¡Gracias por su compra!</p>
        <p>Productos de calidad para tu salud</p>
        <p>No incluye IVA</p>
      </div>
      <script>window.print();setTimeout(()=>window.close(), 1000);<\/script>
    </body>
    </html>
  `);
  ventana.document.close();
}

async function searchVentaProducts(q) {
  const data = await api(`buscar_producto&q=${encodeURIComponent(q)}`);
  $('#ventaResultados').innerHTML = table(['Imagen', 'Código', 'Nombre', 'Precio', 'Stock', ''], data.map(r => `
    <tr>
      <td>${r.imagen ? `<img src="${r.imagen}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 8px;">` : '<span style="color:#999;">-</span>'}</td>
      <td>${r.codigo_barras}</td>
      <td>${r.nombre}</td>
      <td>${money(r.precio_venta)}</td>
      <td>${r.stock}</td>
      <td><button class="btn-add-venta" data-row='${JSON.stringify(r)}'>Agregar</button></td>
    </tr>`).join(''));
  $$('.btn-add-venta').forEach(btn => btn.addEventListener('click', () => {
    addToVenta(JSON.parse(btn.dataset.row));
    $('#ventaBuscarProducto').value = '';
    $('#ventaResultados').innerHTML = '';
    $('#ventaBuscarProducto').focus();
  }));
  
  if (data.length === 1 && (data[0].codigo_barras === q || data[0].clave === q)) {
    addToVenta(data[0]);
    $('#ventaBuscarProducto').value = '';
    $('#ventaResultados').innerHTML = '';
    $('#ventaBuscarProducto').focus();
  }
}

async function initVentas() {
  await loadClientesSelect();
  renderVentaCart();
  $('#ventaBuscarProducto').addEventListener('input', (e) => {
    const q = e.target.value.trim();
    if (q.length >= 2) searchVentaProducts(q);
  });
  $('#ventaDescuento').addEventListener('input', renderVentaCart);
  $('#vaciarTicket').addEventListener('click', () => { ventaCart = []; renderVentaCart(); });
  $('#formVentaFinalizar').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const payload = {
        cliente_id: $('#ventaCliente').value,
        descuento: $('#ventaDescuento').value,
        metodo_pago: $('#ventaMetodoPago').value,
        observaciones: $('#ventaObservaciones').value,
        items: ventaCart.map(item => ({
          id: item.id,
          cantidad: item.cantidad,
          precio_venta: item.precio_venta
        })),
      };
      const result = await api('save_venta', { body: payload });
      showMessage('#ventaMsg', `Venta registrada. Folio: ${result.folio}`);
      
      // Imprimir ticket
      const ventaData = {
        folio: result.folio,
        cliente: $('#ventaCliente option:selected').text,
        subtotal: ventaCart.reduce((a, i) => a + i.cantidad * i.precio_venta, 0),
        descuento: $('#ventaDescuento').value,
        total: ventaCart.reduce((a, i) => a + i.cantidad * i.precio_venta, 0) - $('#ventaDescuento').value
      };
      const items = ventaCart.map(i => ({
        nombre: i.nombre,
        cantidad: i.cantidad,
        precio_unitario: i.precio_venta,
        importe: i.cantidad * i.precio_venta
      }));
      printTicket(ventaData, items);
      
      ventaCart = []; renderVentaCart();
      $('#ventaBuscarProducto').value = ''; $('#ventaResultados').innerHTML = '';
      $('#ventaDescuento').value = '0';
      $('#ventaObservaciones').value = '';
    } catch (err) { showMessage('#ventaMsg', err.message, false); }
  });
  $('#btnScanCamera').addEventListener('click', startCameraScan);
  $('#btnStopCamera').addEventListener('click', stopCameraScan);
}

function addToCompra(product) {
  const found = compraCart.find(i => i.id == product.id);
  if (found) found.cantidad += 1;
  else compraCart.push({ id: product.id, nombre: product.nombre, codigo_barras: product.codigo_barras, precio_compra: Number(product.precio_compra), cantidad: 1 });
  renderCompraCart();
}

function renderCompraCart() {
  const total = compraCart.reduce((a, i) => a + i.cantidad * i.precio_compra, 0);
  $('#compraTotal').textContent = money(total);
  $('#ticketCompra').innerHTML = table(['Código', 'Producto', 'Cant.', 'Costo', 'Importe', ''], compraCart.map((i, idx) => `
    <tr>
      <td>${i.codigo_barras}</td>
      <td>${i.nombre}</td>
      <td><input type="number" min="1" value="${i.cantidad}" data-idx="${idx}" class="compra-cantidad"></td>
      <td><input type="number" min="0.01" step="0.01" value="${i.precio_compra}" data-idx="${idx}" class="compra-precio"></td>
      <td>${money(i.cantidad * i.precio_compra)}</td>
      <td><button class="danger remove-compra" data-idx="${idx}">X</button></td>
    </tr>`).join(''));
  $$('.compra-cantidad').forEach(inp => inp.addEventListener('change', (e) => { compraCart[Number(e.target.dataset.idx)].cantidad = Math.max(1, Number(e.target.value)); renderCompraCart(); }));
  $$('.compra-precio').forEach(inp => inp.addEventListener('change', (e) => { compraCart[Number(e.target.dataset.idx)].precio_compra = Math.max(0.01, Number(e.target.value)); renderCompraCart(); }));
  $$('.remove-compra').forEach(btn => btn.addEventListener('click', (e) => { compraCart.splice(Number(e.target.dataset.idx),1); renderCompraCart(); }));
}

async function searchCompraProducts(q) {
  const data = await api(`buscar_producto&q=${encodeURIComponent(q)}`);
  $('#compraResultados').innerHTML = table(['Código', 'Nombre', 'Costo', 'Stock', ''], data.map(r => `
    <tr>
      <td>${r.codigo_barras}</td>
      <td>${r.nombre}</td>
      <td>${money(r.precio_compra)}</td>
      <td>${r.stock}</td>
      <td><button class="btn-add-compra" data-row='${JSON.stringify(r)}'>Agregar</button></td>
    </tr>`).join(''));
  $$('.btn-add-compra').forEach(btn => btn.addEventListener('click', () => addToCompra(JSON.parse(btn.dataset.row))));
}

async function initCompras() {
  await loadProveedoresSelect();
  renderCompraCart();
  $('#compraBuscarProducto').addEventListener('input', (e) => {
    const q = e.target.value.trim();
    if (q.length >= 2) searchCompraProducts(q);
  });
  $('#vaciarCompra').addEventListener('click', () => { compraCart = []; renderCompraCart(); });
  $('#formCompraFinalizar').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      const payload = {
        proveedor_id: $('#compraProveedor').value,
        factura: $('#compraFactura').value,
        observaciones: $('#compraObservaciones').value,
        items: compraCart,
      };
      const result = await api('save_compra', { body: payload });
      showMessage('#compraMsg', `Compra registrada. Folio: ${result.folio}`);
      compraCart = []; renderCompraCart();
      $('#compraBuscarProducto').value = ''; $('#compraResultados').innerHTML = '';
    } catch (err) { showMessage('#compraMsg', err.message, false); }
  });
}

async function initBuscarVentas() {
  $('#ventaFechaInicio').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10);
  $('#ventaFechaFin').value = new Date().toISOString().slice(0,10);
  async function load() {
    const data = await api(`list_ventas&fi=${$('#ventaFechaInicio').value}&ff=${$('#ventaFechaFin').value}&q=${encodeURIComponent($('#ventaFiltro').value)}`);
    $('#tablaVentas').innerHTML = table(['Folio', 'Fecha', 'Cliente', 'Pago', 'Total', ''], data.map(r => `
      <tr>
        <td>${r.folio}</td>
        <td>${r.fecha}</td>
        <td>${r.cliente}</td>
        <td>${r.metodo_pago}</td>
        <td>${money(r.total)}</td>
        <td><button class="btn-detalle-venta" data-id="${r.id}">Ver detalle</button></td>
      </tr>`).join(''));
    $$('.btn-detalle-venta').forEach(btn => btn.addEventListener('click', async () => {
      const data = await api(`venta_detalle&id=${btn.dataset.id}`);
      const v = data.venta;
      $('#detalleVenta').innerHTML = `
        <div class="detail-box"><p><strong>Folio:</strong> ${v.folio}</p><p><strong>Cliente:</strong> ${v.cliente}</p><p><strong>Fecha:</strong> ${v.fecha}</p><p><strong>Total:</strong> ${money(v.total)}</p></div>
        ${table(['Código','Producto','Cantidad','Precio','Importe'], data.detalle.map(d=>`<tr><td>${d.codigo_barras}</td><td>${d.nombre}</td><td>${d.cantidad}</td><td>${money(d.precio_unitario)}</td><td>${money(d.importe)}</td></tr>`).join(''))}`;
    }));
  }
  $('#buscarVentasBtn').addEventListener('click', load);
  load();
}

async function initBuscarCompras() {
  $('#compraFechaInicio').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10);
  $('#compraFechaFin').value = new Date().toISOString().slice(0,10);
  async function load() {
    const data = await api(`list_compras&fi=${$('#compraFechaInicio').value}&ff=${$('#compraFechaFin').value}&q=${encodeURIComponent($('#compraFiltro').value)}`);
    $('#tablaCompras').innerHTML = table(['Folio', 'Fecha', 'Proveedor', 'Factura', 'Total', ''], data.map(r => `
      <tr>
        <td>${r.folio}</td>
        <td>${r.fecha}</td>
        <td>${r.proveedor}</td>
        <td>${r.factura_referencia || ''}</td>
        <td>${money(r.total)}</td>
        <td><button class="btn-detalle-compra" data-id="${r.id}">Ver detalle</button></td>
      </tr>`).join(''));
    $$('.btn-detalle-compra').forEach(btn => btn.addEventListener('click', async () => {
      const data = await api(`compra_detalle&id=${btn.dataset.id}`);
      const c = data.compra;
      $('#detalleCompra').innerHTML = `
        <div class="detail-box"><p><strong>Folio:</strong> ${c.folio}</p><p><strong>Proveedor:</strong> ${c.proveedor}</p><p><strong>Fecha:</strong> ${c.fecha}</p><p><strong>Total:</strong> ${money(c.total)}</p></div>
        ${table(['Código','Producto','Cantidad','Costo','Importe'], data.detalle.map(d=>`<tr><td>${d.codigo_barras}</td><td>${d.nombre}</td><td>${d.cantidad}</td><td>${money(d.precio_unitario)}</td><td>${money(d.importe)}</td></tr>`).join(''))}`;
    }));
  }
  $('#buscarComprasBtn').addEventListener('click', load);
  load();
}

async function initAdministracion() {
  await loadCategorias();
  async function loadCategoriasTable() {
    const data = await api('list_categorias');
    $('#tablaCategorias').innerHTML = table(['ID', 'Nombre'], data.map(r => `<tr><td>${r.id}</td><td>${r.nombre}</td></tr>`).join(''));
  }
  async function loadUsuarios() {
    const data = await api('list_usuarios');
    $('#tablaUsuarios').innerHTML = table(['Nombre', 'Usuario', 'Rol', 'Estado'], data.map(r => `<tr><td>${r.nombre}</td><td>${r.usuario}</td><td>${r.rol}</td><td>${r.activo == 1 ? 'Activo' : 'Inactivo'}</td></tr>`).join(''));
  }
  $('#formCategoria').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      await api('save_categoria', { body: Object.fromEntries(new FormData(e.target).entries()) });
      e.target.reset();
      showMessage('#categoriaMsg', 'Categoría agregada');
      loadCategoriasTable();
    } catch (err) { showMessage('#categoriaMsg', err.message, false); }
  });
  $('#formUsuario').addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
      await api('save_usuario', { body: Object.fromEntries(new FormData(e.target).entries()) });
      e.target.reset(); $('#usuario_id').value='';
      showMessage('#usuarioMsg', 'Usuario guardado correctamente');
      loadUsuarios();
    } catch (err) { showMessage('#usuarioMsg', err.message, false); }
  });
  $('#resetUsuario').addEventListener('click', () => { $('#formUsuario').reset(); $('#usuario_id').value=''; });
  loadCategoriasTable();
  loadUsuarios();
}

function printReport() {
  const content = document.getElementById('reporteParaImprimir').innerHTML;
  const ventana = window.open('', '_blank');
  ventana.document.write(`
    <html>
    <head>
      <title>Reporte Farmacia Central</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #0ea5e9; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .header { text-align: center; margin-bottom: 20px; }
        .suggestion { background: #e6f7ff; padding: 15px; border-radius: 10px; margin: 10px 0; }
      </style>
    </head>
    <body>
      ${content}
      <script>window.print();setTimeout(()=>window.close(), 1000);<\/script>
    </body>
    </html>
  `);
  ventana.document.close();
}

async function initReportes() {
  const data = await api('reportes');
  const bi = data.business_intelligence;
  
  // Generar sugerencias de inteligencia de negocios
  let suggestionsHtml = '<div class="panel" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;"><div class="panel-header"><h3 style="color: white;">🧠 Inteligencia de Negocios</h3><span class="material-icons-round" style="color: white;">psychology</span></div><div class="suggestions">';
  
  // Productos menos vendidos
  if(bi.productos_menos_vendidos && bi.productos_menos_vendidos.length > 0) {
    suggestionsHtml += `<div class="suggestion-item"><strong>📦 Productos con baja rotación:</strong><br>`;
    bi.productos_menos_vendidos.forEach(p => {
      suggestionsHtml += `• ${p.nombre} (${p.total_vendido} unidades vendidas)<br>`;
    });
    suggestionsHtml += `<span class="suggestion-action">🎯 Sugerencia: Aplicar descuentos del 15-20% en estos productos para aumentar rotación.</span></div>`;
  }
  
  // Mejor cliente
  if(bi.mejor_cliente) {
    suggestionsHtml += `<div class="suggestion-item"><strong>🏆 Cliente más valioso:</strong><br>`;
    suggestionsHtml += `• ${bi.mejor_cliente.nombre} - ${money(bi.mejor_cliente.total_gastado)} gastados (${bi.mejor_cliente.compras} compras)<br>`;
    suggestionsHtml += `<span class="suggestion-action">🎯 Sugerencia: Ofrecer tarjeta VIP con 10% de descuento permanente y promociones exclusivas.</span></div>`;
  }
  
  // Día con menos ventas
  if(bi.dia_menor_venta) {
    suggestionsHtml += `<div class="suggestion-item"><strong>📊 Día con menor actividad:</strong><br>`;
    suggestionsHtml += `• ${bi.dia_menor_venta.dia} (promedio: ${money(bi.dia_menor_venta.promedio_venta)})<br>`;
    suggestionsHtml += `<span class="suggestion-action">🎯 Sugerencia: Implementar "Martes de descuentos" u ofertas especiales para reactivar las ventas.</span></div>`;
  }
  
  // Hora pico
  if(bi.hora_pico) {
    suggestionsHtml += `<div class="suggestion-item"><strong>⏰ Horario de mayor demanda:</strong><br>`;
    suggestionsHtml += `• ${bi.hora_pico.hora}:00 hrs (${bi.hora_pico.total_ventas} ventas)<br>`;
    suggestionsHtml += `<span class="suggestion-action">🎯 Sugerencia: Asegurar suficiente personal en caja durante este horario.</span></div>`;
  }
  
  // Productos próximos a caducar
  if(bi.productos_caducidad_proxima && bi.productos_caducidad_proxima.length > 0) {
    suggestionsHtml += `<div class="suggestion-item"><strong>⚠️ Productos próximos a caducar:</strong><br>`;
    bi.productos_caducidad_proxima.forEach(p => {
      suggestionsHtml += `• ${p.nombre} - ${p.dias_restantes} días restantes<br>`;
    });
    suggestionsHtml += `<span class="suggestion-action">🎯 Sugerencia: Crear paquetes promocionales o "2x1" para liquidar inventario próximo a caducar.</span></div>`;
  }
  
  // Utilidad por categoría
  if(bi.utilidad_por_categoria && bi.utilidad_por_categoria.length > 0) {
    suggestionsHtml += `<div class="suggestion-item"><strong>💰 Categorías más rentables:</strong><br>`;
    bi.utilidad_por_categoria.slice(0,3).forEach(c => {
      suggestionsHtml += `• ${c.nombre || 'Sin categoría'}: ${money(c.utilidad)} utilidad<br>`;
    });
    suggestionsHtml += `<span class="suggestion-action">🎯 Sugerencia: Invertir más en publicidad y stock de estas categorías.</span></div>`;
  }
  
  suggestionsHtml += '</div></div>';
  
  $('#reporteResumen').innerHTML = `
    <div class="stats-grid">
      <div class="stat-card"><h3>Ventas de hoy</h3><strong>${money(data.resumen.ventas_hoy)}</strong></div>
      <div class="stat-card"><h3>Ventas del mes</h3><strong>${money(data.resumen.ventas_mes)}</strong></div>
      <div class="stat-card"><h3>Compras del mes</h3><strong>${money(data.resumen.compras_mes)}</strong></div>
      <div class="stat-card"><h3>Utilidad estimada</h3><strong>${money(data.resumen.utilidad_estimada)}</strong></div>
    </div>
    ${suggestionsHtml}
  `;
  $('#reporteTopProductos').innerHTML = table(['Producto', 'Cantidad', 'Importe'], data.top_productos.map(r => `<tr><td>${r.nombre}</td><td>${r.cantidad}</td><td>${money(r.importe)}</td></tr>`).join(''));
  $('#reporteStockBajo').innerHTML = table(['Código', 'Producto', 'Stock', 'Mínimo'], data.stock_bajo.map(r => `<tr><td>${r.codigo_barras}</td><td>${r.nombre}</td><td>${r.stock}</td><td>${r.stock_minimo}</td></tr>`).join(''));

  new Chart($('#chartVentasDiarias'), {
    type: 'line',
    data: {
      labels: data.ventas_diarias.map(v => v.fecha),
      datasets: [{ label: 'Ventas ($)', data: data.ventas_diarias.map(v => v.total), borderColor: '#2563eb', fill: true, backgroundColor: 'rgba(37, 99, 235, 0.1)', tension: 0.4 }]
    }
  });

  new Chart($('#chartCategorias'), {
    type: 'doughnut',
    data: {
      labels: data.categorias_ventas.map(c => c.nombre || 'Sin categoría'),
      datasets: [{ data: data.categorias_ventas.map(c => c.total), backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#6366f1', '#8b5cf6'] }]
    }
  });

  new Chart($('#chartMetodos'), {
    type: 'pie',
    data: {
      labels: data.metodos.map(m => m.metodo_pago),
      datasets: [{ data: data.metodos.map(m => m.total), backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'] }]
    }
  });
  
  // Botón imprimir
  const printBtn = document.createElement('button');
  printBtn.innerHTML = '<span class="material-icons-round">print</span> Imprimir Reporte';
  printBtn.className = 'btn-primary';
  printBtn.style.marginBottom = '1rem';
  printBtn.onclick = printReport;
  document.querySelector('#reporteResumen').parentNode.insertBefore(printBtn, document.querySelector('#reporteResumen'));
}

function bindEditDelete() {
  $$('.btn-delete').forEach(btn => btn.onclick = async () => {
    if (!confirm('¿Deseas desactivar este registro?')) return;
    const body = { id: btn.dataset.id };
    await fetch(`api.php?action=${btn.dataset.action}`, { method: 'POST', body: new URLSearchParams(body) });
    location.reload();
  });
  $$('.btn-edit').forEach(btn => btn.onclick = () => {
    const row = JSON.parse(btn.dataset.row);
    if (btn.dataset.entity === 'cliente') fillForm('#formCliente', row, '#cliente_id');
    if (btn.dataset.entity === 'proveedor') fillForm('#formProveedor', row, '#proveedor_id');
    if (btn.dataset.entity === 'producto') fillForm('#formProducto', row, '#producto_id');
  });
}

function fillForm(formSel, row, hiddenSel) {
  const form = $(formSel);
  Object.entries(row).forEach(([k, v]) => {
    const field = form.querySelector(`[name="${k}"]`);
    if (field) field.value = v ?? '';
  });
  if ($(hiddenSel)) $(hiddenSel).value = row.id;
  if ($('#imagePreview') && row.imagen) {
    $('#imagePreview').src = row.imagen;
    $('#imagePreview').style.display = 'block';
    $('#imagen_url').value = row.imagen;
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function startCameraScan() {
  const wrap = $('#cameraWrap');
  const video = $('#cameraPreview');
  wrap.classList.remove('hidden');
  try {
    mediaStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
    video.srcObject = mediaStream;
    if ('BarcodeDetector' in window) {
      const detector = new BarcodeDetector({ formats: ['ean_13', 'ean_8', 'code_128', 'qr_code'] });
      scanTimer = setInterval(async () => {
        try {
          const codes = await detector.detect(video);
          if (codes.length) {
            $('#ventaBuscarProducto').value = codes[0].rawValue;
            searchVentaProducts(codes[0].rawValue);
            stopCameraScan();
          }
        } catch (e) {}
      }, 700);
    } else {
      showMessage('#ventaMsg', 'Tu navegador no soporta BarcodeDetector. Usa lector USB tipo teclado.', false);
    }
  } catch (err) {
    showMessage('#ventaMsg', 'No se pudo acceder a la cámara.', false);
  }
}

function stopCameraScan() {
  if (scanTimer) clearInterval(scanTimer);
  if (mediaStream) mediaStream.getTracks().forEach(t => t.stop());
  mediaStream = null;
  $('#cameraWrap')?.classList.add('hidden');
}

(async function init() {
  try {
    if (page === 'index') await initDashboard();
    if (page === 'clientes') await initClientes();
    if (page === 'productos') await initProductos();
    if (page === 'ventas') await initVentas();
    if (page === 'buscar_venta') await initBuscarVentas();
    if (page === 'compras') await initCompras();
    if (page === 'buscar_compra') await initBuscarCompras();
    if (page === 'proveedores') await initProveedores();
    if (page === 'administracion') await initAdministracion();
    if (page === 'reportes') await initReportes();
  } catch (err) {
    console.error(err);
  }
})();