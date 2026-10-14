<?php
/**
 * CEPC · Sitio público
 * includes/secciones/calendario/lista.php
 *
 * Lista de las próximas fechas, agrupadas por semana. Se ve siempre (también
 * sin JavaScript) y sirve como vista rápida en el teléfono.
 */

declare(strict_types=1);

$proximas = array_values(array_filter(
    $eventosCalendario,
    static fn(array $ev): bool => $ev['fecha'] >= date('Y-m-d')
));
$proximas = array_slice($proximas, 0, 30);

$semanas = [];
foreach ($proximas as $ev) {
    $lunes = date('Y-m-d', strtotime('monday this week', strtotime((string) $ev['fecha'])));
    $semanas[$lunes][] = $ev;
}
?>
<section class="seccion proximas" aria-labelledby="proximas-titulo">
  <div class="contenedor contenedor--angosto">
    <h2 id="proximas-titulo" class="seccion__titulo">Lo que viene</h2>

    <?php if ($semanas === []): ?>
      <div class="vacio">
        <?= icono('calendar-blank') ?>
        <p><strong>No hay fechas publicadas por ahora.</strong> Las agregamos apenas se confirman.</p>
      </div>
    <?php endif; ?>

    <?php foreach ($semanas as $lunes => $eventos): $p = fecha_partes($lunes); ?>
      <div class="proximas__semana">
        <h3 class="proximas__titulo-semana">
          <?= $lunes === date('Y-m-d', strtotime('monday this week')) ? 'Esta semana' : 'Semana del ' . esc($p['dia'] . ' de ' . $p['mes_largo']) ?>
        </h3>
        <ol class="proximas__lista">
          <?php foreach ($eventos as $ev): $f = fecha_partes((string) $ev['fecha']); ?>
            <li class="proxima" id="evento-<?= (int) $ev['id'] ?>">
              <span class="proxima__fecha">
                <strong><?= esc($f['dia']) ?></strong>
                <span><?= esc(mb_substr($f['semana'], 0, 3)) ?></span>
              </span>
              <span class="punto punto--<?= esc($ev['categoria']) ?>" aria-hidden="true"></span>
              <span class="proxima__texto">
                <strong><?= esc($ev['titulo']) ?></strong>
                <span><?= esc(implode(' · ', array_filter([$ev['etiqueta'], hora_texto($ev['hora_inicio']), $ev['lugar']]))) ?></span>
              </span>
              <a class="proxima__ics" href="<?= esc(enlace_ics((int) $ev['id'])) ?>" download aria-label="Agregar «<?= esc($ev['titulo']) ?>» al calendario">
                <?= icono('calendar-blank') ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ol>
      </div>
    <?php endforeach; ?>
  </div>
</section>
