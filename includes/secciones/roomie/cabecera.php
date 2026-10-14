<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomie/cabecera.php — Migas, título, lugar y botones
 * para compartir y guardar.
 */

declare(strict_types=1);

$publicado = fecha_cercania((string) $roomie['publicado_en']) ?? ucfirst(fecha_larga((string) $roomie['publicado_en']));
?>
<div class="contenedor ficha-roomie__cabecera">
  <nav class="migas" aria-label="Estás en">
    <a href="<?= esc(pagina('roomies')) ?>">Roomies</a>
    <?= icono('caret-right') ?>
    <span><?= esc($roomie['comuna']) ?></span>
    <?= icono('caret-right') ?>
    <span aria-current="page"><?= esc($roomie['tipo_datos']['nombre']) ?></span>
  </nav>

  <div class="ficha-roomie__titular">
    <div>
      <h1 class="ficha-roomie__titulo"><?= esc($roomie['titulo']) ?></h1>
      <p class="ficha-roomie__lugar">
        <span><?= icono('map-pin') ?><?= esc(trim(($roomie['calle'] ? $roomie['calle'] . ', ' : '') . $roomie['comuna'])) ?></span>
        <span class="ficha-roomie__verificado"><?= icono('seal-check') ?>Correo U. de Chile verificado</span>
        <span class="ficha-roomie__fecha">Publicado <?= esc(mb_strtolower($publicado)) ?></span>
      </p>
    </div>
    <div class="ficha-roomie__botones">
      <button class="boton-texto" type="button" data-compartir data-titulo="<?= esc($roomie['titulo']) ?>"><?= icono('share-network') ?><span data-compartir-texto>Compartir</span></button>
      <button class="boton-texto" type="button" data-guardar="<?= (int) $roomie['id'] ?>" data-guardar-largo aria-pressed="false"><?= icono('heart', 'corazon-vacio') ?><?= icono('heart-fill', 'corazon-lleno') ?><span data-guardar-texto>Guardar</span></button>
    </div>
  </div>
</div>
