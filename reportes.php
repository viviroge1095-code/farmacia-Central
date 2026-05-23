<?php
require_once __DIR__ . '/helpers.php';
render_header('Reportes Avanzados', 'reportes');
?>

<div style="margin-bottom: 1rem; display: flex; gap: 1rem; justify-content: flex-end;">
    <button onclick="imprimirReporte()" class="btn-primary" style="background: #10b981;">
        <span class="material-icons-round">print</span> Imprimir Reporte Completo
    </button>
</div>

<div id="reporteParaImprimir">
    <div class="panel" style="background: linear-gradient(to right, #ffffff, #f8fafc);">
        <div class="panel-header">
            <h3>Resumen de Rendimiento</h3>
            <span class="material-icons-round" style="color: var(--primary);">analytics</span>
        </div>
        <div id="reporteResumen"></div>
    </div>

    <div class="grid-2">
        <section class="panel">
            <div class="panel-header">
                <h3>Tendencia de Ventas</h3>
                <p style="font-size: 0.75rem; color: var(--text-muted);">Últimos 30 días</p>
            </div>
            <div style="height: 300px;">
                <canvas id="chartVentasDiarias"></canvas>
            </div>
        </section>
        
        <section class="panel">
            <div class="panel-header">
                <h3>Distribución por Categoría</h3>
            </div>
            <div style="height: 300px; display: flex; justify-content: center;">
                <canvas id="chartCategorias"></canvas>
            </div>
        </section>
    </div>

    <div class="grid-2">
        <section class="panel">
            <div class="panel-header">
                <h3>Métodos de Pago Preferidos</h3>
            </div>
            <div style="height: 300px; display: flex; justify-content: center;">
                <canvas id="chartMetodos"></canvas>
            </div>
        </section>

        <section class="panel">
            <div class="panel-header">
                <h3>Productos Top Ventas</h3>
            </div>
            <div id="reporteTopProductos"></div>
        </section>
    </div>

    <section class="panel">
        <div class="panel-header">
            <h3>Alertas de Stock Bajo</h3>
        </div>
        <div id="reporteStockBajo"></div>
    </section>
</div>

<script>
function imprimirReporte() {
    const contenido = document.getElementById('reporteParaImprimir').innerHTML;
    const ventana = window.open('', '_blank');
    ventana.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Reporte Farmacia Central</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { font-family: Arial, sans-serif; padding: 20px; }
                h1 { color: #0ea5e9; text-align: center; margin-bottom: 20px; }
                h2 { color: #333; margin: 15px 0 10px 0; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background: #f0f0f0; }
                .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
                .stat-card { background: #f8fafc; padding: 15px; border-radius: 10px; text-align: center; }
                .stat-card h3 { font-size: 0.85rem; color: #666; }
                .stat-card strong { font-size: 1.5rem; color: #0ea5e9; }
                .suggestions { background: #e6f7ff; padding: 15px; border-radius: 10px; margin: 20px 0; }
                .suggestion-item { margin: 10px 0; padding: 10px; background: white; border-radius: 8px; }
                @media print {
                    body { padding: 0; }
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <h1>🏥 FARMACIA CENTRAL</h1>
            <p style="text-align: center;">Reporte Generado: ${new Date().toLocaleString()}</p>
            ${contenido}
            <p style="text-align: center; margin-top: 30px; color: #666;">© Farmacia Central - Sistema de Gestión Profesional</p>
        </body>
        </html>
    `);
    ventana.document.close();
    setTimeout(() => { ventana.print(); }, 500);
}
</script>

<?php render_footer(); ?>