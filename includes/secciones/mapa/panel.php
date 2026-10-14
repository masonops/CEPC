<?php
/**
 * CEPC · Sitio público
 * includes/secciones/mapa/panel.php
 *
 * Panel flotante del mapa con tres pestañas:
 *   Explorar  lista de edificios; al elegir uno, su ficha con pisos y salas
 *   Ruta      desde una entrada o edificio, hasta un edificio o sala
 *   Comer     lugares con filtros (abierto ahora, vegano, Junaeb…)
 *
 * Sin JavaScript se ve la lista completa de edificios con sus salas.
 */

declare(strict_types=1);

$mapa = mapa_datos();

$porCampus = ['851' => [], '850' => []];
foreach ($mapa['edificios'] as $e) {
    $porCampus[$e['campus']][] = $e;
}

$entradas = array_filter($mapa['nodos'], static fn(array $n): bool => ($n['tipo'] ?? '') === 'entrada');
$edificiosConPuerta = array_filter($mapa['edificios'], static fn(array $e): bool => $e['puerta'] !== null);
$salas = mapa_salas();

$etiquetasComida = ['vegano' => 'Vegano', 'vegetariano' => 'Vegetariano', 'junaeb' => 'Junaeb', 'barato' => 'Barato'];
$tiposComida = ['almuerzo' => 'Almuerzo', 'cafeteria' => 'Cafetería', 'snack' => 'Snack', 'minimarket' => 'Minimarket'];
$hayLugaresEjemplo = (bool) array_filter($mapa['lugares'], static fn(array $l): bool => !empty($l['ejemplo']));

