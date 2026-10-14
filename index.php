<?php
/**
 * CEPC · Sitio público
 * index.php — Portada.
 *
 * Orden de la página:
 *   1. Banner con el destacado (evento, noticia o publicación de Instagram)
 *   2. Próximas fechas
 *   3. Lo que necesitas (apuntes, tutorías, bienestar, docencia)
 *   4. Novedades de Instagram
 *   5. Quiénes somos y la directiva
 *   6. Preguntas frecuentes
 */

declare(strict_types=1);

require __DIR__ . '/includes/arranque.php';

$destacado     = datos_destacado();
$agenda        = datos_eventos((int) sitio('agenda_cantidad', 8));
$tutorias      = datos_tutorias(3);
$apuntes       = datos_apuntes();
$publicaciones = datos_publicaciones(4);
$directiva     = datos_directiva();
$preguntas     = contenido_preguntas();
$ayuda         = contenido_ayuda();

$instagram    = datos_instagram();
$urlInstagram = $instagram !== '' ? 'https://www.instagram.com/' . rawurlencode($instagram) . '/' : '';
$correo       = datos_correo();
$urlApuntes   = (string) sitio('banco_apuntes_url', '');

$fondoPortada = !empty($destacado['imagen']) ? (string) $destacado['imagen'] : 'assets/img/fondo-beauchef.jpg';

$titulo      = sitio('sigla') . ' · ' . sitio('nombre');
$descripcion = 'Fechas, apuntes, tutorías y apoyo para Plan Común de la FCFM, Universidad de Chile.';

require __DIR__ . '/includes/partes/encabezado.php';
?>

<main id="contenido">

  <!-- 1. Banner ------------------------------------------------------------>
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

  <!-- 2. Próximas fechas --------------------------------------------------->
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

  <!-- 3. Lo que necesitas -------------------------------------------------->
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
      </div>
    </div>
  </section>

  <!-- 4. Novedades -------------------------------------------------------->
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

  <!-- 5. Quiénes somos ----------------------------------------------------->
  <section class="seccion" id="directiva" aria-labelledby="directiva-titulo">
    <div class="contenedor somos">
      <figure class="somos__foto revelar">
        <img src="<?= esc(recurso('assets/img/beauchef-851.jpg')) ?>" alt="Entrada del edificio de Beauchef 851, con su fachada de vidrio y árboles en primer plano." width="880" height="1100" loading="lazy" decoding="async">
      </figure>

      <div class="somos__texto">
        <h2 id="directiva-titulo" class="oculto">Quiénes somos</h2>
        <p class="declaracion revelar">
          <strong>Somos el Centro de Estudiantes de Plan Común.</strong>
          Representamos a primer y segundo año ante la Escuela y la Facultad, organizamos actividades y te acompañamos cuando algo no anda bien.
        </p>

        <?php if ($directiva !== []): ?>
          <h3 class="somos__subtitulo revelar">Directiva <?= esc(date('Y')) ?></h3>
          <ul class="personas">
            <?php foreach ($directiva as $i => $persona): ?>
              <li class="persona revelar" style="--i: <?= (int) $i ?>">
                <span class="avatar avatar--<?= esc($persona['color'] ?: 'azul') ?>">
                  <?php if ($persona['foto']): ?>
                    <img src="<?= esc($persona['foto']) ?>" alt="" width="46" height="46" loading="lazy" decoding="async">
                  <?php else: ?>
                    <?= esc(iniciales_de($persona['nombre'])) ?>
                  <?php endif; ?>
                </span>
                <span class="persona__texto">
                  <strong><?= esc($persona['nombre']) ?></strong>
                  <span><?= esc($persona['cargo']) ?></span>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- 6. Preguntas frecuentes --------------------------------------------->
  <section class="seccion" id="preguntas" aria-labelledby="preguntas-titulo">
    <div class="contenedor contenedor--angosto">
      <div class="seccion__cabecera seccion__cabecera--centro">
        <h2 id="preguntas-titulo" class="seccion__titulo revelar">Preguntas frecuentes</h2>
      </div>
      <div class="preguntas">
        <?php foreach ($preguntas as $pregunta): ?>
          <details class="pregunta revelar">
            <summary>
              <span><?= esc($pregunta['pregunta']) ?></span>
              <?= icono('caret-down', 'pregunta__flecha') ?>
            </summary>
            <div class="pregunta__respuesta"><p><?= $pregunta['respuesta'] ?></p></div>
          </details>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

</main>

<?php require __DIR__ . '/includes/partes/pie.php'; ?>
