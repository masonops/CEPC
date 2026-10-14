<?php
/**
 * CEPC · Sitio público
 * includes/secciones/calculadora/calculadora.php
 *
 * La calculadora: plantillas, tabla de evaluaciones y el resultado.
 * Las plantillas se editan en $plantillasNotas (peso en %).
 */

declare(strict_types=1);

$plantillasNotas = [
    'tres-controles' => ['nombre' => '3 controles + examen', 'evaluaciones' => [
        ['Control 1', 20], ['Control 2', 20], ['Control 3', 20], ['Examen', 40],
    ]],
    'dos-controles' => ['nombre' => '2 controles + examen', 'evaluaciones' => [
        ['Control 1', 30], ['Control 2', 30], ['Examen', 40],
    ]],
    'con-tareas' => ['nombre' => 'Controles, tareas y examen', 'evaluaciones' => [
        ['Control 1', 20], ['Control 2', 20], ['Tareas', 20], ['Examen', 40],
    ]],
    'en-blanco' => ['nombre' => 'En blanco', 'evaluaciones' => [
        ['Evaluación 1', 50], ['Evaluación 2', 50],
    ]],
];
?>
<section class="seccion calculadora" aria-label="Calculadora" data-calculadora>
  <div class="contenedor">
    <noscript>
      <p class="aviso-ejemplo"><?= icono('info') ?>La calculadora necesita JavaScript activado en tu navegador.</p>
    </noscript>

    <div class="calculadora__grid">
      <div class="calculadora__panel">
        <div class="calculadora__fila-titulo">
          <label class="calculadora__ramo">
            <span class="oculto">Nombre del ramo</span>
            <input type="text" data-calc-ramo placeholder="Nombre del ramo (opcional)" autocomplete="off" maxlength="60">
          </label>
        </div>

        <div class="calculadora__plantillas segmentos" role="group" aria-label="Plantillas">
          <?php foreach ($plantillasNotas as $clave => $plantilla): ?>
            <button type="button" aria-pressed="false" data-calc-plantilla="<?= esc($clave) ?>"><?= esc($plantilla['nombre']) ?></button>
          <?php endforeach; ?>
        </div>

        <table class="notas">
          <thead>
            <tr>
              <th scope="col">Evaluación</th>
              <th scope="col">Peso</th>
              <th scope="col">Nota</th>
              <th scope="col"><span class="oculto">Quitar</span></th>
            </tr>
          </thead>
          <tbody data-calc-filas></tbody>
        </table>

        <div class="calculadora__pie-tabla">
          <button class="boton boton--vidrio" type="button" data-calc-agregar><?= icono('plus') ?> Agregar evaluación</button>
          <p class="calculadora__suma" data-calc-suma aria-live="polite"></p>
        </div>

        <details class="calculadora__ajustes">
          <summary>Ajustes del ramo</summary>
          <div class="calculadora__ajustes-campos">
            <label>
              <span>Nota para aprobar</span>
              <input type="text" inputmode="decimal" data-calc-meta="aprobar" value="4,0">
            </label>
            <label>
              <span>Nota para ir a segunda</span>
              <input type="text" inputmode="decimal" data-calc-meta="segunda" value="3,7">
            </label>
          </div>
          <p class="calculadora__nota-ajuste">Cada ramo puede tener sus propias reglas. Revisa el programa del curso.</p>
        </details>
      </div>

      <aside class="calculadora__resultado" aria-live="polite" data-calc-resultado></aside>
    </div>
  </div>
</section>
<?php foreach (['check-square', 'info', 'warning-circle'] as $iconoCalc): ?>
<template data-icono="<?= esc($iconoCalc) ?>"><?= icono($iconoCalc) ?></template>
<?php endforeach; ?>
<script type="application/json" id="plantillas-notas"><?= json_encode($plantillasNotas, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
