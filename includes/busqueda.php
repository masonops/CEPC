<?php
/**
 * CEPC · Sitio público
 * includes/busqueda.php — Índice del buscador.
 *
 * Arma una sola lista con todo lo que se puede buscar. El buscador de la
 * barra (assets/js/buscador.js) la recibe como JSON y filtra en el
 * navegador; buscar.php usa la misma lista para quien no tiene JavaScript.
 *
 * Para sumar algo nuevo (por ejemplo tareas desde U-Cursos), agrega otra
 * función busqueda_*() que devuelva elementos con la misma forma y súmala
 * en busqueda_indice().
 *
 * Forma de cada elemento:
 *   'grupo'    evaluacion · tarea · articulo · sala · edificio · ramo · lugar · pagina
 *   'titulo'   lo que se muestra en grande
 *   'detalle'  la línea de abajo
 *   'url'      a dónde lleva
 *   'externo'  true si sale del sitio (U-Campus)
 *   'claves'   palabras extra que también encuentran el elemento
 */

declare(strict_types=1);

/** Nombre de cada grupo, en el orden en que se muestran los resultados. */
const BUSQUEDA_GRUPOS = [
    'evaluacion' => ['titulo' => 'Evaluaciones',        'icono' => 'graduation-cap'],
    'tarea'      => ['titulo' => 'Tareas y entregas',   'icono' => 'clipboard-text'],
    'articulo'   => ['titulo' => 'Propuestas y artículos', 'icono' => 'megaphone'],
    'sala'       => ['titulo' => 'Salas',               'icono' => 'door'],
    'ramo'       => ['titulo' => 'Ramos',               'icono' => 'book-open-text'],
    'edificio'   => ['titulo' => 'Edificios',           'icono' => 'buildings'],
    'lugar'      => ['titulo' => 'Dónde comer',         'icono' => 'fork-knife'],
    'pagina'     => ['titulo' => 'En el sitio',         'icono' => 'arrow-right'],
];

function busqueda_indice(): array
{
    return array_merge(
        busqueda_evaluaciones(),
        busqueda_articulos(),
        busqueda_salas(),
        busqueda_ramos(),
        busqueda_edificios(),
        busqueda_lugares(),
        busqueda_paginas(),
    );
}

/** Solemnes, controles, exámenes y entregas desde hace un mes hasta fin de semestre. */
function busqueda_evaluaciones(): array
{
    $eventos = datos_eventos_rango(date('Y-m-d', strtotime('-30 days')), date('Y-m-d', strtotime('+180 days')));
    $items = [];
    foreach ($eventos as $ev) {
        if ($ev['categoria'] !== 'evaluacion') {
            continue;
        }
        $fecha = fecha_larga($ev['fecha']);
        $partes = array_filter([ucfirst($fecha), hora_texto($ev['hora_inicio']), $ev['lugar']]);
        $items[] = [
            'grupo'   => $ev['tipo'] === 'entrega' ? 'tarea' : 'evaluacion',
            'titulo'  => $ev['titulo'],
            'detalle' => $ev['etiqueta'] . ' · ' . implode(', ', $partes),
            'url'     => pagina('calendario') . '?fecha=' . $ev['fecha'] . '#evento-' . (int) $ev['id'],
            'claves'  => trim(($ev['ramo'] ?? '') . ' ' . datos_ramo($ev['ramo'] ?? '') . ' ' . $ev['etiqueta']),
            'fecha'   => $ev['fecha'],
        ];
    }
    return $items;
}

/** Propuestas y publicaciones del panel. */
function busqueda_articulos(): array
{
    return array_map(static fn(array $a): array => [
        'grupo'   => 'articulo',
        'titulo'  => $a['titulo'],
        'detalle' => $a['categoria_texto'] . ' · ' . ucfirst(fecha_larga($a['fecha'])),
        'url'     => $a['url'],
        'claves'  => (string) ($a['resumen'] ?? ''),
    ], datos_articulos(100));
}

/** Salas del mapa (B12, Auditorio Gorbea…). */
function busqueda_salas(): array
{
    return array_map(static fn(array $s): array => [
        'grupo'   => 'sala',
        'titulo'  => $s['nombre'],
        'detalle' => $s['edificio_nombre'] . ', ' . mb_strtolower(mapa_nombre_piso((int) $s['piso'])),
        'url'     => pagina('mapa') . '?sala=' . rawurlencode($s['codigo']),
        'claves'  => $s['codigo'],
    ], mapa_salas());
}

/** Ramos: llevan a U-Campus, donde están las secciones, profesores y horarios. */
function busqueda_ramos(): array
{
    $plantilla = (string) sitio('ucampus_ramo_url', 'https://ucampus.uchile.cl/');
    $items = [];
    foreach (contenido_ramos() as $codigo => $nombre) {
        $items[] = [
            'grupo'   => 'ramo',
            'titulo'  => $nombre,
            'detalle' => $codigo . ' · Secciones, profesores y horarios en U-Campus',
            'url'     => str_replace('{codigo}', rawurlencode((string) $codigo), $plantilla),
            'externo' => true,
            'claves'  => (string) $codigo,
        ];
    }
    return $items;
}

