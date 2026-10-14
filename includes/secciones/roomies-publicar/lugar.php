<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies-publicar/lugar.php — Tipo de aviso, título,
 * descripción y ubicación (comuna, calle sin número y punto en el mapa).
 * Con el punto se calcula la distancia a Beauchef y el metro más cercano.
 */

declare(strict_types=1);

$estacionesMapa = array_map(
    static fn(array $e): array => ['nombre' => $e[0], 'lat' => $e[1], 'lng' => $e[2], 'lineas' => $e[3]],
    roomies_estaciones()
);
?>
<fieldset class="parte" id="parte-lugar" data-parte="lugar">
  <legend><span class="parte__numero">2</span>Dónde queda</legend>

  <div class="tipos" role="radiogroup" aria-label="Qué publicas">
    <?php foreach (ROOMIES_TIPOS as $clave => $tipo): ?>
      <label class="tipo">
        <input type="radio" name="tipo" value="<?= esc($clave) ?>"<?= $marcado('tipo', $clave, $clave === 'pieza') ?>>
        <span><?= icono($tipo['icono']) ?><strong><?= esc($tipo['largo']) ?></strong>
          <small><?= $clave === 'pieza' ? 'Ya vives ahí y se libera una pieza.' : 'Quieres arrendar un depto entero entre varios.' ?></small></span>
      </label>
    <?php endforeach; ?>
  </div>

  <label class="campo<?= $conError('titulo') ?>">
    <span>Título</span>
    <input type="text" name="titulo" maxlength="90" required placeholder="Ej.: Pieza amoblada a 10 minutos caminando de Beauchef" value="<?= $valor('titulo') ?>" data-contador>
    <?= $error('titulo') ?>
  </label>
  <label class="campo<?= $conError('descripcion') ?>">
    <span>Descripción</span>
    <textarea name="descripcion" rows="6" maxlength="3000" required placeholder="Cómo es la pieza, el depto y el barrio; quiénes viven; qué buscan en un roomie…" data-contador><?= $valor('descripcion') ?></textarea>
    <?= $error('descripcion') ?>
  </label>

  <div class="rejilla rejilla--2">
    <label class="campo<?= $conError('comuna') ?>">
      <span>Comuna</span>
      <select name="comuna" required data-comuna>
        <option value="">Elige…</option>
        <?php foreach (ROOMIES_COMUNAS as $comuna): ?>
          <option<?= ($_POST['comuna'] ?? '') === $comuna ? ' selected' : '' ?>><?= esc($comuna) ?></option>
        <?php endforeach; ?>
      </select>
      <?= $error('comuna') ?>
    </label>
    <label class="campo">
      <span>Calle <span class="campo__ayuda">· sin número</span></span>
      <input type="text" name="calle" maxlength="80" placeholder="Ej.: San Ignacio" value="<?= $valor('calle') ?>" data-calle>
    </label>
  </div>

  <div class="campo<?= $conError('ubicacion') ?>">
    <span>Marca dónde queda <span class="campo__ayuda">· el aviso muestra un punto corrido unos 100 m, nunca la dirección exacta</span></span>
    <div class="ubicador">
      <div class="ubicador__mapa" data-ubicador role="application" aria-label="Mapa: toca para marcar el lugar"></div>
      <div class="ubicador__barra">
        <button class="boton-texto" type="button" data-buscar-direccion><?= icono('magnifying-glass') ?>Buscar la calle en el mapa</button>
        <span class="ubicador__resultado" data-ubicador-resultado aria-live="polite">Toca el mapa para poner el punto.</span>
      </div>
    </div>
    <input type="hidden" name="lat" value="<?= $valor('lat') ?>" data-lat>
    <input type="hidden" name="lng" value="<?= $valor('lng') ?>" data-lng>
    <?= $error('ubicacion') ?>
  </div>
  <script type="application/json" id="datos-ubicador"><?= json_encode(['beauchef' => ROOMIES_BEAUCHEF, 'estaciones' => $estacionesMapa, 'lineas' => ROOMIES_LINEAS], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</fieldset>
