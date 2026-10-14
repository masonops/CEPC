<?php
/**
 * CEPC · Sitio público
 * includes/secciones/roomies/filtros.php — Barra de filtros (fija arriba al
 * bajar). Todo se filtra en el navegador (assets/js/roomies.js); sin
 * JavaScript se ven todos los avisos y la barra no aparece.
 */

declare(strict_types=1);

$maximo = $roomies === [] ? 400000 : (int) (ceil(max(array_column($roomies, 'total')) / 10000) * 10000);
?>
<div class="roomies-filtros" data-filtros hidden>
  <div class="contenedor roomies-filtros__interior">
    <label class="roomies-filtros__buscar">
      <?= icono('magnifying-glass') ?>
      <span class="oculto">Buscar</span>
      <input type="search" placeholder="Comuna, calle o metro" data-filtro-texto autocomplete="off">
    </label>

    <div class="segmentos" role="group" aria-label="Tipo">
      <button type="button" aria-pressed="true" data-filtro-tipo="">Todo</button>
      <button type="button" aria-pressed="false" data-filtro-tipo="pieza">Piezas</button>
      <button type="button" aria-pressed="false" data-filtro-tipo="departamento">Deptos</button>
    </div>

    <details class="desplegable" data-desplegable>
      <summary class="pildora-filtro" data-resumen-precio><?= icono('money') ?><span>Precio</span><?= icono('caret-down', 'desplegable__flecha') ?></summary>
      <div class="desplegable__panel">
        <label class="rango">
          <span class="rango__cabeza"><span>Hasta</span><output data-precio-salida><?= esc(roomie_pesos($maximo)) ?></output></span>
          <input type="range" min="100000" max="<?= $maximo ?>" step="10000" value="<?= $maximo ?>" data-filtro-precio>
          <span class="rango__nota">Total al mes: arriendo + gastos comunes.</span>
        </label>
      </div>
    </details>

    <details class="desplegable" data-desplegable>
      <summary class="pildora-filtro" data-resumen-distancia><?= icono('person-simple-walk') ?><span>Distancia</span><?= icono('caret-down', 'desplegable__flecha') ?></summary>
      <div class="desplegable__panel">
        <p class="desplegable__titulo">A pie hasta Beauchef</p>
        <div class="opciones-radio" role="radiogroup">
          <?php foreach (['' => 'Cualquiera', '10' => 'Hasta 10 min', '20' => 'Hasta 20 min', '30' => 'Hasta 30 min'] as $v => $t): ?>
            <label><input type="radio" name="distancia" value="<?= esc($v) ?>"<?= $v === '' ? ' checked' : '' ?> data-filtro-distancia><span><?= esc($t) ?></span></label>
          <?php endforeach; ?>
        </div>
        <label class="interruptor"><input type="checkbox" data-filtro-metro><span>Metro a menos de 5 min</span></label>
      </div>
    </details>

    <details class="desplegable desplegable--ancho" data-desplegable>
      <summary class="pildora-filtro" data-resumen-mas><?= icono('sliders-horizontal') ?><span>Más filtros</span><span class="pildora-filtro__cuenta" data-cuenta-mas hidden></span></summary>
      <div class="desplegable__panel">
        <p class="desplegable__titulo">La pieza y el lugar</p>
        <div class="casillas">
          <label><input type="checkbox" value="amoblada" data-filtro-comodidad><span><?= icono('bed') ?>Amoblada</span></label>
          <label><input type="checkbox" value="bano_privado" data-filtro-comodidad><span><?= icono('bathtub') ?>Baño privado</span></label>
          <label><input type="checkbox" value="escritorio" data-filtro-comodidad><span><?= icono('desk') ?>Escritorio</span></label>
          <label><input type="checkbox" value="lavadora" data-filtro-comodidad><span><?= icono('washing-machine') ?>Lavadora</span></label>
          <label><input type="checkbox" data-filtro-incluido><span><?= icono('lightning') ?>Todo incluido</span></label>
          <label><input type="checkbox" data-filtro-mascotas><span><?= icono('paw-print') ?>Aceptan mascotas</span></label>
          <label><input type="checkbox" data-filtro-disponible><span><?= icono('calendar-check') ?>Disponible ahora</span></label>
        </div>
        <p class="desplegable__titulo">Avisos abiertos a</p>
        <div class="opciones-radio" role="radiogroup">
          <label><input type="radio" name="para" value="" checked data-filtro-para><span>Todos</span></label>
          <label><input type="radio" name="para" value="mujeres" data-filtro-para><span>Mujeres</span></label>
          <label><input type="radio" name="para" value="hombres" data-filtro-para><span>Hombres</span></label>
        </div>
      </div>
    </details>

    <button class="pildora-filtro" type="button" aria-pressed="false" data-filtro-guardados><?= icono('heart') ?><span>Guardados</span></button>
  </div>
</div>
