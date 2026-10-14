<?php
/**
 * CEPC · Sitio público
 * includes/mapa/mapa.php — Junta los datos del mapa y dibuja el SVG.
 *
 * Los datos están repartidos para que sean fáciles de editar:
 *   geometria.php  formas (generado desde el plano, no se toca a mano)
 *   edificios.php  nombres, pisos y salas
 *   rutas.php      caminos para calcular rutas
 *   lugares.php    dónde comer
 */

declare(strict_types=1);

/** Todos los datos del mapa, ya combinados. */
function mapa_datos(): array
{
    static $datos = null;
    if ($datos !== null) {
        return $datos;
    }

    $carpeta   = SITIO_RAIZ . '/includes/mapa/';
    $geometria = require $carpeta . 'geometria.php';
    $info      = require $carpeta . 'edificios.php';
    $rutas     = require $carpeta . 'rutas.php';
    $lugares   = require $carpeta . 'lugares.php';

    // Puerta de cada edificio, para que las rutas sepan dónde llegar.
    $puertas = [];
    foreach ($rutas['nodos'] as $id => $nodo) {
        if (($nodo['tipo'] ?? '') === 'puerta' && isset($nodo['edificio'])) {
            $puertas[$nodo['edificio']] = $id;
        }
    }

    $edificios = [];
    $formas = $geometria['edificios'] + $geometria['espacios'];
    foreach ($formas as $id => $poligono) {
        $e = $info[$id] ?? ['nombre' => ucfirst($id)];
        $edificios[$id] = [
            'id'          => $id,
            'nombre'      => $e['nombre'],
            'corto'       => $e['corto'] ?? $e['nombre'],
            'campus'      => $e['campus'] ?? '850',
            'tipo'        => $e['tipo'] ?? (isset($geometria['espacios'][$id]) ? 'espacio' : 'edificio'),
            'descripcion' => $e['descripcion'] ?? '',
            'servicios'   => $e['servicios'] ?? [],
            'pisos'       => $e['pisos'] ?? [],
            'poligono'    => $poligono,
            'etiqueta'    => $e['etiqueta'] ?? mapa_centro($poligono),
            'puerta'      => $puertas[$id] ?? null,
        ];
    }

    return $datos = [
        'ancho'         => $geometria['ancho'],
        'alto'          => $geometria['alto'],
        'patios'        => $geometria['patios'],
        'perimetro_850' => $geometria['perimetro_850'],
        'edificios'     => $edificios,
        'nodos'         => $rutas['nodos'],
        'aristas'       => $rutas['aristas'],
        'metros'        => (float) $rutas['metros_por_unidad'],
        'lugares'       => $lugares,
        'salas_ejemplo' => defined('MAPA_SALAS_DE_EJEMPLO') && MAPA_SALAS_DE_EJEMPLO,
    ];
}

/** Centro (centroide) de un polígono «x,y x,y …», para poner la etiqueta. */
function mapa_centro(string $poligono): array
{
    $p = array_map(static fn(string $par): array => array_map('floatval', explode(',', $par)), explode(' ', trim($poligono)));
    $area = $cx = $cy = 0.0;
    $n = count($p);
    for ($i = 0; $i < $n; $i++) {
        [$x0, $y0] = $p[$i];
        [$x1, $y1] = $p[($i + 1) % $n];
        $f = $x0 * $y1 - $x1 * $y0;
        $area += $f;
        $cx += ($x0 + $x1) * $f;
        $cy += ($y0 + $y1) * $f;
    }
    if (abs($area) < 1e-6) {
        return $p[0];
    }
    return [round($cx / (3 * $area)), round($cy / (3 * $area))];
}

/** Todas las salas, con su edificio y piso, para el buscador. */
function mapa_salas(): array
{
    $salas = [];
    foreach (mapa_datos()['edificios'] as $edificio) {
        foreach ($edificio['pisos'] as $piso) {
            foreach ($piso['salas'] as $sala) {
                $salas[] = $sala + ['edificio' => $edificio['id'], 'edificio_nombre' => $edificio['nombre'], 'piso' => $piso['nivel']];
            }
        }
    }
    return $salas;
}

/** «Piso 2», «Subterráneo 1». */
function mapa_nombre_piso(int $nivel): string
{
    return $nivel < 0 ? 'Subterráneo ' . abs($nivel) : 'Piso ' . $nivel;
}

/**
 * Dibuja el mapa como SVG.
 *
 * Opciones: 'interactivo' (edificios con foco y datos para mapa.js),
 * 'etiquetas', 'calles', 'lugares', 'coordenadas' (cuadrícula de ayuda),
 * 'vista' (viewBox; por defecto todo el mapa) y 'titulo' (texto accesible).
 */
