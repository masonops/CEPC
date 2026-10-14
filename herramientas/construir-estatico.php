<?php
/**
 * CEPC · Sitio público
 * herramientas/construir-estatico.php — Arma la versión estática (HTML) de
 * la portada para GitHub Pages, que no ejecuta PHP.
 *
 * Usa los datos de ejemplo de demo/ejemplo.php con fechas relativas a hoy,
 * muestra la franja «Vista previa con datos de ejemplo» y genera un .ics por
 * cada fecha de la agenda.
 *
 * Uso:  php herramientas/construir-estatico.php [carpeta de salida]
 *       (por defecto _site/)
 */

declare(strict_types=1);

const SITIO_ESTATICO = true;

$raiz   = dirname(__DIR__);
$salida = rtrim($argv[1] ?? $raiz . '/_site', '/');

if (is_dir($salida)) {
    fwrite(STDERR, "La carpeta $salida ya existe; bórrala antes de construir.\n");
    exit(1);
}

/** Copia una carpeta completa. */
function copiar_carpeta(string $origen, string $destino): void
{
    mkdir($destino, 0775, true);
    foreach (scandir($origen) ?: [] as $nombre) {
        if ($nombre === '.' || $nombre === '..') {
            continue;
        }
        $de = $origen . '/' . $nombre;
        $a  = $destino . '/' . $nombre;
        is_dir($de) ? copiar_carpeta($de, $a) : copy($de, $a);
    }
}

// La página: se ejecuta index.php y se guarda lo que imprime.
ob_start();
require $raiz . '/index.php';
$html = (string) ob_get_clean();

if (!str_contains($html, 'aviso-demo')) {
    fwrite(STDERR, "La página no salió en modo vista previa; revisa includes/arranque.php.\n");
    exit(1);
}

copiar_carpeta($raiz . '/assets', $salida . '/assets');
file_put_contents($salida . '/index.html', $html);
file_put_contents($salida . '/.nojekyll', '');

// Un .ics por cada fecha que aparece en la página.
require_once $raiz . '/includes/ics.php';
mkdir($salida . '/ics', 0775, true);
$host = (getenv('GITHUB_REPOSITORY_OWNER') ?: 'cepc') . '.github.io';
$total = 0;
foreach (datos_eventos(100) as $evento) {
    file_put_contents($salida . '/ics/evento-' . (int) $evento['id'] . '.ics', ics_generar($evento, $host));
    $total++;
}

echo "Listo: $salida/index.html y $total archivos .ics\n";
