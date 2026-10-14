<?php
/**
 * CEPC · Sitio público
 * includes/datos-roomies.php — Roomies: arriendos publicados por estudiantes
 * verificados (tabla `roomies`, ver sql/roomies.sql).
 *
 * Aquí está todo lo que se lee y se calcula: catálogos (comodidades,
 * reglas…), distancia a Beauchef, metro más cercano y la forma pública de
 * cada aviso. Lo que escribe (publicar, verificar, contactar) está en
 * includes/roomies/acciones.php.
 *
 * Privacidad: nunca salen del servidor el correo, los tokens ni el punto
 * exacto. El WhatsApp sólo si la persona marcó «mostrarlo».
 */

declare(strict_types=1);

/** Desde dónde se miden las distancias: la entrada de Beauchef 850. */
const ROOMIES_BEAUCHEF = ['lat' => -33.45785, 'lng' => -70.66340, 'nombre' => 'Beauchef 850'];

const ROOMIES_TIPOS = [
    'pieza'        => ['nombre' => 'Pieza',          'largo' => 'Pieza en depto o casa',            'icono' => 'door-open'],
    'departamento' => ['nombre' => 'Depto completo', 'largo' => 'Depto completo para compartir',    'icono' => 'buildings'],
];

/** Qué puede venir incluido en el precio. */
const ROOMIES_INCLUYE = [
    'agua'     => ['texto' => 'Agua',           'icono' => 'drop'],
    'luz'      => ['texto' => 'Luz',            'icono' => 'lightning'],
    'gas'      => ['texto' => 'Gas',            'icono' => 'flame'],
    'internet' => ['texto' => 'Internet',       'icono' => 'wifi-high'],
    'gastos'   => ['texto' => 'Gastos comunes', 'icono' => 'buildings'],
];

/** Comodidades, agrupadas como se muestran en la ficha. */
const ROOMIES_COMODIDADES = [
    'amoblada'        => ['texto' => 'Pieza amoblada',      'icono' => 'bed',             'grupo' => 'pieza'],
    'bano_privado'    => ['texto' => 'Baño privado',        'icono' => 'bathtub',         'grupo' => 'pieza'],
    'closet'          => ['texto' => 'Clóset',              'icono' => 'coat-hanger',     'grupo' => 'pieza'],
    'escritorio'      => ['texto' => 'Escritorio',          'icono' => 'desk',            'grupo' => 'pieza'],
    'ventana'         => ['texto' => 'Ventana al exterior', 'icono' => 'sun-horizon',     'grupo' => 'pieza'],
    'cocina'          => ['texto' => 'Cocina equipada',     'icono' => 'cooking-pot',     'grupo' => 'casa'],
    'living'          => ['texto' => 'Living compartido',   'icono' => 'couch',           'grupo' => 'casa'],
    'lavadora'        => ['texto' => 'Lavadora',            'icono' => 'washing-machine', 'grupo' => 'casa'],
    'balcon'          => ['texto' => 'Balcón o terraza',    'icono' => 'plant',           'grupo' => 'casa'],
    'calefaccion'     => ['texto' => 'Calefacción',         'icono' => 'thermometer',     'grupo' => 'casa'],
    'ascensor'        => ['texto' => 'Ascensor',            'icono' => 'elevator',        'grupo' => 'edificio'],
    'conserjeria'     => ['texto' => 'Conserjería 24 h',    'icono' => 'shield-check',    'grupo' => 'edificio'],
    'estacionamiento' => ['texto' => 'Estacionamiento',     'icono' => 'car',             'grupo' => 'edificio'],
    'bodega'          => ['texto' => 'Bodega',              'icono' => 'package',         'grupo' => 'edificio'],
    'bicicletero'     => ['texto' => 'Bicicletero',         'icono' => 'bicycle',         'grupo' => 'edificio'],
    'gimnasio'        => ['texto' => 'Gimnasio',            'icono' => 'barbell',         'grupo' => 'edificio'],
];

const ROOMIES_GRUPOS_COMODIDAD = ['pieza' => 'La pieza', 'casa' => 'El depto o la casa', 'edificio' => 'El edificio'];

/** Reglas de convivencia: clave → [valor → texto]. */
const ROOMIES_REGLAS = [
    'mascotas'    => ['titulo' => 'Mascotas',     'icono' => 'paw-print',   'valores' => ['si' => 'Se aceptan mascotas', 'consultar' => 'Mascotas: hay que conversarlo', 'no' => 'Sin mascotas']],
    'fumar'       => ['titulo' => 'Fumar',        'icono' => 'cigarette',   'valores' => ['no' => 'No se fuma', 'terraza' => 'Sólo en la terraza o afuera', 'si' => 'Se puede fumar']],
    'visitas'     => ['titulo' => 'Visitas',      'icono' => 'users',       'valores' => ['si' => 'Visitas bienvenidas', 'aviso' => 'Visitas avisando antes', 'no' => 'Sin visitas que se queden']],
    'preferencia' => ['titulo' => 'Busca vivir con', 'icono' => 'users-three', 'valores' => ['todos' => 'Cualquier persona', 'mujeres' => 'Sólo mujeres', 'hombres' => 'Sólo hombres']],
];

