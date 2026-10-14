<?php
/**
 * CEPC · Sitio público
 * includes/roomies/vistas.php — Piezas de HTML que se repiten en Roomies:
 * la tarjeta de un aviso, el metro más cercano y el avatar de quien publica.
 */

declare(strict_types=1);

/** «$230.000». */
function roomie_pesos(int $monto): string
{
    return pesos($monto);
}

/** «$230 mil» para los marcadores del mapa. */
function roomie_pesos_corto(int $monto): string
{
    return '$' . number_format($monto / 1000, 0, ',', '.') . ' mil';
}

/** Metro más cercano: «L2 Toesca · 5 min». */
function roomie_metro_html(?array $metro, bool $conMinutos = true): string
{
    if ($metro === null) {
        return '';
    }
    $lineas = implode('', array_map('roomies_linea_html', $metro['lineas']));
    return '<span class="metro-cercano">' . $lineas . '<span class="metro-cercano__nombre">' . esc($metro['nombre'])
        . ($conMinutos ? '<span class="metro-cercano__min"> · ' . (int) $metro['a_pie'] . ' min</span>' : '') . '</span></span>';
}

/** Avatar con iniciales y un color estable según el nombre. */
function roomie_avatar(array $r, string $clase = ''): string
{
    $tono = crc32((string) $r['autor_nombre']) % 4;
    return '<span class="avatar-roomie avatar-roomie--' . $tono . ($clase !== '' ? ' ' . esc($clase) : '') . '" aria-hidden="true">' . esc($r['iniciales']) . '</span>';
}

/** Rasgos cortos para la tarjeta: Amoblada · Baño privado · 2 roomies. */
function roomie_rasgos(array $r): array
{
    $rasgos = [];
    if ($r['tipo'] === 'departamento') {
        $rasgos[] = ['bed', $r['dormitorios'] . ' dormitorios'];
    } else {
        $rasgos[] = ['bed', in_array('amoblada', $r['comodidades'], true) ? 'Amoblada' : 'Sin amoblar'];
    }
    $rasgos[] = ['bathtub', in_array('bano_privado', $r['comodidades'], true) ? 'Baño privado' : ((int) $r['banos'] . ($r['banos'] > 1 ? ' baños' : ' baño'))];
    $actuales = (int) $r['roomies_actuales'];
    $rasgos[] = ['users', $actuales === 0 ? 'Sin roomies aún' : ($actuales === 1 ? 'Vive 1 persona' : "Viven $actuales personas")];
    return $rasgos;
}

/**
 * Tarjeta de un aviso. $atributosFiltro agrega los data-* que usa el
 * filtro del listado (roomies.js).
 */
