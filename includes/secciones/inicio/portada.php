<?php
/**
 * CEPC · Sitio público
 * includes/secciones/inicio/portada.php
 *
 * Banner con el destacado: un evento, una noticia o la última
 * publicación de Instagram (ver datos_destacado() en includes/datos.php).
 */

declare(strict_types=1);

$destacado    = datos_destacado();
$instagram    = datos_instagram();
$urlInstagram = datos_url_instagram();
$fondoPortada = !empty($destacado['imagen']) ? (string) $destacado['imagen'] : 'assets/img/fondo-beauchef.jpg';
?>
<section class="portada" aria-labelledby="portada-titulo">
  <div class="portada__fondo" aria-hidden="true">
    <img src="<?= esc(recurso($fondoPortada)) ?>" alt="" width="960" height="600" fetchpriority="high" decoding="async">
  </div>

  <div class="contenedor portada__grid">
    <?php if ($destacado === null): ?>
      <div class="portada__texto">
        <h1 id="portada-titulo" class="portada__titulo">Hola, Plan Común.</h1>
        <p class="portada__resumen">Fechas, apuntes, tutorías y apoyo para primer y segundo año, en un solo lugar.</p>
        <div class="portada__acciones">
          <a class="boton boton--primario" href="#ayuda">Ver recursos</a>
          <?php if ($urlInstagram !== ''): ?>
            <a class="boton boton--vidrio" href="<?= esc($urlInstagram) ?>" rel="noopener" target="_blank">Seguir en Instagram</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="portada__tarjeta vidrio portada__tarjeta--logo">
        <img class="solo-claro" src="<?= esc(recurso('assets/img/logo-cepc.png')) ?>" alt="" width="280" height="317">
        <img class="solo-oscuro" src="<?= esc(recurso('assets/img/logo-cepc-oscuro.png')) ?>" alt="" width="280" height="317">
      </div>

    <?php else:
      $esEvento = ($destacado['tipo'] ?? '') === 'evento';
      $esPost   = ($destacado['tipo'] ?? '') === 'instagram';
      $fecha    = $destacado['fecha'] ?? null;
      $cercania = $fecha ? fecha_cercania((string) $fecha) : null;
    ?>
      <div class="portada__texto">
        <p class="pildora">
          <?= icono($esPost ? 'instagram-logo' : ($esEvento ? 'calendar-blank' : 'megaphone')) ?>
          <span><?= esc($destacado['etiqueta']) ?></span>
        </p>
        <h1 id="portada-titulo" class="portada__titulo"><?= esc($destacado['titulo']) ?></h1>
        <?php if (trim((string) $destacado['resumen']) !== ''): ?>
          <p class="portada__resumen"><?= esc($destacado['resumen']) ?></p>
        <?php endif; ?>
        <div class="portada__acciones">
          <?php if ($esEvento): ?>
            <a class="boton boton--primario" href="<?= esc(enlace_ics((int) $destacado['id'])) ?>" download>
              <?= icono('calendar-blank') ?> Agregar al calendario
            </a>
            <a class="boton boton--vidrio" href="#agenda">Ver la agenda</a>
          <?php elseif (($destacado['enlace'] ?? '') !== ''): ?>
            <a class="boton boton--primario" href="<?= esc($destacado['enlace']) ?>" rel="noopener" target="_blank">
              <?= esc($destacado['enlace_texto']) ?> <?= icono('arrow-up-right') ?>
            </a>
            <a class="boton boton--vidrio" href="#agenda">Ver la agenda</a>
          <?php else: ?>
            <a class="boton boton--primario" href="#agenda">Ver la agenda</a>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($fecha): $p = fecha_partes((string) $fecha); ?>
        <aside class="portada__tarjeta vidrio" aria-label="Cuándo y dónde">
          <div class="calendario">
            <span class="calendario__semana"><?= esc($p['semana']) ?></span>
            <span class="calendario__dia"><?= esc($p['dia']) ?></span>
            <span class="calendario__mes"><?= esc($p['mes_largo']) ?></span>
          </div>
          <dl class="datos">
            <?php if ($cercania !== null): ?>
              <div><dt><?= icono('hourglass-simple') ?><span class="oculto">Cuándo</span></dt><dd><?= esc($esPost ? 'Publicado ' . mb_strtolower($cercania) : $cercania) ?></dd></div>
            <?php endif; ?>
            <?php if (!empty($destacado['hora'])): ?>
              <div><dt><?= icono('clock') ?><span class="oculto">Hora</span></dt><dd><?= esc(hora_texto($destacado['hora'])) ?></dd></div>
            <?php endif; ?>
            <?php if (!empty($destacado['lugar'])): ?>
              <div><dt><?= icono('map-pin') ?><span class="oculto">Lugar</span></dt><dd><?= esc($destacado['lugar']) ?></dd></div>
            <?php endif; ?>
            <?php if ($esPost && $instagram !== ''): ?>
              <div><dt><?= icono('instagram-logo') ?><span class="oculto">Cuenta</span></dt><dd translate="no">@<?= esc($instagram) ?></dd></div>
            <?php endif; ?>
          </dl>
        </aside>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
