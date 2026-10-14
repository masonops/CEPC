<?php
/**
 * CEPC · Sitio público
 * includes/partes/pie.php — Contacto, créditos, el buscador y el cierre del
 * documento. Común a todas las páginas.
 */

declare(strict_types=1);

$instagram    = datos_instagram();
$urlInstagram = datos_url_instagram();
$correo       = datos_correo();
?>
<footer class="pie" id="contacto">
  <div class="contenedor pie__grid">
    <div class="pie__marca">
      <?php if (es_lista()): ?>
        <span class="marca__sello" translate="no" aria-hidden="true"><?= esc(sitio('lista')['nombre']) ?></span>
        <p><strong translate="no"><?= esc(sitio('lista')['nombre']) ?></strong> · <?= esc(sitio('lista')['lema']) ?><br><?= esc(sitio('lista')['descripcion']) ?>, <?= esc(sitio('facultad')) ?></p>
      <?php else: ?>
        <img class="solo-claro" src="<?= esc(recurso('assets/img/marca-cepc.png')) ?>" alt="" width="34" height="40" loading="lazy">
        <img class="solo-oscuro" src="<?= esc(recurso('assets/img/marca-cepc-oscuro.png')) ?>" alt="" width="34" height="40" loading="lazy">
        <p><strong><?= esc(sitio('nombre')) ?></strong><br><?= esc(sitio('facultad')) ?></p>
      <?php endif; ?>
    </div>

    <div class="pie__columna">
      <h2>Contacto</h2>
      <ul>
        <?php if ($correo !== ''): ?>
          <li><a href="mailto:<?= esc($correo) ?>"><?= icono('envelope-simple') ?><?= esc($correo) ?></a></li>
        <?php endif; ?>
        <?php if ($urlInstagram !== ''): ?>
          <li><a href="<?= esc($urlInstagram) ?>" rel="noopener" target="_blank"><?= icono('instagram-logo') ?><span translate="no">@<?= esc($instagram) ?></span></a></li>
        <?php endif; ?>
        <li><span><?= icono('map-pin') ?><?= esc(sitio('direccion')) ?></span></li>
      </ul>
    </div>

    <div class="pie__columna">
      <h2>Sitio</h2>
      <ul>
        <li><a href="<?= esc(pagina('articulos')) ?>"><?= es_lista() ? 'Propuestas' : 'Publicaciones' ?></a></li>
        <li><a href="<?= esc(pagina('calendario')) ?>">Calendario</a></li>
        <li><a href="<?= esc(pagina('mapa')) ?>">Mapa del campus</a></li>
        <li><a href="<?= esc(pagina('calculadora')) ?>">Calculadora de notas</a></li>
        <li><a href="<?= esc(pagina('transparencia')) ?>">Transparencia</a></li>
        <li><a href="<?= esc(pagina('inicio', 'preguntas')) ?>">Preguntas frecuentes</a></li>
      </ul>
    </div>
  </div>

  <div class="contenedor pie__legal">
    <p>© <?= esc(date('Y')) ?> <span translate="no"><?= esc(marca()) ?></span>. Hecho por y para estudiantes.<?php if (es_lista()): ?> Este no es el sitio oficial del CEPC.<?php endif; ?></p>
    <p>
      Foto de Beauchef 851: <a href="https://commons.wikimedia.org/wiki/File:Beauchef_851,_FCFM_Universidad_de_Chile,_Santiago_20231215.jpg" rel="noopener" target="_blank">Carlos yo</a>,
      <a href="https://creativecommons.org/licenses/by-sa/4.0/deed.es" rel="noopener license" target="_blank">CC BY-SA 4.0</a>, recortada.
    </p>
    <?php if (sitio('panel_url', '') !== ''): ?>
      <p><a class="pie__panel" href="<?= esc(sitio('panel_url')) ?>"><?= icono('lock-simple') ?><?= es_lista() ? 'Acceso equipo' : 'Acceso directiva' ?></a></p>
    <?php endif; ?>
  </div>
</footer>

<?php require SITIO_RAIZ . '/includes/partes/buscador.php'; ?>

</body>
</html>
