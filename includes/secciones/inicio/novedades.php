<?php
/**
 * CEPC · Sitio público
 * includes/secciones/inicio/novedades.php
 *
 * Lo último publicado en Instagram (panel → Comunicaciones,
 * publicaciones en estado «Publicado»).
 */

declare(strict_types=1);

$publicaciones = datos_publicaciones(4);
$instagram     = datos_instagram();
$urlInstagram  = datos_url_instagram();
?>
<section class="seccion" id="novedades" aria-labelledby="novedades-titulo">
  <div class="contenedor novedades">
    <div class="novedades__cuenta revelar">
      <h2 id="novedades-titulo" class="seccion__titulo">Lo último que publicamos.</h2>
      <p class="seccion__bajada">Avisos, fechas y actividades salen primero en Instagram.</p>
      <?php if ($urlInstagram !== ''): ?>
        <a class="cuenta" href="<?= esc($urlInstagram) ?>" rel="noopener" target="_blank">
          <span class="cuenta__logo">
            <img class="solo-claro" src="<?= esc(recurso('assets/img/marca-cepc.png')) ?>" alt="" width="40" height="47">
            <img class="solo-oscuro" src="<?= esc(recurso('assets/img/marca-cepc-oscuro.png')) ?>" alt="" width="40" height="47">
          </span>
          <span class="cuenta__texto">
            <strong translate="no">@<?= esc($instagram) ?></strong>
            <span>Seguir en Instagram</span>
          </span>
          <?= icono('arrow-up-right', 'cuenta__flecha') ?>
        </a>
      <?php endif; ?>
    </div>

    <?php if ($publicaciones === []): ?>
      <div class="vacio revelar">
        <?= icono('instagram-logo') ?>
        <p><strong>Aún no hay publicaciones para mostrar.</strong> Vuelve pronto.</p>
      </div>
    <?php else: ?>
      <ol class="publicaciones">
        <?php foreach ($publicaciones as $i => $pub): $cerca = fecha_cercania($pub['fecha_publicacion']); $p = fecha_partes($pub['fecha_publicacion']); ?>
          <li class="revelar" style="--i: <?= (int) $i ?>">
            <a class="publicacion" href="<?= esc($urlInstagram ?: '#novedades') ?>"<?= $urlInstagram ? ' rel="noopener" target="_blank"' : '' ?>>
              <time class="publicacion__fecha" datetime="<?= esc($pub['fecha_publicacion']) ?>"><?= esc($cerca ?? ($p['dia'] . ' de ' . $p['mes_largo'])) ?></time>
              <span class="publicacion__titulo"><?= esc($pub['titulo']) ?></span>
              <?php if (trim((string) $pub['copy_texto']) !== ''): ?>
                <span class="publicacion__texto"><?= esc($pub['copy_texto']) ?></span>
              <?php endif; ?>
              <?= icono('arrow-up-right', 'publicacion__flecha') ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </div>
</section>
