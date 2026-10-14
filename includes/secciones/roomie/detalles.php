<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomie/detalles.php — Todo lo del aviso: cifras, cómo
 * llegar a Beauchef, descripción, qué incluye el precio, comodidades,
 * convivencia, quién publica y consejos.
 */

declare(strict_types=1);

$c = $roomie['cercania'];
$esPieza = $roomie['tipo'] === 'pieza';
$cifras = array_filter([
    ['icono' => $roomie['tipo_datos']['icono'], 'valor' => $roomie['tipo_datos']['nombre'], 'texto' => $esPieza ? 'en depto o casa' : 'para compartir'],
    ['icono' => 'bed',     'valor' => (string) $roomie['dormitorios'], 'texto' => $roomie['dormitorios'] == 1 ? 'dormitorio' : 'dormitorios'],
    ['icono' => 'bathtub', 'valor' => (string) $roomie['banos'], 'texto' => $roomie['banos'] == 1 ? 'baño' : 'baños'],
    $roomie['m2'] ? ['icono' => 'ruler', 'valor' => $roomie['m2'] . ' m²', 'texto' => 'en total'] : null,
    $roomie['piso'] !== null && $roomie['piso'] !== '' ? ['icono' => 'buildings', 'valor' => 'Piso ' . $roomie['piso'], 'texto' => in_array('ascensor', $roomie['comodidades'], true) ? 'con ascensor' : 'sin ascensor'] : null,
    ['icono' => 'users', 'valor' => (string) $roomie['roomies_actuales'], 'texto' => $roomie['roomies_actuales'] == 1 ? 'vive ahí' : 'viven ahí'],
    ['icono' => 'door-open', 'valor' => (string) $roomie['piezas_libres'], 'texto' => $roomie['piezas_libres'] == 1 ? 'pieza libre' : 'piezas libres'],
]);
$rutaTransporte = 'https://www.google.com/maps/dir/?api=1&origin=' . $roomie['punto']['lat'] . ',' . $roomie['punto']['lng']
    . '&destination=' . ROOMIES_BEAUCHEF['lat'] . ',' . ROOMIES_BEAUCHEF['lng'] . '&travelmode=transit';
$parrafos = array_filter(preg_split('/\n{2,}/', trim((string) $roomie['descripcion'])) ?: []);
$datosMapa = [
    'punto'      => $roomie['punto'],
    'beauchef'   => ROOMIES_BEAUCHEF,
    'estaciones' => array_map(static fn(array $e): array => array_intersect_key($e, array_flip(['nombre', 'lineas', 'lat', 'lng'])), $c['estaciones']),
    'lineas'     => ROOMIES_LINEAS,
];
?>
<ul class="ficha-cifras">
  <?php foreach ($cifras as $cifra): ?>
    <li><?= icono($cifra['icono']) ?><strong><?= esc($cifra['valor']) ?></strong><span><?= esc($cifra['texto']) ?></span></li>
  <?php endforeach; ?>
</ul>

