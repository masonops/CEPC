<?php
/**
 * CEPC · Sitio público
 * includes/secciones/transparencia/votaciones.php
 *
 * Resultados de las votaciones cerradas: barras de una sola serie con el
 * número de votos y el porcentaje al final de cada barra.
 */

declare(strict_types=1);

$votaciones = transparencia_votaciones();
?>
<section class="seccion" id="votaciones" aria-labelledby="votaciones-titulo">
  <div class="contenedor">
    <h2 id="votaciones-titulo" class="seccion__titulo">Votaciones</h2>
    <p class="seccion__bajada">Resultados de plebiscitos y consultas, una vez cerradas.</p>

    <?php if ($votaciones === []): ?>
      <div class="vacio"><?= icono('check-square') ?><p><strong>Aún no hay votaciones cerradas.</strong></p></div>
    <?php else: ?>
      <div class="votaciones">
        <?php foreach ($votaciones as $v): $mayor = max(array_map(static fn(array $o): int => (int) $o['votos'], $v['opciones']) ?: [0]); ?>
          <article class="votacion">
            <h3><?= esc($v['titulo']) ?></h3>
            <p class="votacion__meta">
              Cerró el <?= esc(fecha_larga((string) ($v['fecha_fin'] ?: $v['fecha_inicio']))) ?>
              · <?= (int) $v['total'] ?> votos<?php if ((int) $v['padron'] > 0): ?>
              · participó el <?= esc(number_format($v['total'] / (int) $v['padron'] * 100, 0, ',', '.')) ?>% del padrón<?php endif; ?>
            </p>
            <ul class="grafico-barras grafico-barras--votos">
              <?php foreach ($v['opciones'] as $o): $pct = $v['total'] > 0 ? (int) $o['votos'] / $v['total'] * 100 : 0; ?>
                <li class="barra-fila" tabindex="0" data-pista="<?= (int) $o['votos'] ?> votos">
                  <span class="barra-fila__nombre"><?= esc($o['texto']) ?></span>
                  <span class="barra-fila__pista">
                    <span class="barra-fila__barra" style="--v: <?= $mayor > 0 ? round((int) $o['votos'] / $mayor, 4) : 0 ?>"></span>
                    <span class="barra-fila__valor"><?= esc(number_format($pct, 0, ',', '.')) ?>%</span>
                  </span>
                </li>
              <?php endforeach; ?>
            </ul>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