/** Hábitos que la persona puede marcar sobre sí misma. */
const ROOMIES_HABITOS = [
    'madrugador' => 'Madrugador/a',
    'nocturno'   => 'Nocturno/a',
    'ordenado'   => 'Ordenado/a',
    'tranquilo'  => 'Tranquilo/a',
    'sociable'   => 'Sociable',
    'cocina'     => 'Cocino seguido',
    'estudia'    => 'Estudio en casa',
    'deporte'    => 'Hago deporte',
    'musica'     => 'Toco música',
    'gamer'      => 'Gamer',
];

/** Colores de las líneas de Metro (fondo y texto). */
const ROOMIES_LINEAS = [
    '1'  => ['#e1251b', '#ffffff'],
    '2'  => ['#f7b500', '#1d1d1f'],
    '3'  => ['#8a5a2b', '#ffffff'],
    '4'  => ['#1b4fa0', '#ffffff'],
    '4A' => ['#4aa3df', '#0e2a40'],
    '5'  => ['#0b9a4b', '#ffffff'],
    '6'  => ['#8e3a96', '#ffffff'],
];

/** Comunas del Gran Santiago para el formulario (las más cercanas primero). */
const ROOMIES_COMUNAS = [
    'Santiago', 'Estación Central', 'San Miguel', 'Pedro Aguirre Cerda', 'Quinta Normal',
    'Independencia', 'Recoleta', 'Providencia', 'Ñuñoa', 'San Joaquín', 'Cerrillos', 'Lo Prado',
    'Macul', 'La Cisterna', 'Lo Espejo', 'Maipú', 'Conchalí', 'Renca', 'Cerro Navia', 'Pudahuel',
    'La Granja', 'San Ramón', 'El Bosque', 'La Florida', 'Peñalolén', 'La Reina', 'Las Condes',
    'Vitacura', 'Lo Barnechea', 'Huechuraba', 'Quilicura', 'La Pintana', 'Puente Alto', 'San Bernardo',
];

/* --------------------------------------------------------------------- */
/*  Distancias y metro                                                    */
/* --------------------------------------------------------------------- */

/** Distancia en línea recta entre dos puntos, en metros. */
function roomies_metros(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $r = 6371000.0;
    $p1 = deg2rad($lat1);
    $p2 = deg2rad($lat2);
    $a = sin(($p2 - $p1) / 2) ** 2 + cos($p1) * cos($p2) * sin(deg2rad($lng2 - $lng1) / 2) ** 2;
    return 2 * $r * asin(min(1.0, sqrt($a)));
}

/** Estaciones de Metro (includes/roomies/metro.php). */
function roomies_estaciones(): array
{
    static $estaciones = null;
    return $estaciones ??= require SITIO_RAIZ . '/includes/roomies/metro.php';
}

/**
 * Cuánto se demora desde un punto: a Beauchef (a pie y en bici) y a las
 * estaciones de Metro más cercanas. Las calles no son rectas: se suma un
 * 25 % a la línea recta. A pie 80 m/min, en bici 230 m/min.
 */
function roomies_cercania(float $lat, float $lng): array
{
    $recorrido = static fn(float $recta): int => (int) round($recta * 1.25);
    $aBeauchef = $recorrido(roomies_metros($lat, $lng, ROOMIES_BEAUCHEF['lat'], ROOMIES_BEAUCHEF['lng']));

    $estaciones = [];
    foreach (roomies_estaciones() as [$nombre, $eLat, $eLng, $lineas]) {
        $m = $recorrido(roomies_metros($lat, $lng, $eLat, $eLng));
        $estaciones[] = ['nombre' => $nombre, 'lineas' => $lineas, 'metros' => $m, 'a_pie' => max(1, (int) ceil($m / 80)), 'lat' => $eLat, 'lng' => $eLng];
    }
    usort($estaciones, static fn(array $a, array $b): int => $a['metros'] <=> $b['metros']);

    return [
        'metros'     => $aBeauchef,
        'a_pie'      => max(1, (int) ceil($aBeauchef / 80)),
        'bici'       => max(1, (int) ceil($aBeauchef / 230)),
        'metro'      => $estaciones[0] ?? null,
        'estaciones' => array_slice($estaciones, 0, 3),
    ];
}

