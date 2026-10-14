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

// Vista previa estática (GitHub Pages): sin panel, con datos de ejemplo.
// La activa herramientas/construir-estatico.php antes de cargar la página.
$SITIO['estatico'] = defined('SITIO_ESTATICO');
$SITIO['demo']     = $SITIO['estatico'];

/* --------------------------------------------------------------------- */
/*  Buscar el panel                                                       */
/* --------------------------------------------------------------------- */

$SITIO['panel_raiz'] = null;
// Se busca en: ./panel (panel dentro del sitio), ../cepc-panel (este repo)
// y ../panel.* (subdominio en cPanel, ej. /home/usuario/panel.midominio.cl).
$candidatas = match (true) {
    $SITIO['estatico']          => [],
    $SITIO['panel_ruta'] !== '' => [$SITIO['panel_ruta']],
    default                     => array_merge(
        [SITIO_RAIZ . '/panel', dirname(SITIO_RAIZ) . '/cepc-panel'],
        glob(dirname(SITIO_RAIZ) . '/panel.*', GLOB_ONLYDIR) ?: []
    ),
};

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

// Enlace «Acceso directiva»: si el panel vive dentro del sitio, /panel/;
// si está en un subdominio (carpeta panel.midominio.cl), https://panel.midominio.cl/.
if ($SITIO['panel_url'] === '' && $SITIO['panel_raiz'] !== null) {
    $carpetaPanel = basename($SITIO['panel_raiz']);
    if (str_starts_with($SITIO['panel_raiz'], (realpath(SITIO_RAIZ) ?: SITIO_RAIZ) . DIRECTORY_SEPARATOR)) {
        $SITIO['panel_url'] = $carpetaPanel . '/';
    } elseif (preg_match('/^panel\.[a-z0-9.-]+\.[a-z]{2,}$/i', $carpetaPanel)) {
        $SITIO['panel_url'] = 'https://' . strtolower($carpetaPanel) . '/';
    }
}

require SITIO_RAIZ . '/includes/utilidades.php';
require SITIO_RAIZ . '/includes/contenido.php';
require SITIO_RAIZ . '/includes/datos.php';
require SITIO_RAIZ . '/includes/datos-transparencia.php';
require SITIO_RAIZ . '/includes/datos-articulos.php';
require SITIO_RAIZ . '/includes/datos-roomies.php';
require SITIO_RAIZ . '/includes/roomies/vistas.php';
require SITIO_RAIZ . '/includes/mapa/mapa.php';
require SITIO_RAIZ . '/includes/busqueda.php';
