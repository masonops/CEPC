<?php
/**
 * CEPC · Sitio público
 * includes/secciones/articulo/cuerpo.php
 *
 * Título, fecha y texto de la publicación ($articulo, de articulo.php).
 * El texto llega como texto simple desde el panel y se convierte con
 * articulo_html(): párrafos, «## subtítulos» y listas con «- ».
 */

declare(strict_types=1);
?>
<article class="articulo">
  <div class="contenedor contenedor--texto">
    <a class="articulo__volver enlace-flecha" href="<?= esc(pagina('articulos')) ?>"><?= icono('arrow-left') ?><?= es_lista() ? 'Propuestas y artículos' : 'Publicaciones' ?></a>

    <?php if ($articulo === null): ?>
      <h1 class="articulo__titulo">No encontramos esta publicación</h1>
      <p class="articulo__resumen">Puede que el enlace esté mal o que la hayamos sacado. Revisa las demás aquí abajo.</p>
    <?php else: ?>
      <header class="articulo__cabecera">
        <span class="etiqueta-articulo etiqueta-articulo--<?= esc($articulo['categoria']) ?>"><?= esc($articulo['categoria_texto']) ?></span>
        <h1 class="articulo__titulo"><?= esc($articulo['titulo']) ?></h1>
        <?php if (!empty($articulo['resumen'])): ?>
          <p class="articulo__resumen"><?= esc($articulo['resumen']) ?></p>
        <?php endif; ?>
        <p class="articulo__datos">
          <time datetime="<?= esc($articulo['fecha']) ?>"><?= esc(ucfirst(fecha_larga($articulo['fecha']))) ?></time>
          <?php if (!empty($articulo['autor'])): ?><span><?= esc($articulo['autor']) ?></span><?php endif; ?>
        </p>
      </header>

      <?php if (!empty($articulo['imagen'])): ?>
        <img class="articulo__imagen" src="<?= esc($articulo['imagen']) ?>" alt="" decoding="async">
      <?php endif; ?>

      <div class="articulo__texto">
        <?= articulo_html((string) $articulo['cuerpo']) ?>
      </div>
    <?php endif; ?>
  </div>
</article>