<section class="ficha-bloque llegar" aria-labelledby="llegar-titulo">
  <h2 id="llegar-titulo">Cómo llegar a Beauchef</h2>
  <div class="llegar__tarjeta">
    <div class="llegar__mapa" data-mapa-ficha role="region" aria-label="Mapa: zona del aviso y Beauchef 850"></div>
    <div class="llegar__datos">
      <ul class="llegar__tiempos">
        <li><?= icono('person-simple-walk') ?><strong><?= esc(roomies_minutos_texto($c['a_pie'])) ?></strong><span>a pie · <?= esc(roomies_distancia_texto($c['metros'])) ?></span></li>
        <li><?= icono('bicycle') ?><strong><?= esc(roomies_minutos_texto($c['bici'])) ?></strong><span>en bici</span></li>
      </ul>
      <p class="llegar__subtitulo">Metro más cerca</p>
      <ul class="llegar__metros">
        <?php foreach ($c['estaciones'] as $e): ?>
          <li>
            <span class="llegar__lineas"><?= implode('', array_map('roomies_linea_html', $e['lineas'])) ?></span>
            <span class="llegar__estacion"><?= esc($e['nombre']) ?></span>
            <span class="llegar__min"><?= (int) $e['a_pie'] ?> min</span>
          </li>
        <?php endforeach; ?>
      </ul>
      <a class="boton boton--claro llegar__ruta" href="<?= esc($rutaTransporte) ?>" target="_blank" rel="noopener"><?= icono('arrow-square-out') ?>Ruta en transporte público</a>
      <p class="llegar__nota">Tiempos estimados. El punto del mapa está corrido unos 100 m para cuidar la dirección exacta.</p>
    </div>
  </div>
  <script type="application/json" id="datos-ficha-mapa"><?= json_encode($datosMapa, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</section>

<section class="ficha-bloque" aria-labelledby="descripcion-titulo">
  <h2 id="descripcion-titulo">Sobre el lugar</h2>
  <div class="ficha-texto">
    <?php foreach ($parrafos as $p): ?><p><?= nl2br(esc(trim($p))) ?></p><?php endforeach; ?>
  </div>
</section>

<section class="ficha-bloque" aria-labelledby="incluye-titulo">
  <h2 id="incluye-titulo">Qué incluye el precio</h2>
  <ul class="incluye">
    <?php foreach (ROOMIES_INCLUYE as $clave => $item): $si = in_array($clave, $roomie['incluye'], true); ?>
      <li class="<?= $si ? 'incluye--si' : 'incluye--no' ?>">
        <span class="incluye__icono"><?= icono($item['icono']) ?></span>
        <span><?= esc($item['texto']) ?></span>
        <span class="incluye__estado"><?= $si ? icono('check') . '<span class="oculto">incluido</span>' : '<span>Aparte</span>' ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</section>

<?php if ($roomie['comodidades'] !== []): ?>
<section class="ficha-bloque" aria-labelledby="comodidades-titulo">
  <h2 id="comodidades-titulo">Comodidades</h2>
  <div class="comodidades">
    <?php foreach (ROOMIES_GRUPOS_COMODIDAD as $grupo => $nombreGrupo):
        $items = array_filter(ROOMIES_COMODIDADES, static fn(array $c, string $k): bool => $c['grupo'] === $grupo && in_array($k, $roomie['comodidades'], true), ARRAY_FILTER_USE_BOTH);
        if ($items === []) continue; ?>
      <div>
        <h3><?= esc($nombreGrupo) ?></h3>
        <ul>
          <?php foreach ($items as $item): ?><li><?= icono($item['icono']) ?><?= esc($item['texto']) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="ficha-bloque" aria-labelledby="convivencia-titulo">
  <h2 id="convivencia-titulo">Convivencia</h2>
  <ul class="reglas">
    <?php foreach (ROOMIES_REGLAS as $clave => $regla): $valor = (string) $roomie[$clave]; ?>
      <li class="regla regla--<?= esc($clave . '-' . $valor) ?>">
        <span class="regla__icono"><?= icono($regla['icono']) ?></span>
        <span><small><?= esc($regla['titulo']) ?></small><?= esc($regla['valores'][$valor] ?? '') ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</section>

<section class="ficha-bloque" aria-labelledby="quien-titulo">
  <h2 id="quien-titulo">Quién publica</h2>
  <div class="quien">
    <div class="quien__cabeza">
      <?= roomie_avatar($roomie, 'avatar-roomie--grande') ?>
      <div>
        <p class="quien__nombre"><?= esc($roomie['autor_nombre']) ?><?= $roomie['autor_edad'] ? ', ' . (int) $roomie['autor_edad'] : '' ?></p>
        <?php if ($roomie['autor_detalle'] !== ''): ?><p class="quien__detalle"><?= icono('graduation-cap') ?><?= esc($roomie['autor_detalle']) ?></p><?php endif; ?>
        <p class="quien__verificado"><?= icono('seal-check') ?>Verificó su correo de la Universidad</p>
      </div>
    </div>
    <?php if (!empty($roomie['autor_sobre'])): ?>
      <p class="quien__sobre"><?= nl2br(esc((string) $roomie['autor_sobre'])) ?></p>
    <?php endif; ?>
    <?php if ($roomie['habitos'] !== []): ?>
      <ul class="quien__habitos" aria-label="Cómo es en la casa">
        <?php foreach ($roomie['habitos'] as $h): ?><li><?= esc(ROOMIES_HABITOS[$h]) ?></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>

<section class="ficha-bloque ficha-consejos" aria-labelledby="consejos-titulo">
  <h2 id="consejos-titulo"><?= icono('shield-check') ?>Para arrendar tranquilo</h2>
  <ul>
    <li>Visita el lugar y conoce a quienes viven ahí antes de pagar.</li>
    <li>Nunca transfieras la garantía sin un contrato o acuerdo por escrito.</li>
    <li>Pregunta cómo se dividen las cuentas y el aseo; evita sorpresas.</li>
  </ul>
  <details class="reportar">
    <summary><?= icono('flag') ?>Reportar este aviso</summary>
    <?php if ($reporte !== null): ?>
      <p class="formulario__ok"><?= $reporte ? 'Gracias. Lo revisaremos pronto.' : 'No pudimos enviar el reporte. Intenta más tarde.' ?></p>
    <?php elseif (sitio('estatico', false)): ?>
      <p class="formulario__nota">En la vista previa no se envían reportes.</p>
    <?php else: ?>
      <?php require_once SITIO_RAIZ . '/includes/roomies/acciones.php'; ?>
      <form method="post" action="#contactar" class="reportar__form">
        <?= roomies_campos_seguridad() ?>
        <input type="hidden" name="accion" value="reportar">
        <label class="campo"><span>¿Qué pasa con este aviso?</span>
          <textarea name="motivo" rows="3" maxlength="600" required placeholder="Ej.: las fotos no son del lugar, me pidieron plata por adelantado…"></textarea>
        </label>
        <button class="boton boton--claro" type="submit">Enviar reporte</button>
      </form>
    <?php endif; ?>
  </details>
</section>