// Lo que necesita mapa.js (sin las formas: las lee del SVG).
$datosMapa = [
    'edificios' => array_map(static fn(array $e): array => array_intersect_key($e, array_flip(
        ['id', 'nombre', 'corto', 'campus', 'tipo', 'descripcion', 'servicios', 'pisos', 'puerta', 'escaleras']
    )) + ['zonas' => array_values(array_map(
        static fn(array $z): array => ['id' => $z['id'], 'nombre' => $z['nombre'], 'descripcion' => $z['descripcion']],
        $e['zonas']
    ))], $mapa['edificios']),
    'nodos'        => $mapa['nodos'],
    'aristas'      => $mapa['aristas'],
    'metros'       => $mapa['metros'],
    'lugares'      => $mapa['lugares'],
    'salasEjemplo' => $mapa['salas_ejemplo'],
];
?>
<aside class="panel-mapa" data-panel-mapa aria-label="Buscar y explorar el campus">
  <div class="panel-mapa__cabeza">
    <label class="panel-mapa__buscar">
      <?= icono('magnifying-glass') ?>
      <span class="oculto">Buscar en el mapa</span>
      <input type="search" placeholder="Edificio, sala o lugar" data-mapa-buscar autocomplete="off" spellcheck="false">
    </label>
    <div class="segmentos panel-mapa__pestanas" role="tablist" aria-label="Modo">
      <button type="button" role="tab" aria-selected="true" aria-pressed="true" aria-controls="panel-explorar" data-pestana="explorar"><?= icono('buildings') ?>Explorar</button>
      <button type="button" role="tab" aria-selected="false" aria-pressed="false" aria-controls="panel-ruta" data-pestana="ruta"><?= icono('path') ?>Ruta</button>
      <button type="button" role="tab" aria-selected="false" aria-pressed="false" aria-controls="panel-comer" data-pestana="comer"><?= icono('fork-knife') ?>Comer</button>
    </div>
  </div>

  <div class="panel-mapa__cuerpo">
    <!-- Explorar -------------------------------------------------------- -->
    <div id="panel-explorar" role="tabpanel" data-panel="explorar">
      <div data-mapa-ficha hidden></div>
      <div data-mapa-resultados hidden></div>

      <div data-mapa-lista>
        <?php foreach ($porCampus as $campus => $lista): ?>
          <h2 class="panel-mapa__grupo">Beauchef <?= esc($campus) ?></h2>
          <ul class="lista-edificios">
            <?php foreach ($lista as $e): ?>
              <li>
                <details class="edificio-item" data-edificio-item="<?= esc($e['id']) ?>">
                  <summary data-ir-edificio="<?= esc($e['id']) ?>">
                    <span class="edificio-item__icono"><?= icono($e['tipo'] === 'espacio' ? 'soccer-ball' : 'buildings') ?></span>
                    <span><?= esc($e['nombre']) ?></span>
                  </summary>
                  <div class="edificio-item__cuerpo">
                    <?php if ($e['descripcion'] !== ''): ?><p><?= esc($e['descripcion']) ?></p><?php endif; ?>
                    <?php foreach ($e['pisos'] as $piso): if ($piso['salas'] === []) continue; ?>
                      <p class="edificio-item__piso"><?= esc(mapa_nombre_piso((int) $piso['nivel'])) ?>:
                        <?= esc(implode(', ', array_map(static fn(array $s): string => $s['nombre'], $piso['salas']))) ?></p>
                    <?php endforeach; ?>
                  </div>
                </details>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endforeach; ?>
      </div>

      <?php if ($mapa['salas_ejemplo']): ?>
        <p class="aviso-ejemplo panel-mapa__aviso"><?= icono('info') ?>Los pisos y salas son de ejemplo mientras cargamos los reales.</p>
      <?php endif; ?>
    </div>

    <!-- Ruta ------------------------------------------------------------- -->
    <div id="panel-ruta" role="tabpanel" data-panel="ruta" hidden>
      <form class="ruta-form" data-ruta-form>
        <label>
          <span>Desde</span>
          <select name="desde" data-ruta-desde>
            <optgroup label="Entradas">
              <?php foreach ($entradas as $id => $n): ?>
                <option value="<?= esc($id) ?>"><?= esc($n['nombre']) ?></option>
              <?php endforeach; ?>
            </optgroup>
            <optgroup label="Edificios">
              <?php foreach ($edificiosConPuerta as $e): ?>
                <option value="<?= esc($e['puerta']) ?>"><?= esc($e['nombre']) ?></option>
              <?php endforeach; ?>
            </optgroup>
          </select>
        </label>
        <button class="ruta-form__invertir" type="button" data-ruta-invertir aria-label="Invertir origen y destino"><?= icono('arrow-right') ?></button>
        <label>
          <span>Hasta</span>
          <select name="hasta" data-ruta-hasta>
            <optgroup label="Edificios">
              <?php foreach ($edificiosConPuerta as $e): ?>
                <option value="e:<?= esc($e['id']) ?>"><?= esc($e['nombre']) ?></option>
              <?php endforeach; ?>
            </optgroup>
            <optgroup label="Salas">
              <?php foreach ($salas as $s): ?>
                <option value="s:<?= esc($s['codigo']) ?>"><?= esc($s['nombre']) ?> · <?= esc($mapa['edificios'][$s['edificio']]['corto']) ?></option>
              <?php endforeach; ?>
            </optgroup>
          </select>
        </label>
      </form>
      <div class="ruta-resultado" data-ruta-resultado aria-live="polite"></div>
      <p class="panel-mapa__nota">Las distancias y tiempos son aproximados.</p>
    </div>

    <!-- Comer ------------------------------------------------------------ -->
    <div id="panel-comer" role="tabpanel" data-panel="comer" hidden>
      <div class="filtros-comida" role="group" aria-label="Filtrar lugares">
        <button type="button" aria-pressed="false" data-filtro-comida="abierto"><?= icono('clock') ?>Abierto ahora</button>
        <?php foreach ($etiquetasComida as $clave => $nombre): ?>
          <button type="button" aria-pressed="false" data-filtro-comida="<?= esc($clave) ?>"><?= $clave === 'vegano' ? icono('leaf') : ($clave === 'junaeb' ? icono('credit-card') : '') ?><?= esc($nombre) ?></button>
        <?php endforeach; ?>
      </div>

      <ul class="lugares" data-lugares>
        <?php foreach ($mapa['lugares'] as $id => $l): ?>
          <li>
            <button type="button" class="lugar" data-ir-lugar="<?= esc($id) ?>" data-etiquetas="<?= esc(implode(' ', $l['etiquetas'] ?? [])) ?>">
              <span class="lugar__icono"><?= icono($l['tipo'] === 'cafeteria' ? 'coffee' : 'fork-knife') ?></span>
              <span class="lugar__texto">
                <strong><?= esc($l['nombre']) ?><?= !empty($l['ejemplo']) ? ' <em>ejemplo</em>' : '' ?></strong>
                <span><?= esc($tiposComida[$l['tipo']] ?? ucfirst($l['tipo'])) ?><?php foreach ($l['etiquetas'] ?? [] as $t): ?> · <?= esc($etiquetasComida[$t] ?? $t) ?><?php endforeach; ?></span>
                <span class="lugar__horario" data-horario-de="<?= esc($id) ?>"><?= $l['horario'] ? '' : 'Horario por confirmar' ?></span>
              </span>
            </button>
          </li>
        <?php endforeach; ?>
      </ul>
      <p class="lugares__vacio" data-lugares-vacio hidden>Ningún lugar cumple esos filtros.</p>

      <?php if ($hayLugaresEjemplo): ?>
        <p class="aviso-ejemplo panel-mapa__aviso"><?= icono('info') ?>Los lugares marcados «ejemplo» son de muestra: falta cargar los reales con su horario.</p>
      <?php endif; ?>
    </div>
  </div>
</aside>
<script type="application/json" id="datos-mapa"><?= json_encode($datosMapa, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
