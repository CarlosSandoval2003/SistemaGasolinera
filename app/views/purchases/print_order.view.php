<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Orden de Compra <?= htmlspecialchars($h['codigo']) ?></title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <style>
    body { padding: 16px; }
    .doc-title { text-align:center; margin-bottom:12px; }
    .meta div { line-height: 1.4; }
    @media print {
      .no-print { display:none !important; }
    }
  </style>
</head>
<body>
  <h4 class="doc-title">Orden de Compra</h4>

  <div class="meta mb-2">
    <div><strong>OC:</strong> <?= htmlspecialchars($h['codigo']) ?></div>
    <div><strong>Proveedor:</strong> <?= htmlspecialchars($h['supplier_name'] ?? '') ?></div>
    <div><strong>Fecha:</strong> <?= htmlspecialchars($h['fecha']) ?></div>
    <div><strong>Estatus:</strong> <?= htmlspecialchars($h['estatus']) ?></div>
  </div>

  <table class="table table-sm table-bordered">
    <thead>
      <tr>
        <th>#</th>
        <th>Gasolina</th>
        <th class="text-end">Litros</th>
        <th class="text-end">Costo (Q/L)</th>
        <th class="text-end">Total línea (Q)</th>
      </tr>
    </thead>
    <tbody>
      <?php $i=1; $subtotal=0; foreach($items as $it):
        $line = (float)$it['cantidad_litros'] * (float)$it['precio_unitario']; $subtotal += $line; ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($it['petrol_name']) ?></td>
          <td class="text-end"><?= number_format((float)$it['cantidad_litros'],3) ?></td>
          <td class="text-end"><?= number_format((float)$it['precio_unitario'],4) ?></td>
          <td class="text-end"><?= number_format($line,2) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr><th colspan="4" class="text-end">SUBTOTAL</th><th class="text-end"><?= number_format($subtotal,2) ?></th></tr>
      <tr><th colspan="4" class="text-end">IMPUESTOS</th><th class="text-end"><?= number_format((float)($h['impuestos'] ?? 0),2) ?></th></tr>
      <tr><th colspan="4" class="text-end">TOTAL</th><th class="text-end"><?= number_format((float)($h['total'] ?? $subtotal),2) ?></th></tr>
    </tfoot>
  </table>

  <div class="no-print text-end mt-3">
    <button onclick="window.print()" class="btn btn-success btn-sm"><i class="fa fa-print"></i> Imprimir</button>
  </div>

  <script>
    // Imprime automáticamente (como el original)
    window.addEventListener('load', () => {
      window.print();
      setTimeout(()=> window.close(), 300);
    });
  </script>
</body>
</html>