function mapa_svg(array $opciones = []): string
{
    $o = $opciones + [
        'interactivo' => false, 'etiquetas' => true, 'calles' => true, 'lugares' => false,
        'coordenadas' => false, 'vista' => null, 'titulo' => 'Mapa del campus Beauchef',
    ];
    $d = mapa_datos();
    $vista = $o['vista'] ?? '0 0 ' . $d['ancho'] . ' ' . $d['alto'];

    $h = '<svg class="mapa-svg" viewBox="' . esc($vista) . '" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="' . esc($o['titulo']) . '"'
        . ($o['interactivo'] ? ' data-mapa-svg' : '') . '>';

    // Suelo, terrenos de cada campus y calles.
    $h .= '<rect class="mapa-suelo" x="-2000" y="-2000" width="' . ($d['ancho'] + 4000) . '" height="' . ($d['alto'] + 4000) . '"/>';
    if ($o['calles']) {
        $h .= '<g class="mapa-calles" aria-hidden="true">'
            . '<rect class="mapa-plaza" x="1036" y="178" width="120" height="476" rx="18"/>'
            . '<rect x="-400" y="134" width="1900" height="40"/>'
            . '<rect x="-400" y="652" width="1900" height="40"/>'
            . '<rect x="199" y="-400" width="26" height="1500"/>'
            . '</g>';
    }
    $h .= '<g class="mapa-terrenos" aria-hidden="true">'
        . '<polygon points="' . esc($d['perimetro_850']) . '"/>'
        . '<rect x="34" y="210" width="161" height="430" rx="6"/>'
        . '</g>';
    if ($o['calles'] && $o['etiquetas']) {
        $h .= '<g class="mapa-nombres-calles" aria-hidden="true">'
            . '<text x="640" y="159">Blanco Encalada</text>'
            . '<text x="640" y="677">Tupper</text>'
            . '<text x="212" y="700" transform="rotate(-90 212 700)">Beauchef</text>'
            . '<text x="1070" y="420" transform="rotate(90 1070 420)">Plaza Ercilla</text>'
            . '</g>';
    }

    // Líneas de los patios centrales.
    $h .= '<g class="mapa-patios" aria-hidden="true">';
    foreach ($d['patios'] as $trazo) {
        $h .= '<polyline points="' . esc($trazo) . '"/>';
    }
    $h .= '</g>';

    // Edificios y espacios.
    $h .= '<g class="mapa-edificios">';
    foreach ($d['edificios'] as $e) {
        $clase = 'mapa-edificio mapa-edificio--' . $e['tipo'];
        if ($o['interactivo']) {
            $h .= '<g class="' . $clase . '" data-edificio="' . esc($e['id']) . '" tabindex="0" role="button" aria-label="' . esc($e['nombre']) . '">';
        } else {
            $h .= '<g class="' . $clase . '">';
        }
        $h .= '<polygon points="' . esc($e['poligono']) . '"/></g>';
    }
    $h .= '</g>';

    if ($o['etiquetas']) {
        $h .= '<g class="mapa-etiquetas" aria-hidden="true">';
        foreach ($d['edificios'] as $e) {
            [$x, $y] = $e['etiqueta'];
            $h .= '<text x="' . $x . '" y="' . $y . '" data-etiqueta="' . esc($e['id']) . '">' . esc($e['corto']) . '</text>';
        }
        $h .= '</g>';
    }

    // Capa de la ruta (mapa.js la dibuja).
    if ($o['interactivo']) {
        $h .= '<g class="mapa-ruta" data-mapa-ruta aria-hidden="true"></g>';
    }

    // Entradas.
    if ($o['interactivo']) {
        $h .= '<g class="mapa-entradas" aria-hidden="true">';
        foreach ($d['nodos'] as $nodo) {
            if (($nodo['tipo'] ?? '') === 'entrada') {
                $h .= '<circle cx="' . $nodo['x'] . '" cy="' . $nodo['y'] . '" r="5"/>';
            }
        }
        $h .= '</g>';
    }

    // Lugares para comer.
    if ($o['lugares']) {
        $h .= '<g class="mapa-lugares">';
        foreach ($d['lugares'] as $id => $l) {
            $h .= '<g class="mapa-lugar" data-lugar="' . esc($id) . '" transform="translate(' . $l['x'] . ' ' . $l['y'] . ')"'
                . ($o['interactivo'] ? ' tabindex="0" role="button" aria-label="' . esc($l['nombre']) . '"' : '') . '>'
                . '<circle r="11"/><path d="M-3.5 -5v4.5a2 2 0 0 0 4 0V-5M-1.5 -5v10M3.5 -5c1.5 0 1.5 4 0 5v5" /></g>';
        }
        $h .= '</g>';
    }

    // Cuadrícula de ayuda para quien edita los datos (mapa.php?coordenadas=1).
    if ($o['coordenadas']) {
        $h .= '<g class="mapa-cuadricula" aria-hidden="true">';
        for ($x = 0; $x <= $d['ancho']; $x += 50) {
            $h .= '<line x1="' . $x . '" y1="0" x2="' . $x . '" y2="' . $d['alto'] . '"/>'
                . ($x % 100 === 0 ? '<text x="' . ($x + 2) . '" y="10">' . $x . '</text>' : '');
        }
        for ($y = 0; $y <= $d['alto']; $y += 50) {
            $h .= '<line x1="0" y1="' . $y . '" x2="' . $d['ancho'] . '" y2="' . $y . '"/>'
                . ($y % 100 === 0 ? '<text x="2" y="' . ($y - 2) . '">' . $y . '</text>' : '');
        }
        $h .= '</g>';
    }

    return $h . '</svg>';
}

/** Miniatura del mapa para la portada: sin etiquetas ni calles con nombre. */
function mapa_miniatura_svg(): string
{
    return mapa_svg(['etiquetas' => false, 'calles' => false, 'vista' => '28 200 1010 450', 'titulo' => 'Vista previa del mapa del campus']);
}
