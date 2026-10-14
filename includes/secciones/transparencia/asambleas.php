<?php
/**
 * CEPC · Sitio público
 * includes/secciones/transparencia/asambleas.php
 *
 * Asambleas convocadas y realizadas, con asistencia, quórum y acta.
 * El acta se sube en el panel (Secretaría → Asambleas → enlace del acta).
 */

declare(strict_types=1);

$asambleas = transparencia_asambleas();
$hoy = date('Y-m-d');
?>
<section class="seccion" id="asambleas" aria-labelledby="asambleas-titulo">
  <div class="contenedor">
    <h2 id="asambleas-titulo" class="seccion__titulo">Asambleas</h2>
    <p class="seccion__bajada">Son abiertas a todo Plan Común. Aquí quedan la asistencia y el acta de cada una.</p>

    <?php if ($asambleas === []): ?>
      <div class="vacio"><?= icono('users-three') ?><p><strong>Todavía no hay asambleas registradas.</strong></p></div>
    <?php else: ?>
      <ol class="asambleas">
        <?php foreach ($asambleas as $a): $f = fecha_partes((string) $a['fecha']); $futura = $a['fecha'] >= $hoy; ?>
          <li class="asamblea<?= $futura ? ' asamblea--proxima' : '' ?>">
            <div class="asamblea__fecha">
              <strong><?= esc($f['dia']) ?></strong>
              <span><?= esc($f['mes']) ?></span>
            </div>
            <div class="asamblea__texto">
              <h3><?= esc($a['titulo']) ?></h3>
              <p>
                <?= esc(implode(' · ', array_filter([ucfirst($f['semana']), hora_texto($a['hora']), $a['lugar']]))) ?>
              </p>
              <?php if (!$futura && (int) $a['quorum_requerido'] > 0): ?>
                <p class="asamblea__asistencia">
                  <?= (int) $a['asistentes'] ?> asistentes · quórum <?= (int) $a['quorum_requerido'] ?>
                </p>
              <?php endif; ?>
            </div>
            <div class="asamblea__acciones">
              <span class="asamblea__estado asamblea__estado--<?= esc($a['estado']) ?>"><?= esc($a['estado_texto']) ?></span>
              <?php if (!empty($a['acta_url'])): ?>
                <a class="enlace-flecha" href="<?= esc($a['acta_url']) ?>" rel="noopener" target="_blank">Ver acta <?= icono('arrow-up-right') ?></a>
              <?php elseif (!$futura && $a['estado'] === 'realizada'): ?>
                <span class="asamblea__sin-acta">Acta en preparación</span>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </div>
</section>
