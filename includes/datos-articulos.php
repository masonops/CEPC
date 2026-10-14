<?php
/**
 * CEPC · Sitio público
 * includes/datos-articulos.php — Propuestas y artículos (sólo lectura).
 *
 * Se escriben desde el panel en la tabla `articulos` (ver sql/articulos.sql).
 * Si la tabla todavía no existe, todo devuelve vacío y la portada muestra
 * un aviso en vez de la sección.
 */

declare(strict_types=1);

/** Categorías de publicación y cómo se llaman en pantalla. */
const ARTICULO_CATEGORIAS = [
    'propuesta' => 'Propuesta',
    'fechas'    => 'Fechas importantes',
    'noticia'   => 'Noticia',
];

/** Completa una fila con lo que la vista necesita. */
function articulo_preparar(array $a): array
{
    $a['id']              = (int) $a['id'];
    $a['categoria_texto'] = ARTICULO_CATEGORIAS[$a['categoria']] ?? 'Publicación';
    $a['fecha']           = substr((string) ($a['publicado_en'] ?? ''), 0, 10);
    $a['url']             = enlace_articulo($a['id']);
    return $a;
}

/** Publicaciones publicadas, de la más nueva a la más antigua (las destacadas primero si se pide). */
function datos_articulos(int $limite = 50, ?string $categoria = null, bool $destacadasPrimero = false): array
{
    $parametros = [date('Y-m-d H:i:s')];
    $sql = "SELECT id, titulo, resumen, categoria, imagen, autor, destacado, publicado_en
              FROM articulos
             WHERE estado = 'publicado' AND publicado_en <= ?";
    if ($categoria !== null && isset(ARTICULO_CATEGORIAS[$categoria])) {
        $sql .= ' AND categoria = ?';
        $parametros[] = $categoria;
    }
    $sql .= ' ORDER BY ' . ($destacadasPrimero ? 'destacado DESC, ' : '') . 'publicado_en DESC, id DESC LIMIT ' . max(1, $limite);
    return array_map('articulo_preparar', datos_filas($sql, $parametros));
}

/** Una publicación completa, o null si no existe o no está publicada. */
function datos_articulo(int $id): ?array
{
    $filas = datos_filas(
        "SELECT id, titulo, resumen, cuerpo, categoria, imagen, autor, destacado, publicado_en
           FROM articulos WHERE id = ? AND estado = 'publicado' AND publicado_en <= ?",
        [$id, date('Y-m-d H:i:s')]
    );
    return $filas === [] ? null : articulo_preparar($filas[0]);
}

/**
 * Convierte el cuerpo (texto simple) en HTML seguro: párrafos, subtítulos
 * («## ») y listas («- »). Todo el texto se escapa; no se acepta HTML.
 */
function articulo_html(string $cuerpo): string
{
    $bloques = preg_split('/\R{2,}/u', trim($cuerpo)) ?: [];
    $html = '';
    foreach ($bloques as $bloque) {
        $lineas = preg_split('/\R/u', trim($bloque)) ?: [];
        if (str_starts_with($lineas[0], '## ')) {
            $html .= '<h2>' . esc(substr($lineas[0], 3)) . '</h2>';
            $lineas = array_slice($lineas, 1);
            if ($lineas === []) {
                continue;
            }
        }
        if (array_filter($lineas, static fn(string $l): bool => !str_starts_with(ltrim($l), '- ')) === []) {
            $html .= '<ul>' . implode('', array_map(static fn(string $l): string => '<li>' . esc(substr(ltrim($l), 2)) . '</li>', $lineas)) . '</ul>';
        } else {
            $html .= '<p>' . implode('<br>', array_map('esc', $lineas)) . '</p>';
        }
    }
    return $html;
}
