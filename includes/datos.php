<?php
/**
 * CEPC · Sitio público
 * includes/datos.php — Lo que la portada lee del panel (sólo lectura).
 *
 * Reglas de lo que se publica:
 *   · Eventos: sólo los tipos de PUBLICOS. Las reuniones de directiva y los
 *     plazos internos nunca salen del panel.
 *   · Publicaciones: sólo las de Instagram marcadas «Publicado» y con fecha
 *     de hoy o anterior (lo programado no se adelanta).
 *   · Tutorías: sólo las abiertas y futuras; sin datos de contacto del tutor.
 *   · Directiva: nombre, cargo y foto. Nada de correos ni teléfonos.
 *
 * Todas las funciones devuelven un arreglo vacío (o null) si la base no
 * está disponible, así la página se arma igual con sus estados vacíos.
 */

declare(strict_types=1);

/** Tipos de evento que se muestran al público, con su nombre y peso para el destacado. */
const TIPOS_PUBLICOS = [
    'asamblea'  => ['etiqueta' => 'Asamblea',      'peso' => 3],
    'marcha'    => ['etiqueta' => 'Día de marcha', 'peso' => 3],
    'paro'      => ['etiqueta' => 'Paro',          'peso' => 3],
    'actividad' => ['etiqueta' => 'Actividad',     'peso' => 2],
    'sunset'    => ['etiqueta' => 'Sunset',        'peso' => 2],
    'solemne'   => ['etiqueta' => 'Solemne',       'peso' => 1],
    'control'   => ['etiqueta' => 'Control',       'peso' => 1],
    'entrega'   => ['etiqueta' => 'Entrega',       'peso' => 1],
    'otro'      => ['etiqueta' => 'Evento',        'peso' => 1],
];

/**
 * Conexión de donde salen los datos: la base del panel o, en la vista
 * previa estática (GitHub Pages), la base de ejemplo de demo/ejemplo.php.
 * null si no hay ninguna.
 */
function datos_pdo(): ?PDO
{
    static $pdo = false;
    if ($pdo === false) {
        $pdo = null;
        try {
            if (sitio('demo', false)) {
                require_once SITIO_RAIZ . '/demo/ejemplo.php';
                $pdo = demo_pdo();
            } elseif (sitio('panel_raiz') !== null && function_exists('db')) {
                $pdo = db();
            }
            // Comprueba que la base esté instalada antes de usarla.
            $pdo?->query('SELECT 1 FROM eventos LIMIT 1');
        } catch (Throwable $e) {
            error_log('[sitio CEPC] ' . $e->getMessage());
            $pdo = null;
        }
    }
    return $pdo;
}

/** ¿Hay base de datos con la que trabajar? */
function datos_disponibles(): bool
{
    return datos_pdo() !== null;
}

/** Ejecuta una consulta protegida: ante cualquier error devuelve []. */
function datos_filas(string $sql, array $parametros = []): array
{
    $pdo = datos_pdo();
    if ($pdo === null) {
        return [];
    }
    try {
        $consulta = $pdo->prepare($sql);
        $consulta->execute($parametros);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('[sitio CEPC] ' . $e->getMessage());
        return [];
    }
}

/** Valor de la tabla `configuracion` del panel, o el de config.php. */
function datos_ajuste(string $clavePanel, string $claveSitio): string
{
    static $ajustes = null;
    if ($ajustes === null) {
        $ajustes = [];
        foreach (datos_filas('SELECT clave, valor FROM configuracion') as $fila) {
            $ajustes[(string) $fila['clave']] = (string) $fila['valor'];
        }
    }
    $valor = trim($ajustes[$clavePanel] ?? '');
    return $valor !== '' ? $valor : (string) sitio($claveSitio, '');
}

function datos_instagram(): string
{
    return ltrim(datos_ajuste('instagram', 'instagram'), '@');
}

function datos_correo(): string
{
    return datos_ajuste('correo_contacto', 'correo');
}