/** «850 m» o «2,4 km». */
function roomies_distancia_texto(int $metros): string
{
    return $metros < 1000
        ? (string) (int) (round($metros / 10) * 10) . ' m'
        : number_format($metros / 1000, 1, ',', '.') . ' km';
}

/** «12 min» o «1 h 5 min». */
function roomies_minutos_texto(int $minutos): string
{
    return $minutos < 60 ? $minutos . ' min' : intdiv($minutos, 60) . ' h' . ($minutos % 60 ? ' ' . ($minutos % 60) . ' min' : '');
}

/** Pastilla con el número de una línea de Metro. */
function roomies_linea_html(string $linea): string
{
    [$fondo, $texto] = ROOMIES_LINEAS[$linea] ?? ['#6e6e73', '#ffffff'];
    return '<span class="linea-metro" style="--metro: ' . esc($fondo) . '; --metro-texto: ' . esc($texto) . '" title="Línea ' . esc($linea) . '">L' . esc($linea) . '</span>';
}

/**
 * Punto que se publica: el real corrido entre 70 y 130 m en una dirección
 * fija para cada aviso. Así el mapa muestra el barrio, no la puerta.
 */
function roomie_punto_publico(array $r): array
{
    $semilla = crc32('roomie-' . $r['id'] . '-' . ($r['creado_en'] ?? ''));
    $angulo = deg2rad($semilla % 360);
    $metros = 70 + ($semilla >> 9) % 61;
    $lat = (float) $r['lat'];
    return [
        'lat' => round($lat + ($metros * cos($angulo)) / 111320, 5),
        'lng' => round((float) $r['lng'] + ($metros * sin($angulo)) / (111320 * cos(deg2rad($lat))), 5),
    ];
}

/* --------------------------------------------------------------------- */
/*  Lectura                                                               */
/* --------------------------------------------------------------------- */

function enlace_roomie(int $id): string
{
    return sitio('estatico', false) ? 'roomie-' . $id . '.html' : 'roomie.php?id=' . $id;
}

/** URL de una foto: tamaño 'g' (grande) o 'm' (miniatura). */
function roomie_foto_url(string $archivo, string $tam = 'm'): string
{
    return recurso($archivo . '-' . ($tam === 'g' ? 'g' : 'm') . '.jpg');
}

function roomies_lista_csv(?string $valor, array $validas): array
{
    $claves = array_filter(array_map('trim', explode(',', (string) $valor)));
    return array_values(array_filter($claves, static fn(string $c): bool => isset($validas[$c])));
}

/** Completa una fila con lo que necesitan las vistas. */
function roomie_preparar(array $r, array $fotos = []): array
{
    $r['id']             = (int) $r['id'];
    $r['precio']         = (int) $r['precio'];
    $r['gastos_comunes'] = (int) $r['gastos_comunes'];
    $r['incluye']        = roomies_lista_csv($r['incluye'] ?? '', ROOMIES_INCLUYE);
    $r['comodidades']    = roomies_lista_csv($r['comodidades'] ?? '', ROOMIES_COMODIDADES);
    $r['habitos']        = roomies_lista_csv($r['autor_habitos'] ?? '', ROOMIES_HABITOS);
    $r['gastos_incluidos'] = in_array('gastos', $r['incluye'], true);
    $r['total']          = $r['precio'] + ($r['gastos_incluidos'] ? 0 : $r['gastos_comunes']);
    $r['tipo_datos']     = ROOMIES_TIPOS[$r['tipo']] ?? ROOMIES_TIPOS['pieza'];
    $r['punto']          = roomie_punto_publico($r);
    $r['cercania']       = roomies_cercania((float) $r['lat'], (float) $r['lng']);
    $r['url']            = enlace_roomie($r['id']);
    $r['fotos']          = $fotos;
    $r['iniciales']      = iniciales_de((string) $r['autor_nombre']);
    $r['nuevo']          = !empty($r['publicado_en']) && strtotime((string) $r['publicado_en']) > strtotime('-7 days');

    $desde = (string) ($r['disponible_desde'] ?? '');
    $r['disponible_ya'] = $desde === '' || $desde <= date('Y-m-d');
    $r['disponible_texto'] = $r['disponible_ya'] ? 'Disponible ahora' : 'Desde el ' . fecha_corta_roomie($desde);
    $r['disponible_corto'] = $r['disponible_ya'] ? 'Disponible' : 'Desde ' . (int) date('j', strtotime($desde)) . ' ' . fecha_partes($desde)['mes'] . '.';

    $ingreso = (int) ($r['autor_ingreso'] ?? 0);
    $r['autor_detalle'] = implode(' · ', array_filter([
        trim((string) ($r['autor_carrera'] ?? '')),
        $ingreso > 0 ? 'generación ' . $ingreso : '',
    ]));
    return $r;
}

