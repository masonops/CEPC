<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomie/galeria.php — Una foto grande y cuatro chicas;
 * cualquiera abre el visor (assets/js/roomie.js). En el teléfono es un
 * carrusel que se desliza con el dedo.
 */

declare(strict_types=1);

$fotos = $roomie['fotos'];
?>
<?php if ($fotos !== []): ?>
<div class="contenedor">
  <div class="galeria<?= count($fotos) < 5 ? ' galeria--' . count($fotos) : '' ?>" data-galeria>
    <?php foreach (array_slice($fotos, 0, 5) as $i => $f): ?>
      <button class="galeria__foto" type="button" data-abrir-foto="<?= $i ?>" aria-label="Ver foto <?= $i + 1 ?> de <?= count($fotos) ?>">
        <img src="<?= esc($i === 0 ? $f['g'] : $f['m']) ?>" alt="" <?= $i === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?> decoding="async">
      </button>
    <?php endforeach; ?>
    <button class="galeria__todas" type="button" data-abrir-foto="0"><?= icono('images') ?>Ver las <?= count($fotos) ?> fotos</button>
    <span class="galeria__contador" aria-hidden="true" data-galeria-contador>1 / <?= count($fotos) ?></span>
  </div>
</div>

<dialog class="visor" data-visor aria-label="Fotos del aviso">
  <div class="visor__barra">
    <span class="visor__contador" data-visor-contador>1 / <?= count($fotos) ?></span>
    <button class="visor__cerrar" type="button" data-visor-cerrar><?= icono('x') ?><span class="oculto">Cerrar</span></button>
  </div>
  <div class="visor__pista" data-visor-pista>
    <?php foreach ($fotos as $i => $f): ?>
      <figure class="visor__foto">
        <img src="<?= esc($f['g']) ?>" alt="Foto <?= $i + 1 ?> de <?= count($fotos) ?>" loading="lazy" decoding="async">
        <?php if ($f['credito'] !== ''): ?><figcaption><?= esc($f['credito']) ?></figcaption><?php endif; ?>
      </figure>
    <?php endforeach; ?>
  </div>
  <button class="visor__flecha visor__flecha--antes" type="button" data-visor-paso="-1"><?= icono('caret-left') ?><span class="oculto">Foto anterior</span></button>
  <button class="visor__flecha visor__flecha--despues" type="button" data-visor-paso="1"><?= icono('caret-right') ?><span class="oculto">Foto siguiente</span></button>
</dialog>
<?php endif; ?>