/** Nombre largo de un ramo («MA1001 · Introducción al Cálculo») si el panel lo conoce. */
function datos_ramo(?string $codigo): string
{
    $codigo = trim((string) $codigo);
    if ($codigo === '') {
        return '';
    }
    $ramos = function_exists('cat_ramos') ? cat_ramos() : [];
    return $ramos[$codigo] ?? $codigo;
}

/** Marcadores «?, ?, ?» y valores para un IN con los tipos públicos. */
function datos_tipos_in(): array
{
    $tipos = array_keys(TIPOS_PUBLICOS);
    return [implode(', ', array_fill(0, count($tipos), '?')), $tipos];
}

/** Completa un evento con lo que la vista necesita. */
function datos_preparar_evento(array $ev): array
{
    $tipo = (string) $ev['tipo'];
    $ev['etiqueta'] = TIPOS_PUBLICOS[$tipo]['etiqueta'] ?? 'Evento';
    $ev['resumen']  = trim((string) ($ev['descripcion'] ?? '')) ?: contenido_resumen_evento($tipo);
    return $ev;
}

/**
 * Próximos eventos públicos, desde hoy.
 *
 * @param int|null $dias  si se indica, sólo los de los próximos N días
 */
function datos_eventos(int $limite, ?int $dias = null): array
{
    [$marcas, $tipos] = datos_tipos_in();
    $parametros = array_merge([date('Y-m-d')], $tipos);
    $sql = "SELECT id, titulo, descripcion, tipo, fecha, fecha_fin, hora_inicio, hora_fin, lugar, ramo
              FROM eventos
             WHERE fecha >= ? AND tipo IN ($marcas)";
    if ($dias !== null) {
        $sql .= ' AND fecha <= ?';
        $parametros[] = date('Y-m-d', strtotime("+$dias days"));
    }
    $sql .= ' ORDER BY fecha ASC, hora_inicio ASC LIMIT ' . max(1, $limite);

    return array_map('datos_preparar_evento', datos_filas($sql, $parametros));
}

/** Un evento público por id (para el archivo .ics). */
function datos_evento(int $id): ?array
{
    [$marcas, $tipos] = datos_tipos_in();
    $filas = datos_filas(
        "SELECT id, titulo, descripcion, tipo, fecha, fecha_fin, hora_inicio, hora_fin, lugar, ramo
           FROM eventos WHERE id = ? AND tipo IN ($marcas)",
        array_merge([$id], $tipos)
    );
    return $filas === [] ? null : datos_preparar_evento($filas[0]);
}

/** Últimas publicaciones de Instagram ya publicadas. */
function datos_publicaciones(int $limite): array
{
    return datos_filas(
        "SELECT id, titulo, copy_texto, fecha_publicacion
           FROM publicaciones
          WHERE plataforma = 'instagram' AND estado = 'publicado' AND fecha_publicacion <= ?
          ORDER BY fecha_publicacion DESC, id DESC
          LIMIT " . max(1, $limite),
        [date('Y-m-d')]
    );
}

/** Próximas tutorías con cupo abierto. */
function datos_tutorias(int $limite): array
{
    $filas = datos_filas(
        "SELECT ramo, tipo, fecha, hora, lugar, cupos, inscritos
           FROM tutorias
          WHERE estado = 'abierto' AND fecha >= ?
          ORDER BY fecha ASC, hora ASC
          LIMIT " . max(1, $limite),
        [date('Y-m-d')]
    );
    $tiposTutoria = function_exists('cat_tipos_tutoria') ? cat_tipos_tutoria() : [
        'tutoria'       => 'Tutoría de pares',
        'ayudantia'     => 'Ayudantía extra',
        'reforzamiento' => 'Reforzamiento pre-solemne',
        'taller'        => 'Taller',
    ];
    foreach ($filas as &$t) {
        $t['tipo_texto'] = $tiposTutoria[$t['tipo']] ?? 'Tutoría';
        $t['libres']     = max(0, (int) $t['cupos'] - (int) $t['inscritos']);
    }
    return $filas;
}

/** Cuánto material revisado hay en el banco de apuntes. */
function datos_apuntes(): array
{
    $fila = datos_filas('SELECT COUNT(*) AS total, COUNT(DISTINCT ramo) AS ramos FROM apuntes WHERE verificado = 1');
    return [
        'total' => (int) ($fila[0]['total'] ?? 0),
        'ramos' => (int) ($fila[0]['ramos'] ?? 0),
    ];
}

