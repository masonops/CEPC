<?php
/**
 * CEPC · Sitio público
 * includes/arranque.php — Punto de entrada común de las páginas públicas.
 *
 * Carga la configuración del sitio y, si encuentra el panel, reutiliza su
 * conexión a la base de datos (mismas credenciales del .env del panel).
 * Si el panel no está o la base no responde, el sitio se muestra igual
 * con sus textos fijos y estados vacíos: nunca una página en blanco.
 *
 * Debe incluirse desde el ámbito global (no dentro de una función), porque
 * el config.php del panel define variables globales.
 */

declare(strict_types=1);

define('SITIO_RAIZ', dirname(__DIR__));

$SITIO = require SITIO_RAIZ . '/config.php';

/* --------------------------------------------------------------------- */
/*  Buscar el panel                                                       */
/* --------------------------------------------------------------------- */

$SITIO['panel_raiz'] = null;
$candidatas = $SITIO['panel_ruta'] !== ''
    ? [$SITIO['panel_ruta']]
    : [SITIO_RAIZ . '/panel', dirname(SITIO_RAIZ) . '/cepc-panel'];

foreach ($candidatas as $candidata) {
    if (is_file($candidata . '/config/database.php')) {
        $SITIO['panel_raiz'] = realpath($candidata) ?: $candidata;
        break;
    }
}

if ($SITIO['panel_raiz'] !== null) {
    define('CEPC_RAIZ', $SITIO['panel_raiz']);
    require CEPC_RAIZ . '/config/config.php';
    require CEPC_RAIZ . '/includes/compatibilidad.php';
    require CEPC_RAIZ . '/config/database.php';
    require CEPC_RAIZ . '/includes/catalogos.php';

    // El panel muestra errores en modo debug; el sitio público nunca.
    ini_set('display_errors', '0');
} else {
    date_default_timezone_set('America/Santiago');
}

// Enlace «Acceso directiva»: si el panel vive dentro del sitio, /panel/.
if ($SITIO['panel_url'] === '' && $SITIO['panel_raiz'] !== null
    && str_starts_with($SITIO['panel_raiz'], (realpath(SITIO_RAIZ) ?: SITIO_RAIZ) . DIRECTORY_SEPARATOR)) {
    $SITIO['panel_url'] = basename($SITIO['panel_raiz']) . '/';
}

require SITIO_RAIZ . '/includes/utilidades.php';
require SITIO_RAIZ . '/includes/contenido.php';
require SITIO_RAIZ . '/includes/datos.php';