function busqueda_edificios(): array
{
    $items = [];
    foreach (mapa_datos()['edificios'] as $e) {
        $items[] = [
            'grupo'   => 'edificio',
            'titulo'  => $e['nombre'],
            'detalle' => 'Beauchef ' . $e['campus'] . ($e['descripcion'] !== '' ? ' · ' . $e['descripcion'] : ''),
            'url'     => pagina('mapa') . '?edificio=' . rawurlencode($e['id']),
            'claves'  => str_replace("\n", ' ', $e['corto']) . ' ' . implode(' ', $e['servicios']),
        ];
        foreach ($e['zonas'] as $z) {
            if ($z['id'] === 'pasillo') {
                continue;
            }
            $items[] = [
                'grupo'   => 'edificio',
                'titulo'  => $z['nombre'],
                'detalle' => $e['nombre'] . ($z['descripcion'] !== '' ? ' · ' . $z['descripcion'] : ''),
                'url'     => pagina('mapa') . '?edificio=' . rawurlencode($e['id']),
                'claves'  => '',
            ];
        }
    }
    return $items;
}

function busqueda_lugares(): array
{
    $etiquetas = ['vegano' => 'Vegano', 'vegetariano' => 'Vegetariano', 'junaeb' => 'Acepta Junaeb', 'barato' => 'Barato'];
    $items = [];
    foreach (mapa_datos()['lugares'] as $id => $l) {
        $tags = array_map(static fn(string $t): string => $etiquetas[$t] ?? $t, $l['etiquetas'] ?? []);
        $items[] = [
            'grupo'   => 'lugar',
            'titulo'  => $l['nombre'],
            'detalle' => implode(' · ', array_filter([ucfirst($l['tipo']), implode(', ', $tags)])),
            'url'     => pagina('mapa') . '?lugar=' . rawurlencode((string) $id),
            'claves'  => implode(' ', $l['etiquetas'] ?? []) . ' comer almuerzo comida',
        ];
    }
    return $items;
}

/** Páginas y secciones del sitio. */
function busqueda_paginas(): array
{
    $p = static fn(string $titulo, string $detalle, string $url, string $claves = ''): array =>
        ['grupo' => 'pagina', 'titulo' => $titulo, 'detalle' => $detalle, 'url' => $url, 'claves' => $claves];

    return [
        $p(es_lista() ? 'Propuestas y artículos' : 'Publicaciones', 'Todo lo que hemos publicado', pagina('articulos'), 'propuestas noticias lista programa'),
        $p('Calendario', 'Solemnes, controles, entregas y actividades', pagina('calendario'), 'fechas agenda pruebas'),
        $p('Mapa del campus', 'Edificios, salas, rutas y dónde comer', pagina('mapa'), 'plano ruta como llegar'),
        $p('Calculadora de notas', 'Cuánto necesitas para aprobar o ir a segunda', pagina('calculadora'), 'ponderacion promedio nota minima examen'),
        $p('Transparencia', 'Cuentas, asambleas, votaciones y estatutos', pagina('transparencia')),
        $p('Rendición de cuentas', 'Ingresos y gastos del año', pagina('transparencia', 'cuentas'), 'finanzas gastos plata dinero'),
        $p('Asambleas y actas', 'Fechas, asistencia y acuerdos', pagina('transparencia', 'asambleas')),
        $p('Votaciones', 'Resultados de plebiscitos y consultas', pagina('transparencia', 'votaciones'), 'plebiscito elecciones'),
        $p('Estatutos', 'Documentos oficiales del CEPC', pagina('transparencia', 'documentos'), 'reglamento documentos'),
        $p('Apuntes y solemnes antiguas', 'Banco de apuntes revisado por Docencia', pagina('inicio', 'ayuda'), 'certamenes guias'),
        $p('Bienestar', 'Salud mental, becas, BAES y apoyo', pagina('inicio', 'ayuda'), 'salud mental beca junaeb ayuda'),
        $p('Preguntas frecuentes', 'Sede, asambleas, cómo participar', pagina('inicio', 'preguntas'), 'faq ayuda'),
    ];
}

/** Texto sin tildes y en minúsculas, para comparar. */
function busqueda_normalizar(string $texto): string
{
    $texto = mb_strtolower($texto);
    $texto = strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    return trim(preg_replace('/\s+/', ' ', $texto) ?? $texto);
}

/**
 * Busca en el índice (versión de servidor, para buscar.php). Misma lógica
 * que buscador.js: todas las palabras deben aparecer; pesa más que el
 * título empiece con lo buscado.
 */
function busqueda_buscar(string $consulta, int $limite = 40): array
{
    $consulta = busqueda_normalizar($consulta);
    if ($consulta === '') {
        return [];
    }
    $palabras = explode(' ', $consulta);
    $resultados = [];
    foreach (busqueda_indice() as $item) {
        $titulo = busqueda_normalizar($item['titulo']);
        $todo = $titulo . ' ' . busqueda_normalizar($item['detalle'] . ' ' . ($item['claves'] ?? ''));
        $puntaje = 0;
        foreach ($palabras as $palabra) {
            if (!str_contains($todo, $palabra)) {
                continue 2;
            }
            $puntaje += str_starts_with($titulo, $palabra) ? 3 : (preg_match('/\b' . preg_quote($palabra, '/') . '/u', $todo) ? 2 : 1);
        }
        $resultados[] = $item + ['puntaje' => $puntaje];
    }
    usort($resultados, static fn(array $a, array $b): int => $b['puntaje'] <=> $a['puntaje']);
    return array_slice($resultados, 0, $limite);
}