/** La directiva vigente, en el orden del panel. */
function datos_directiva(): array
{
    $filas = datos_filas('SELECT nombre, cargo, avatar, color FROM usuarios WHERE activo = 1 ORDER BY orden ASC, id ASC');
    $panelUrl = (string) sitio('panel_url', '');

    foreach ($filas as &$p) {
        $p['foto'] = null;
        $avatar = trim((string) ($p['avatar'] ?? ''));
        if ($avatar !== '' && $panelUrl !== '' && function_exists('config')) {
            $carpeta = trim((string) config('subidas_ruta', 'uploads'), '/');
            if (is_file(CEPC_RAIZ . '/' . $carpeta . '/' . ltrim($avatar, '/'))) {
                $p['foto'] = rtrim($panelUrl, '/') . '/' . $carpeta . '/' . ltrim($avatar, '/');
            }
        }
    }
    return $filas;
}

/**
 * Lo que va en el banner de la portada, en este orden:
 *   1. El destacado manual de contenido.php, si hay uno.
 *   2. El evento más importante de las próximas semanas (asambleas, marchas
 *      y paros pesan más que una actividad, y ésta más que una solemne;
 *      a igual peso, el más próximo).
 *   3. La publicación de Instagram más reciente, si es nueva.
 *   4. El próximo evento, aunque esté lejos.
 *   5. null: la portada muestra la bienvenida.
 */
function datos_destacado(): ?array
{
    $manual = contenido_destacado_manual();
    if ($manual !== null) {
        return $manual + ['tipo' => 'noticia', 'etiqueta' => 'Noticia', 'resumen' => '', 'enlace' => '', 'enlace_texto' => 'Ver más'];
    }

    $instagram = datos_instagram();
    $urlInstagram = $instagram !== '' ? 'https://www.instagram.com/' . rawurlencode($instagram) . '/' : '';

    $desdeEvento = static fn(array $ev, string $etiqueta): array => [
        'tipo'         => 'evento',
        'etiqueta'     => $etiqueta,
        'titulo'       => $ev['titulo'],
        'resumen'      => $ev['resumen'],
        'fecha'        => $ev['fecha'],
        'hora'         => $ev['hora_inicio'],
        'lugar'        => $ev['lugar'],
        'categoria'    => $ev['etiqueta'],
        'id'           => (int) $ev['id'],
    ];

    $cercanos = datos_eventos(40, (int) sitio('destacado_dias_evento', 21));
    $importantes = array_filter($cercanos, static fn(array $ev): bool => TIPOS_PUBLICOS[$ev['tipo']]['peso'] >= 2);
    if ($importantes !== []) {
        // Ya vienen ordenados por fecha: el primero de mayor peso gana.
        usort($importantes, static fn(array $a, array $b): int =>
            TIPOS_PUBLICOS[$b['tipo']]['peso'] <=> TIPOS_PUBLICOS[$a['tipo']]['peso']
            ?: strcmp($a['fecha'] . $a['hora_inicio'], $b['fecha'] . $b['hora_inicio']));
        return $desdeEvento($importantes[0], 'Próximo evento');
    }

    $post = datos_publicaciones(1)[0] ?? null;
    $limitePost = date('Y-m-d', strtotime('-' . (int) sitio('destacado_dias_post', 10) . ' days'));
    if ($post !== null && $post['fecha_publicacion'] >= $limitePost) {
        return [
            'tipo'         => 'instagram',
            'etiqueta'     => 'Nuevo en Instagram',
            'titulo'       => $post['titulo'],
            'resumen'      => (string) $post['copy_texto'],
            'fecha'        => $post['fecha_publicacion'],
            'enlace'       => $urlInstagram,
            'enlace_texto' => 'Ver en Instagram',
        ];
    }

    $proximo = $cercanos[0] ?? (datos_eventos(1)[0] ?? null);
    return $proximo !== null ? $desdeEvento($proximo, 'Próximamente') : null;
}
