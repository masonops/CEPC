<?php
/**
 * CEPC · Sitio público
 * includes/secciones/inicio/agenda.php
 *
 * Próximas fechas, en un carril que se desliza hacia el lado.
 * Las fechas salen del calendario del panel.
 */

declare(strict_types=1);

$agenda       = datos_eventos((int) sitio('agenda_cantidad', 8));
$instagram    = datos_instagram();
$urlInstagram = datos_url_instagram();
?>
<section class="seccion" id="agenda" aria-labelledby="agenda-titulo">
  <div class="contenedor">
    <div class="seccion__cabecera seccion__cabecera--fila">
      <h2 id="agenda-titulo" class="seccion__titulo revelar">Próximas fechas</h2>
      <?php if (count($agenda) > 3): ?>
        <div class="paletas" data-paletas="agenda-carril">
          <button class="paleta" type="button" data-dir="-1" aria-label="Fechas anteriores" disabled><?= icono('arrow-right', 'icono--girado') ?></button>
          <button class="paleta" type="button" data-dir="1" aria-label="Fechas siguientes"><?= icono('arrow-right') ?></button>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($agenda === []): ?>
    <div class="contenedor">
      <div class="vacio revelar">
        <?= icono('calendar-blank') ?>
        <p><strong>Todavía no hay fechas publicadas.</strong>
          <?php if ($urlInstagram !== ''): ?>Las anunciamos primero en <a href="<?= esc($urlInstagram) ?>" rel="noopener" target="_blank">Instagram</a>.<?php endif; ?>
        </p>
      </div>
    </div>
  <?php else: ?>
    <ol class="carril" id="agenda-carril" tabindex="0" aria-label="Próximas fechas, desliza para ver más">
      <?php foreach ($agenda as $i => $ev): $p = fecha_partes($ev['fecha']); $cerca = fecha_cercania($ev['fecha']); ?>
        <li class="fecha revelar" style="--i: <?= (int) $i ?>">
          <div class="fecha__cabeza">
            <div class="fecha__dia">
              <span class="fecha__numero"><?= esc($p['dia']) ?></span>
              <span class="fecha__mes"><?= esc($p['mes']) ?> · <?= esc($p['semana']) ?></span>
            </div>
            <?php if ($cerca !== null): ?><span class="fecha__cerca<?= $cerca === 'Hoy' || $cerca === 'Mañana' ? ' fecha__cerca--pronto' : '' ?>"><?= esc($cerca) ?></span><?php endif; ?>
          </div>
          <p class="fecha__tipo"><?= esc($ev['etiqueta']) ?></p>
          <h3 class="fecha__titulo"><?= esc($ev['titulo']) ?></h3>
          <p class="fecha__meta">
            <?php if (!empty($ev['hora_inicio'])): ?><span><?= icono('clock') ?><?= esc(hora_texto($ev['hora_inicio'])) ?></span><?php endif; ?>
            <?php if (!empty($ev['lugar'])): ?><span><?= icono('map-pin') ?><?= esc($ev['lugar']) ?></span><?php endif; ?>
          </p>
          <a class="fecha__agregar" href="<?= esc(enlace_ics((int) $ev['id'])) ?>" download aria-label="Agregar «<?= esc($ev['titulo']) ?>» al calendario">
            <?= icono('calendar-blank') ?> Agregar
          </a>
        </li>
      <?php endforeach; ?>
    </ol>
  <?php endif; ?>
</section>
