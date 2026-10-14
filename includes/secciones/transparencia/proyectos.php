<?php
/**
 * CEPC · Sitio público
 * includes/secciones/transparencia/proyectos.php
 *
 * Cada proyecto del año con su presupuesto y cuánto se ha gastado (medidor:
 * la pista es un tono más claro del mismo azul).
 */

declare(strict_types=1);

$proyectos = transparencia_proyectos($anioCuentas);
?>
<section class="seccion" id="proyectos" aria-labelledby="proyectos-titulo">
  <div class="contenedor">
    <h2 id="proyectos-titulo" class="seccion__titulo">Proyectos</h2>
    <p class="seccion__bajada">Lo que aprobamos hacer este año y cuánto va gastado de cada presupuesto.</p>

    <?php if ($proyectos === []): ?>
      <div class="vacio"><?= icono('chart-bar') ?><p><strong>No hay proyectos cargados para <?= esc($anioCuentas) ?>.</strong></p></div>
    <?php else: ?>
      <ul class="proyectos">
        <?php foreach ($proyectos as $p):
          $pct = $p['presupuesto'] > 0 ? $p['gastado'] / $p['presupuesto'] * 100 : 0;
          $excedido = $pct > 100.5;
        ?>
          <li class="proyecto">
            <div class="proyecto__cabeza">
              <h3><?= esc($p['nombre']) ?></h3>
              <span class="proyecto__estado"><?= esc($p['estado_texto']) ?></span>
            </div>
            <?php if ($p['descripcion']): ?><p class="proyecto__descripcion"><?= esc($p['descripcion']) ?></p><?php endif; ?>
            <div class="medidor" role="meter" aria-valuemin="0" aria-valuemax="<?= (int) round($p['presupuesto']) ?>"
                 aria-valuenow="<?= (int) round(min($p['gastado'], $p['presupuesto'])) ?>"
                 aria-label="Gastado de <?= esc($p['nombre']) ?>">
              <span class="medidor__relleno<?= $excedido ? ' medidor__relleno--excedido' : '' ?>" style="width: <?= round(min($pct, 100), 2) ?>%"></span>
            </div>
            <p class="proyecto__cifras">
              <strong><?= esc(pesos($p['gastado'])) ?></strong> de <?= esc(pesos($p['presupuesto'])) ?>
              <span>· <?= esc(number_format($pct, 0, ',', '.')) ?>%</span>
              <?php if ($excedido): ?>
                <span class="proyecto__alerta"><?= icono('warning-circle') ?>Sobre el presupuesto</span>
              <?php endif; ?>
            </p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