function roomie_tarjeta(array $r, bool $atributosFiltro = false, string $encabezado = 'h3'): string
{
    $fotos = array_slice($r['fotos'], 0, 6);
    $metro = $r['cercania']['metro'];
    $datos = '';
    if ($atributosFiltro) {
        $buscable = implode(' ', [$r['titulo'], $r['comuna'], (string) ($r['calle'] ?? ''), $metro['nombre'] ?? '', $r['tipo_datos']['nombre']]);
        $datos = ' data-aviso="' . (int) $r['id'] . '"'
            . ' data-tipo="' . esc($r['tipo']) . '"'
            . ' data-total="' . (int) $r['total'] . '"'
            . ' data-a-pie="' . (int) $r['cercania']['a_pie'] . '"'
            . ' data-metro="' . (int) ($metro['a_pie'] ?? 99) . '"'
            . ' data-comodidades="' . esc(implode(' ', $r['comodidades'])) . '"'
            . ' data-incluidos="' . ($r['gastos_incluidos'] && count($r['incluye']) >= 4 ? '1' : '0') . '"'
            . ' data-mascotas="' . esc($r['mascotas']) . '"'
            . ' data-preferencia="' . esc($r['preferencia']) . '"'
            . ' data-disponible="' . ($r['disponible_ya'] ? '1' : '0') . '"'
            . ' data-publicado="' . esc((string) $r['publicado_en']) . '"'
            . ' data-texto="' . esc(mb_strtolower($buscable)) . '"';
    }
    ob_start();
    ?>
<article class="aviso"<?= $datos ?>>
  <div class="aviso__fotos" data-carrusel>
    <a class="aviso__pista" href="<?= esc($r['url']) ?>" tabindex="-1" aria-hidden="true" data-carrusel-pista>
      <?php foreach ($fotos as $i => $f): ?>
        <img src="<?= esc($f['m']) ?>" alt="" width="640" height="427" loading="lazy" decoding="async">
      <?php endforeach; ?>
      <?php if ($fotos === []): ?><span class="aviso__sin-foto"><?= icono('house') ?></span><?php endif; ?>
    </a>
    <div class="aviso__sellos">
      <span class="sello-verificado" title="Correo de la Universidad verificado"><?= icono('seal-check') ?>Verificado</span>
      <?php if ($r['nuevo']): ?><span class="sello-nuevo">Nuevo</span><?php endif; ?>
    </div>
    <button class="aviso__guardar" type="button" data-guardar="<?= (int) $r['id'] ?>" aria-pressed="false">
      <?= icono('heart', 'corazon-vacio') ?><?= icono('heart-fill', 'corazon-lleno') ?><span class="oculto">Guardar aviso</span>
    </button>
    <?php if (count($fotos) > 1): ?>
      <button class="aviso__flecha aviso__flecha--antes" type="button" data-carrusel-paso="-1" tabindex="-1" aria-hidden="true"><?= icono('caret-left') ?></button>
      <button class="aviso__flecha aviso__flecha--despues" type="button" data-carrusel-paso="1" tabindex="-1" aria-hidden="true"><?= icono('caret-right') ?></button>
      <span class="aviso__puntos" aria-hidden="true"><?php foreach ($fotos as $i => $f): ?><i<?= $i === 0 ? ' class="activo"' : '' ?>></i><?php endforeach; ?></span>
    <?php endif; ?>
  </div>

  <a class="aviso__cuerpo" href="<?= esc($r['url']) ?>">
    <span class="aviso__precio">
      <strong><?= esc(roomie_pesos($r['precio'])) ?></strong><span class="aviso__mes">/mes</span>
      <?php if ($r['gastos_incluidos'] && count($r['incluye']) >= 4): ?>
        <span class="aviso__gastos aviso__gastos--incluidos">Todo incluido</span>
      <?php elseif ($r['gastos_incluidos']): ?>
        <span class="aviso__gastos">Gastos comunes incluidos</span>
      <?php elseif ($r['gastos_comunes'] > 0): ?>
        <span class="aviso__gastos">+ <?= esc(roomie_pesos($r['gastos_comunes'])) ?> gastos</span>
      <?php endif; ?>
    </span>
    <<?= $encabezado ?> class="aviso__titulo"><?= esc($r['titulo']) ?></<?= $encabezado ?>>
    <span class="aviso__lugar"><?= esc($r['tipo_datos']['nombre']) ?> · <?= esc($r['comuna']) ?><?= !empty($r['calle']) ? ', ' . esc($r['calle']) : '' ?></span>
    <span class="aviso__trayecto">
      <span class="aviso__a-pie"><?= icono('person-simple-walk') ?><strong><?= (int) $r['cercania']['a_pie'] ?> min</strong> a Beauchef</span>
      <?= roomie_metro_html($metro) ?>
    </span>
    <span class="aviso__rasgos">
      <?php foreach (roomie_rasgos($r) as [$ic, $texto]): ?>
        <span><?= icono($ic) ?><?= esc($texto) ?></span>
      <?php endforeach; ?>
    </span>
    <span class="aviso__pie">
      <?= roomie_avatar($r) ?>
      <span class="aviso__autor"><strong><?= esc($r['autor_nombre']) ?></strong><?= $r['autor_detalle'] !== '' ? ' · ' . esc($r['autor_detalle']) : '' ?></span>
      <span class="aviso__disponible<?= $r['disponible_ya'] ? ' aviso__disponible--ya' : '' ?>"><?= esc($r['disponible_corto']) ?></span>
    </span>
  </a>
</article>
    <?php
    return (string) ob_get_clean();
}
