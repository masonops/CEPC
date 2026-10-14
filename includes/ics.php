<?php
/**
 * CEPC · Sitio público
 * includes/ics.php — Arma el archivo .ics (iCalendar) de un evento. Lo usan
 * ics.php en el servidor y herramientas/construir-estatico.php para la
 * versión de GitHub Pages.
 */

declare(strict_types=1);

/** Escapa texto según RFC 5545 (comas, punto y coma, barras y saltos de línea). */
function ics_texto(string $texto): string
{
    return str_replace(["\\", ';', ',', "\r\n", "\n"], ["\\\\", '\;', '\,', '\n', '\n'], $texto);
}

/** Corta las líneas a 75 octetos como pide el estándar, sin partir caracteres UTF-8. */
function ics_linea(string $linea): string
{
    $salida = '';
    while (strlen($linea) > 75) {
        $corte = 75;
        while ($corte > 0 && (ord($linea[$corte]) & 0xC0) === 0x80) {
            $corte--;
        }
        $salida .= substr($linea, 0, $corte) . "\r\n ";
        $linea = substr($linea, $corte);
    }
    return $salida . $linea . "\r\n";
}

/** Contenido del .ics de un evento (ya preparado por datos_preparar_evento). */
function ics_generar(array $evento, string $host): string
{
    $zona  = date_default_timezone_get();
    $fecha = str_replace('-', '', substr((string) $evento['fecha'], 0, 10));
    $hora  = trim((string) ($evento['hora_inicio'] ?? ''));

    if ($hora === '') {
        // Sin hora: evento de día completo.
        $fin     = str_replace('-', '', substr((string) ($evento['fecha_fin'] ?: $evento['fecha']), 0, 10));
        $inicio  = 'DTSTART;VALUE=DATE:' . $fecha;
        $termino = 'DTEND;VALUE=DATE:' . date('Ymd', strtotime($fin . ' +1 day'));
    } else {
        $desde   = new DateTimeImmutable(substr((string) $evento['fecha'], 0, 10) . ' ' . substr($hora, 0, 5));
        $horaFin = trim((string) ($evento['hora_fin'] ?? ''));
        $hasta   = $horaFin !== ''
            ? new DateTimeImmutable(substr((string) ($evento['fecha_fin'] ?: $evento['fecha']), 0, 10) . ' ' . substr($horaFin, 0, 5))
            : $desde->modify('+2 hours');
        if ($hasta <= $desde) {
            $hasta = $desde->modify('+1 hour');
        }
        $inicio  = 'DTSTART;TZID=' . $zona . ':' . $desde->format('Ymd\THis');
        $termino = 'DTEND;TZID=' . $zona . ':' . $hasta->format('Ymd\THis');
    }

    $lineas = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//CEPC//Sitio publico//ES',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        'UID:evento-' . (int) $evento['id'] . '@' . $host,
        'DTSTAMP:' . gmdate('Ymd\THis\Z'),
        $inicio,
        $termino,
        'SUMMARY:' . ics_texto((string) $evento['titulo']),
        'DESCRIPTION:' . ics_texto((string) $evento['resumen']),
    ];
    if (trim((string) $evento['lugar']) !== '') {
        $lineas[] = 'LOCATION:' . ics_texto((string) $evento['lugar']);
    }
    array_push($lineas, 'END:VEVENT', 'END:VCALENDAR');

    return implode('', array_map('ics_linea', $lineas));
}

/** Nombre de archivo legible: «cepc-asamblea-general-de-plan-comun.ics». */
function ics_nombre_archivo(array $evento): string
{
    $ascii  = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $evento['titulo']) : false;
    $nombre = 'cepc-' . substr(preg_replace('/[^a-z0-9]+/', '-', strtolower($ascii ?: 'evento')) ?? 'evento', 0, 60);
    return trim($nombre, '-') . '.ics';
}
