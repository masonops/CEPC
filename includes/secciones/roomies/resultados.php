<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies/resultados.php — Tarjetas de los avisos y mapa.
 * En computador el mapa queda fijo a la derecha; en el teléfono se cambia
 * entre «Lista» y «Mapa».
 *
 * El mapa (assets/js/roomies-mapa.js) usa Leaflet con mapas de CARTO y
 * OpenStreetMap. Los puntos están corridos ~100 m del lugar real.
 */

declare(strict_types=1);

$datosRoomies = [
    'avisos'     => array_map('roomie_publico', $roomies),
    'beauchef'   => ROOMIES_BEAUCHEF,
    'estaciones' => array_values(array_filter(
        array_map(static fn(array $e): array => ['nombre' => $e[0], 'lat' => $e[1], 'lng' => $e[2], 'lineas' => $e[3]], roomies_estaciones()),
        static fn(array $e): bool => roomies_metros($e['lat'], $e['lng'], ROOMIES_BEAUCHEF['lat'], ROOMIES_BEAUCHEF['lng']) < 6000
    )),
    'lineas'     => ROOMIES_LINEAS,
];
?>
<section class="roomies-resultados" aria-label="Avisos" data-resultados>
  <div class="contenedor">
    <div class="roomies-resultados__barra">
      <p class="roomies-resultados__cuenta" aria-live="polite" data-cuenta><?= count($roomies) ?> <?= count($roomies) === 1 ? 'aviso' : 'avisos' ?></p>
      <button class="enlace-boton" type="button" data-limpiar hidden>Limpiar filtros</button>
      <label class="ordenar">
        <span>Ordenar por</span>
        <select data-orden>
          <option value="cerca">Más cerca de Beauchef</option>
          <option value="barato">Menor precio</option>
          <option value="nuevo">Más recientes</option>
        </select>
      </label>
      <div class="segmentos roomies-vista" role="group" aria-label="Vista" data-vista hidden>
        <button type="button" aria-pressed="true" data-vista-boton="lista"><?= icono('list') ?>Lista</button>
        <button type="button" aria-pressed="false" data-vista-boton="mapa"><?= icono('map-trifold') ?>Mapa</button>
      </div>
    </div>
  </div>

  <div class="contenedor roomies-resultados__cuerpo">
    <?php if ($roomies === []): ?>
      <div class="vacio">
        <?= icono('house') ?>
        <p><strong>Todavía no hay avisos.</strong> ¿Tienes una pieza libre? Publícala y aparece aquí.</p>
      </div>
    <?php else: ?>
      <div class="roomies-lista" data-lista>
        <?php foreach ($roomies as $r): ?>
          <?= roomie_tarjeta($r, true, 'h2') ?>
        <?php endforeach; ?>
      </div>
      <div class="vacio roomies-sin-resultados" data-sin-resultados hidden>
        <?= icono('magnifying-glass') ?>
        <p><strong>Ningún aviso cumple esos filtros.</strong> Prueba con menos filtros o un precio más alto.</p>
      </div>
      <div class="roomies-mapa" data-mapa-contenedor>
        <div class="roomies-mapa__lienzo" data-mapa-roomies role="region" aria-label="Mapa de los avisos"></div>
        <p class="roomies-mapa__nota"><?= icono('info') ?>Los puntos son aproximados: la dirección exacta te la da quien publica.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
<script type="application/json" id="datos-roomies"><?= json_encode($datosRoomies, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
