<?php
/**
 * CEPC · Sitio público
 * includes/utilidades.php — Escape, rutas, íconos y fechas en español.
 */

declare(strict_types=1);

/** Escapa texto para HTML. */
function esc(mixed $valor): string
{
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Lee un ajuste de config.php. */
function sitio(string $clave, mixed $porDefecto = null): mixed
{
    return $GLOBALS['SITIO'][$clave] ?? $porDefecto;
}

/** URL de un archivo estático con versión para que el navegador no use uno viejo. */
function recurso(string $ruta): string
{
    $absoluta = SITIO_RAIZ . '/' . ltrim($ruta, '/');
    $version  = is_file($absoluta) ? (string) filemtime($absoluta) : '1';
    return ltrim($ruta, '/') . '?v=' . $version;
}

/**
 * Ícono de Phosphor (assets/iconos/*.svg) incrustado en línea, para que
 * tome el color del texto. Licencia MIT, ver assets/iconos/LICENCIA.txt.
 */
function icono(string $nombre, string $clase = ''): string
{
    static $cache = [];
    if (!isset($cache[$nombre])) {
        $ruta = SITIO_RAIZ . '/assets/iconos/' . basename($nombre) . '.svg';
        $cache[$nombre] = is_file($ruta) ? (string) file_get_contents($ruta) : '';
    }
    if ($cache[$nombre] === '') {
        return '';
    }
    $atributos = 'class="icono' . ($clase !== '' ? ' ' . esc($clase) : '') . '" aria-hidden="true" focusable="false"';
    return preg_replace('/^<svg /', '<svg ' . $atributos . ' ', $cache[$nombre], 1) ?? '';
}

/**
 * Enlace a una página del sitio: «calendario.php» en el servidor y
 * «calendario.html» en la versión estática. 'inicio' es la portada.
 */
function pagina(string $nombre, string $ancla = ''): string
{
    $archivo = $nombre === 'inicio'
        ? (sitio('estatico', false) ? 'index.html' : './')
        : $nombre . (sitio('estatico', false) ? '.html' : '.php');
    return $archivo . ($ancla !== '' ? '#' . $ancla : '');
}

/**
 * Enlace para agregar un evento al calendario: ics.php en el servidor, o
 * el .ics ya generado en la versión estática.
 */
function enlace_ics(int $id): string
{
    return sitio('estatico', false) ? 'ics/evento-' . $id . '.ics' : 'ics.php?id=' . $id;
}

/* --------------------------------------------------------------------- */
/*  Fechas                                                                */
/* --------------------------------------------------------------------- */

function meses(): array
{
    return ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
        'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
}

function dias_semana(): array
{
    return ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
}

/** Partes de una fecha para dibujar el «calendario»: día, mes corto y día de la semana. */
function fecha_partes(string $fecha): array
{
    $t = strtotime($fecha) ?: time();
    return [
        'dia'       => date('j', $t),
        'mes'       => mb_substr(meses()[(int) date('n', $t)], 0, 3),
        'mes_largo' => meses()[(int) date('n', $t)],
        'semana'    => dias_semana()[(int) date('w', $t)],
    ];
}

/** «jueves 16 de octubre». */
function fecha_larga(string $fecha): string
{
    $p = fecha_partes($fecha);
    return $p['semana'] . ' ' . $p['dia'] . ' de ' . $p['mes_largo'];
}

/** «Hoy», «Mañana», «En 5 días»… o null si falta más de dos semanas. */
function fecha_cercania(string $fecha): ?string
{
    $hoy  = new DateTimeImmutable('today');
    $dia  = new DateTimeImmutable(substr($fecha, 0, 10));
    $dias = (int) $hoy->diff($dia)->format('%r%a');

    return match (true) {
        $dias === 0 => 'Hoy',
        $dias === 1 => 'Mañana',
        $dias > 1 && $dias <= 14 => 'En ' . $dias . ' días',
        $dias === -1 => 'Ayer',
        $dias < -1 && $dias >= -14 => 'Hace ' . abs($dias) . ' días',
        default => null,
    };
}

/** «18:30 h»; vacío si no hay hora. */
function hora_texto(?string $hora): string
{
    $hora = trim((string) $hora);
    return $hora === '' ? '' : substr($hora, 0, 5) . ' h';
}

/** Iniciales para el avatar de respaldo. */
function iniciales_de(string $nombre): string
{
    $partes = preg_split('/\s+/u', trim($nombre)) ?: [];
    $letras = '';
    foreach (array_slice($partes, 0, 2) as $parte) {
        $letras .= mb_substr($parte, 0, 1);
    }
    return mb_strtoupper($letras);
}
