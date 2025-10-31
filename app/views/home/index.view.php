<?php
// VISTA: views/home/index.view.php
// Espera variables desde el controlador:
// $today_sales (float), $today_date (Y-m-d),
// $containerStocks (array de [container, petrol_name, qty_liters, capacity_liters, min_level_liters]),
// $salesLastDays (array de [date, total]),
// $salesFuelToday (array de [petrol, total]),
// $page (string, opcional para navbar)
?>
<h3>Estación de Gasolina</h3>
<hr>

<!-- Alturas fijas para que los charts NO se estiren infinito -->
<style>
  .chart-h-360 { position: relative; height: 360px; }
  .chart-h-220 { position: relative; height: 220px; }
</style>

<div class="row g-3">
  <!-- Tarjeta: Ventas de hoy -->
  <div class="col-12 col-md-4">
    <div class="card shadow-sm">
      <div class="card-body d-flex align-items-center">
        <div class="me-3">
          <span class="fa fa-calendar-day fs-2 text-info"></span>
        </div>
        <div class="flex-grow-1">
          <div class="fs-6 text-muted">Ventas de hoy (<?= htmlspecialchars($today_date) ?>)</div>
          <div class="fs-3 fw-bold">Q <?= number_format((float)$today_sales, 2) ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<hr class="my-3">

<!-- Gráficas -->
<div class="row g-3">
  <!-- Stock por contenedor -->
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-database me-2"></i>Stock por Contenedor (L)</h5>
      </div>
      <div class="card-body">
        <div class="chart-h-360">
          <canvas id="chartContainers"></canvas>
        </div>
        <div class="small text-muted mt-2">
          * Barras en <span style="color:#dc3545;font-weight:bold;">rojo</span> indican contenedores por debajo del mínimo configurado.
        </div>
      </div>
    </div>
  </div>

  <!-- Ventas últimos 7 días -->
  <div class="col-12 col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-chart-line me-2"></i>Ventas últimos 7 días (Q)</h5>
      </div>
      <div class="card-body">
        <div class="chart-h-220">
          <canvas id="chartLast7"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Ventas de hoy por combustible -->
  <div class="col-12 col-lg-6">
    <div class="card shadow-sm h-100">
      <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-chart-pie me-2"></i>Ventas de hoy por combustible (Q)</h5>
      </div>
      <div class="card-body">
        <div class="chart-h-220">
          <canvas id="chartTodayFuel"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  // ====== Datos desde PHP ======
  const stocks = <?= json_encode($containerStocks ?? []) ?>;
  const last7  = <?= json_encode($salesLastDays ?? []) ?>;
  const todayF = <?= json_encode($salesFuelToday ?? []) ?>;

  // Registro global para evitar re-inicializar gráficos si se reinyecta la vista
  window.__dashCharts = window.__dashCharts || {};

  function makeOrReplaceChart(key, ctx, config){
    if (window.__dashCharts[key]) {
      try { window.__dashCharts[key].destroy(); } catch(e){}
    }
    window.__dashCharts[key] = new Chart(ctx, config);
  }

  // ====== Chart 1: Contenedores (barra horizontal) ======
  (function(){
    const ctx = document.getElementById('chartContainers');
    if(!ctx) return;

    const labels = stocks.map(s => `${s.container} (${s.petrol_name})`);
    const qtys   = stocks.map(s => Number(s.qty_liters || 0));
    const mins   = stocks.map(s => Number(s.min_level_liters || 0));

    // Colores por barra (rojo si < mínimo, azul si >= mínimo)
    const colors = qtys.map((q, i) => q < mins[i] ? '#dc3545' : '#0d6efd');

    makeOrReplaceChart('containers', ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Stock (L)',
          data: qtys,
          backgroundColor: colors,
          borderWidth: 0
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false, // clave: el alto lo controla el wrapper
        scales: {
          x: { beginAtZero: true }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              afterBody: function(items){
                if(!items || !items.length) return '';
                const idx = items[0].dataIndex;
                const min = Number(mins[idx] ?? 0);
                const cap = Number(stocks[idx]?.capacity_liters ?? 0);
                const pct = cap>0 ? ((qtys[idx]/cap)*100).toFixed(1) : '0.0';
                return `Mínimo: ${min.toFixed(2)} L\nCapacidad: ${cap.toFixed(2)} L\nOcupación: ${pct}%`;
              }
            }
          }
        }
      }
    });
  })();

  // ====== Chart 2: Ventas últimos 7 días ======
  (function(){
    const ctx = document.getElementById('chartLast7');
    if(!ctx) return;
    const labels = last7.map(r => r.date);
    const data   = last7.map(r => Number(r.total || 0));

    makeOrReplaceChart('last7', ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Total (Q)',
          data,
          tension: 0.25,
          fill: false,
          borderColor: '#20c997',
          pointRadius: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: true } },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  })();

  // ====== Chart 3: Ventas de hoy por combustible ======
  (function(){
    const ctx = document.getElementById('chartTodayFuel');
    if(!ctx) return;
    const labels = todayF.map(r => r.petrol);
    const data   = todayF.map(r => Number(r.total || 0));

    makeOrReplaceChart('todayFuel', ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data,
          backgroundColor: [
            '#0d6efd','#6f42c1','#20c997','#fd7e14',
            '#dc3545','#198754','#0dcaf0','#6c757d'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
      }
    });
  })();
})();
</script>
