<?php
/**
 * CEPC · Sitio público
 * includes/secciones/inicio/ayuda.php
 *
 * «Lo que necesitas, a mano»: apuntes, tutorías, bienestar,
 * problemas académicos, mapa y calculadora. Los textos están en
 * includes/contenido.php → contenido_ayuda().
 */

declare(strict_types=1);

$ayuda        = contenido_ayuda();
$tutorias     = datos_tutorias(3);
$apuntes      = datos_apuntes();
$correo       = datos_correo();
$urlApuntes   = (string) sitio('banco_apuntes_url', '');
$instagram    = datos_instagram();
$urlInstagram = datos_url_instagram();
?>
<section class="seccion" id="ayuda" aria-labelledby="ayuda-titulo">
  <div class="contenedor">
    <div class="seccion__cabecera">
      <h2 id="ayuda-titulo" class="seccion__titulo revelar">Lo que necesitas, a mano.</h2>
    </div>

    <div class="bento">
      <article class="bento__celda bento__celda--apuntes revelar">
        <div class="bento__icono"><?= icono('book-open-text') ?></div>
        <h3><?= esc($ayuda['apuntes']['titulo']) ?></h3>
        <p><?= esc($ayuda['apuntes']['texto']) ?></p>
        <?php if ($apuntes['total'] > 0): ?>
          <p class="cifra">
            <span class="cifra__numero"><?= (int) $apuntes['total'] ?></span>
            <span class="cifra__texto">materiales revisados<br>en <?= (int) $apuntes['ramos'] ?> ramos</span>
          </p>
        <?php endif; ?>
        <?php if ($urlApuntes !== ''): ?>
          <a class="boton boton--primario" href="<?= esc($urlApuntes) ?>" rel="noopener" target="_blank">Abrir el banco <?= icono('arrow-up-right') ?></a>
        <?php elseif ($urlInstagram !== ''): ?>
          <a class="enlace-flecha" href="<?= esc($urlInstagram) ?>" rel="noopener" target="_blank">El enlace está en nuestra bio de Instagram <?= icono('arrow-right') ?></a>
        <?php endif; ?>
      </article>

      <article class="bento__celda bento__celda--tutorias revelar">
        <div class="bento__icono"><?= icono('student') ?></div>
        <h3><?= esc($ayuda['tutorias']['titulo']) ?></h3>
        <p><?= esc($ayuda['tutorias']['texto']) ?></p>
        <?php if ($tutorias === []): ?>
          <p class="bento__nota">No hay tutorías abiertas en este momento. Las anunciamos en Instagram apenas se confirman.</p>
        <?php else: ?>
          <ul class="tutorias">
            <?php foreach ($tutorias as $t): $p = fecha_partes($t['fecha']); ?>
              <li>
                <span class="tutorias__ramo" translate="no"><?= esc($t['ramo']) ?></span>
                <span class="tutorias__detalle">
                  <strong><?= esc($t['tipo_texto']) ?></strong>
                  <span><?= esc(ucfirst($p['semana']) . ' ' . $p['dia'] . ' de ' . $p['mes_largo']) ?><?= $t['hora'] ? ', ' . esc(hora_texto($t['hora'])) : '' ?><?= $t['lugar'] ? ' · ' . esc($t['lugar']) : '' ?></span>
                </span>
                <span class="tutorias__cupos"><?= (int) $t['libres'] ?> <?= $t['libres'] === 1 ? 'cupo' : 'cupos' ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </article>

      <article class="bento__celda bento__celda--bienestar revelar">
        <div class="bento__icono"><?= icono('hand-heart') ?></div>
        <h3><?= esc($ayuda['bienestar']['titulo']) ?></h3>
        <p><?= esc($ayuda['bienestar']['texto']) ?></p>
        <?php if ($correo !== ''): ?>
          <a class="boton boton--claro" href="mailto:<?= esc($correo) ?>?subject=<?= rawurlencode('Bienestar: necesito apoyo') ?>">Pedir apoyo</a>
        <?php endif; ?>
      </article>

      <article class="bento__celda bento__celda--docencia revelar">
        <div class="bento__icono"><?= icono('chat-circle-text') ?></div>
        <h3><?= esc($ayuda['docencia']['titulo']) ?></h3>
        <p><?= esc($ayuda['docencia']['texto']) ?></p>
        <?php if ($correo !== ''): ?>
          <a class="enlace-flecha" href="mailto:<?= esc($correo) ?>?subject=<?= rawurlencode('Docencia: caso académico') ?>">Contar un caso <?= icono('arrow-right') ?></a>
        <?php endif; ?>
      </article>

      <a class="bento__celda bento__celda--mapa revelar" href="<?= esc(pagina('mapa')) ?>">
        <div class="bento__mapa" aria-hidden="true"><?= mapa_miniatura_svg() ?></div>
        <div class="bento__icono"><?= icono('map-trifold') ?></div>
        <h3>Mapa del campus</h3>
        <p>Encuentra tu sala, cómo llegar desde la entrada y dónde comer cerca.</p>
        <span class="enlace-flecha">Abrir el mapa <?= icono('arrow-right') ?></span>
      </a>

      <a class="bento__celda bento__celda--notas revelar" href="<?= esc(pagina('calculadora')) ?>">
        <div class="bento__icono"><?= icono('calculator') ?></div>
        <h3>¿Cuánto necesito?</h3>
        <p>Calcula la nota mínima para aprobar con 4,0 o ir a segunda con 3,7.</p>
        <span class="enlace-flecha">Calcular <?= icono('arrow-right') ?></span>
      </a>
    </div>
  </div>
</section>
