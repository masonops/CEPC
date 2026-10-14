<?php
/**
 * CEPC · Sitio público
 * includes/secciones/transparencia/resumen.php
 *
 * Balance del año y en qué se gasta: barras horizontales de una sola serie
 * (un solo color), ordenadas de mayor a menor, con el monto al final de la
 * barra. Al pasar el mouse o con el teclado se ve el porcentaje.
 */

declare(strict_types=1);

$resumen    = transparencia_resumen($anioCuentas);
$categorias = transparencia_gastos_por_categoria($anioCuentas);
$mayor      = $categorias[0]['total'] ?? 0;
?>
<section class="seccion" id="cuentas" aria-labelledby="cuentas-titulo">
  <div class="contenedor">
    <h2 id="cuentas-titulo" class="seccion__titulo">Cuentas <?= esc($anioCuentas) ?></h2>
    <?php if ($resumen['ultima']): ?>
      <p class="seccion__bajada">Actualizado con los movimientos hasta el <?= esc(fecha_larga((string) $resumen['ultima'])) ?>.</p>
    <?php endif; ?>

    <?php if ($resumen['movimientos'] === 0): ?>
      <div class="vacio"><?= icono('receipt') ?><p><strong>Aún no hay movimientos publicados este año.</strong></p></div>
    <?php else: ?>
      <div class="balance">
        <dl class="balance__cifras">
          <div><dt>Ingresos</dt><dd><?= esc(pesos($resumen['ingresos'])) ?></dd></div>
          <div><dt>Gastos</dt><dd><?= esc(pesos($resumen['gastos'])) ?></dd></div>
          <div class="balance__saldo"><dt>Saldo</dt><dd><?= esc(pesos($resumen['saldo'])) ?></dd></div>
        </dl>

        <figure class="grafico-barras">
          <figcaption>En qué se gastó</figcaption>
          <ul>
            <?php foreach ($categorias as $c): $pct = $resumen['gastos'] > 0 ? $c['total'] / $resumen['gastos'] * 100 : 0; ?>
              <li class="barra-fila" tabindex="0" data-pista="<?= esc(number_format($pct, 1, ',', '.')) ?>% del gasto del año">
                <span class="barra-fila__nombre"><?= esc($c['categoria']) ?></span>
                <span class="barra-fila__pista">
                  <span class="barra-fila__barra" style="--v: <?= $mayor > 0 ? round($c['total'] / $mayor, 4) : 0 ?>"></span>
                  <span class="barra-fila__valor"><?= esc(pesos($c['total'])) ?></span>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        </figure>
      </div>
    <?php endif; ?>
  </div>
</section>
