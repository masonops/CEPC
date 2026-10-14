<?php
/**
 * CEPC · Sitio público
 * includes/secciones/calendario/mes.php
 *
 * Vista mensual: barra con el mes y los filtros, la cuadrícula de días y el
 * detalle del día elegido. calendario.js la dibuja con los datos que van en
 * <script id="datos-calendario">.
 *
 * Grupos (colores validados para daltonismo, ver calendario.css):
 *   evaluacion  solemnes, controles, exámenes y entregas
 *   actividad   actividades y sunsets
 *   comunidad   asambleas, elecciones, marchas y paros
 */

declare(strict_types=1);

$gruposCalendario = [
    'evaluacion' => 'Evaluaciones',
    'actividad'  => 'Actividades',
    'comunidad'  => 'Asambleas y elecciones',
];

$datosCalendario = [
    'hoy'     => date('Y-m-d'),
    'grupos'  => $gruposCalendario,
    'eventos' => array_map(static fn(array $ev): array => [
        'id'       => (int) $ev['id'],
        'titulo'   => $ev['titulo'],
        'tipo'     => $ev['etiqueta'],
        'grupo'    => $ev['categoria'],
        'fecha'    => substr((string) $ev['fecha'], 0, 10),
        'hora'     => $ev['hora_inicio'] ? substr((string) $ev['hora_inicio'], 0, 5) : null,
        'horaFin'  => $ev['hora_fin'] ? substr((string) $ev['hora_fin'], 0, 5) : null,
        'lugar'    => $ev['lugar'],
        'resumen'  => $ev['resumen'],
        'ics'      => enlace_ics((int) $ev['id']),
    ], $eventosCalendario),
];
?>
<section class="seccion calendario" aria-label="Calendario mensual" data-calendario hidden>
  <div class="contenedor">
    <div class="calendario__barra">
      <h2 class="calendario__mes" data-cal-titulo aria-live="polite"></h2>
      <div class="calendario__navegacion">
        <button class="boton boton--vidrio calendario__hoy" type="button" data-cal-hoy>Hoy</button>
        <button class="paleta" type="button" data-cal-mover="-1" aria-label="Mes anterior"><?= icono('caret-left') ?></button>
        <button class="paleta" type="button" data-cal-mover="1" aria-label="Mes siguiente"><?= icono('caret-right') ?></button>
      </div>
    </div>

    <div class="calendario__filtros segmentos" role="group" aria-label="Mostrar">
      <?php foreach ($gruposCalendario as $clave => $nombre): ?>
        <button type="button" aria-pressed="true" data-cal-filtro="<?= esc($clave) ?>">
          <span class="punto punto--<?= esc($clave) ?>" aria-hidden="true"></span><?= esc($nombre) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <div class="calendario__cuerpo">
      <div class="calendario__rejilla" role="grid" aria-labelledby="cal-titulo-accesible">
        <span class="oculto" id="cal-titulo-accesible" data-cal-titulo-accesible></span>
        <div class="calendario__semana" role="row">
          <?php foreach (['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'] as $dia): ?>
            <span role="columnheader" aria-label="<?= esc($dia) ?>"><?= esc(mb_strtoupper(mb_substr($dia, 0, 3))) ?></span>
          <?php endforeach; ?>
        </div>
        <div class="calendario__dias" data-cal-dias></div>
      </div>

      <aside class="calendario__detalle" aria-live="polite" data-cal-detalle></aside>
    </div>
  </div>
</section>
<script type="application/json" id="datos-calendario"><?= json_encode($datosCalendario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
