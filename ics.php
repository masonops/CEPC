<?php
/**
 * CEPC · Sitio público
 * ics.php — Descarga un evento de la agenda como archivo .ics para
 * agregarlo al calendario del teléfono (iPhone, Android, Google, Outlook).
 *
 * Uso: ics.php?id=12. Sólo entrega eventos de tipos públicos.
 */

declare(strict_types=1);

require __DIR__ . '/includes/arranque.php';
require __DIR__ . '/includes/ics.php';

$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$evento = $id > 0 ? datos_evento($id) : null;

if ($evento === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Este evento no existe o ya no está publicado.';
    exit;
}

$host = preg_replace('/[^A-Za-z0-9.\-]/', '', explode(':', (string) ($_SERVER['HTTP_HOST'] ?? 'cepc.local'))[0]) ?: 'cepc.local';

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . ics_nombre_archivo($evento) . '"');
header('Cache-Control: no-store');

echo ics_generar($evento, $host);