/** «3 de marzo» (y el año si no es el actual). */
function fecha_corta_roomie(string $fecha): string
{
    $p = fecha_partes($fecha);
    return $p['dia'] . ' de ' . $p['mes_largo'] . (substr($fecha, 0, 4) !== date('Y') ? ' de ' . substr($fecha, 0, 4) : '');
}

/** Fotos de varios avisos de una vez: id → [fotos]. */
function roomies_fotos(array $ids): array
{
    $ids = array_values(array_unique(array_map('intval', $ids)));
    if ($ids === []) {
        return [];
    }
    $filas = datos_filas(
        'SELECT roomie_id, archivo, ancho, alto, credito FROM roomies_fotos WHERE roomie_id IN (' . implode(',', array_fill(0, count($ids), '?')) . ') ORDER BY roomie_id, orden, id',
        $ids
    );
    $porAviso = [];
    foreach ($filas as $f) {
        $porAviso[(int) $f['roomie_id']][] = [
            'g'       => roomie_foto_url((string) $f['archivo'], 'g'),
            'm'       => roomie_foto_url((string) $f['archivo'], 'm'),
            'credito' => (string) ($f['credito'] ?? ''),
        ];
    }
    return $porAviso;
}

/** Avisos visibles (publicados y no vencidos), del más nuevo al más antiguo. */
function datos_roomies(): array
{
    $filas = datos_filas(
        "SELECT * FROM roomies WHERE estado = 'publicado' AND (expira_en IS NULL OR expira_en >= ?) ORDER BY publicado_en DESC, id DESC",
        [date('Y-m-d H:i:s')]
    );
    $fotos = roomies_fotos(array_column($filas, 'id'));
    return array_map(static fn(array $r): array => roomie_preparar($r, $fotos[(int) $r['id']] ?? []), $filas);
}

/** Un aviso visible, o null. */
function datos_roomie(int $id): ?array
{
    $filas = datos_filas(
        "SELECT * FROM roomies WHERE id = ? AND estado = 'publicado' AND (expira_en IS NULL OR expira_en >= ?)",
        [$id, date('Y-m-d H:i:s')]
    );
    if ($filas === []) {
        return null;
    }
    return roomie_preparar($filas[0], roomies_fotos([$id])[$id] ?? []);
}

/**
 * Lo que puede ir al navegador (JSON del listado y del mapa): sin correo,
 * sin tokens y sin el punto exacto.
 */
function roomie_publico(array $r): array
{
    return [
        'id'          => $r['id'],
        'url'         => $r['url'],
        'tipo'        => $r['tipo'],
        'titulo'      => $r['titulo'],
        'comuna'      => $r['comuna'],
        'calle'       => (string) ($r['calle'] ?? ''),
        'precio'      => $r['precio'],
        'gastos'      => $r['gastos_comunes'],
        'total'       => $r['total'],
        'incluidos'   => $r['gastos_incluidos'],
        'incluye'     => $r['incluye'],
        'comodidades' => $r['comodidades'],
        'mascotas'    => $r['mascotas'],
        'preferencia' => $r['preferencia'],
        'disponible'  => $r['disponible_ya'],
        'lat'         => $r['punto']['lat'],
        'lng'         => $r['punto']['lng'],
        'a_pie'       => $r['cercania']['a_pie'],
        'metros'      => $r['cercania']['metros'],
        'metro'       => $r['cercania']['metro'] ? [
            'nombre' => $r['cercania']['metro']['nombre'],
            'lineas' => $r['cercania']['metro']['lineas'],
            'a_pie'  => $r['cercania']['metro']['a_pie'],
        ] : null,
        'foto'        => $r['fotos'][0]['m'] ?? '',
        'publicado'   => (string) ($r['publicado_en'] ?? ''),
    ];
}

/** Avisos parecidos: misma zona o precio cercano. */
function roomies_similares(array $roomie, int $cantidad = 3): array
{
    $otros = array_filter(datos_roomies(), static fn(array $r): bool => $r['id'] !== $roomie['id']);
    usort($otros, static function (array $a, array $b) use ($roomie): int {
        $puntaje = static fn(array $r): float => abs($r['total'] - $roomie['total']) / 50000
            + roomies_metros($r['punto']['lat'], $r['punto']['lng'], $roomie['punto']['lat'], $roomie['punto']['lng']) / 1500
            + ($r['tipo'] === $roomie['tipo'] ? 0 : 1);
        return $puntaje($a) <=> $puntaje($b);
    });
    return array_slice($otros, 0, $cantidad);
}
