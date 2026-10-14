<?php
/**
 * CEPC · Sitio público
 * demo/ejemplo.php — Base de ejemplo para la vista previa de GitHub Pages.
 *
 * Crea en memoria (SQLite) las mismas tablas que lee includes/datos.php,
 * con los datos de ejemplo del panel. Las fechas son relativas al día en
 * que se construye la página, así la vista previa siempre parece al día.
 *
 * Nada de esto se usa en el servidor real: allí los datos salen del panel.
 */

declare(strict_types=1);

function demo_pdo(): PDO
{
    $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $pdo->exec("
        CREATE TABLE configuracion (clave TEXT PRIMARY KEY, valor TEXT);
        CREATE TABLE eventos (
            id INTEGER PRIMARY KEY AUTOINCREMENT, titulo TEXT, descripcion TEXT, tipo TEXT,
            fecha TEXT, fecha_fin TEXT, hora_inicio TEXT, hora_fin TEXT, lugar TEXT, ramo TEXT
        );
        CREATE TABLE publicaciones (
            id INTEGER PRIMARY KEY AUTOINCREMENT, titulo TEXT, plataforma TEXT,
            fecha_publicacion TEXT, estado TEXT, copy_texto TEXT
        );
        CREATE TABLE tutorias (
            id INTEGER PRIMARY KEY AUTOINCREMENT, ramo TEXT, tipo TEXT, fecha TEXT, hora TEXT,
            lugar TEXT, cupos INTEGER, inscritos INTEGER, estado TEXT
        );
        CREATE TABLE apuntes (id INTEGER PRIMARY KEY AUTOINCREMENT, ramo TEXT, verificado INTEGER);
        CREATE TABLE usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT, cargo TEXT, avatar TEXT,
            color TEXT, orden INTEGER, activo INTEGER
        );
    ");

    $dia = static fn(int $dias): string => date('Y-m-d', strtotime("$dias days"));

    $insertar = static function (PDO $pdo, string $tabla, array $columnas, array $filas): void {
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $tabla,
            implode(', ', $columnas),
            implode(', ', array_fill(0, count($columnas), '?'))
        );
        $consulta = $pdo->prepare($sql);
        foreach ($filas as $fila) {
            $consulta->execute($fila);
        }
    };

    $insertar($pdo, 'configuracion', ['clave', 'valor'], [
        ['instagram', 'cepc.uchile'],
        ['correo_contacto', 'cepc@uchile.cl'],
    ]);

    // [título, tipo, en cuántos días, hora, lugar, ramo]
    $eventos = [
        ['Reforzamiento pre-solemne MA1002', 'actividad', 1, '16:00', 'Sala B21', 'MA1002'],
        ['Solemne 1 · MA1001 Introducción al Cálculo', 'solemne', 2, '18:30', 'Auditorio Gorbea', 'MA1001'],
        ['Asamblea general de Plan Común', 'asamblea', 3, '13:00', 'Auditorio Gorbea', null],
        ['Fecha 2 · Torneo interno de fútbol', 'actividad', 4, '16:00', 'Cancha Beauchef', null],
        ['Solemne 1 · FI1001 Física Clásica', 'solemne', 5, '18:30', 'Sala B12', 'FI1001'],
        ['Entrega informe · EI1000', 'entrega', 7, '23:59', 'U-Cursos', 'EI1000'],
        ['Conversatorio: salud mental en Plan Común', 'actividad', 8, '15:00', 'Sala de conferencias', null],
        ['Control 2 · CC1002 Programación', 'control', 9, '14:30', 'Labs Beauchef', 'CC1002'],
        ['Sunset de bienvenida al segundo semestre', 'sunset', 11, '17:00', 'Patio Beauchef 851', null],
        ['Día de marcha por el financiamiento de la educación', 'marcha', 13, '11:00', 'Plaza Baquedano', null],
        ['Feria de organizaciones estudiantiles', 'actividad', 16, '12:00', 'Patio Beauchef 851', null],
    ];
    $insertar($pdo, 'eventos', ['titulo', 'tipo', 'fecha', 'hora_inicio', 'lugar', 'ramo'], array_map(
        static fn(array $e): array => [$e[0], $e[1], $dia($e[2]), $e[3], $e[4], $e[5]],
        $eventos
    ));

    // [título, hace cuántos días, texto]
    $publicaciones = [
        ['Calendario de solemnes del semestre', 6, 'Carrusel con todas las fechas de solemnes de Plan Común. Guárdalo para tenerlo a mano.'],
        ['Cómo pedir un reembolso al CEPC', 13, 'Explicativo en 4 láminas con el paso a paso.'],
        ['Red de apoyo psicológico de la facultad', 20, 'Dónde pedir hora y qué esperar de la primera sesión.'],
        ['Banco de apuntes: ya está en línea', 27, 'Link en la bio, ordenado por ramo.'],
    ];
    $insertar($pdo, 'publicaciones', ['titulo', 'plataforma', 'fecha_publicacion', 'estado', 'copy_texto'], array_map(
        static fn(array $p): array => [$p[0], 'instagram', $dia(-$p[1]), 'publicado', $p[2]],
        $publicaciones
    ));

    // [ramo, tipo, en cuántos días, hora, lugar, cupos, inscritos]
    $tutorias = [
        ['MA1001', 'reforzamiento', 1, '16:00', 'Sala B21', 30, 24],
        ['FI1001', 'reforzamiento', 4, '17:00', 'Sala B12', 30, 18],
        ['CC1002', 'ayudantia', 8, '14:30', 'Lab 3', 25, 9],
    ];
    $insertar($pdo, 'tutorias', ['ramo', 'tipo', 'fecha', 'hora', 'lugar', 'cupos', 'inscritos', 'estado'], array_map(
        static fn(array $t): array => [$t[0], $t[1], $dia($t[2]), $t[3], $t[4], $t[5], $t[6], 'abierto'],
        $tutorias
    ));

    $ramos = ['MA1001', 'MA1001', 'MA1002', 'MA1101', 'FI1001', 'FI1001', 'QU1000', 'CC1002', 'MA2001', 'MA1102'];
    $insertar($pdo, 'apuntes', ['ramo', 'verificado'], array_map(static fn(string $r): array => [$r, 1], $ramos));

    $directiva = [
        ['Mari', 'Presidenta'], ['Mikel', 'Vicepresidente'], ['Benja', 'Secretario'],
        ['Demian', 'Finanzas'], ['Trini', 'Comunicaciones'], ['Luciano', 'Bienestar'],
        ['Eli', 'Disidencias'], ['Viole', 'Docencia'], ['Tami', 'Docencia'],
    ];
    $insertar($pdo, 'usuarios', ['nombre', 'cargo', 'orden', 'activo'], array_map(
        static fn(array $p, int $i): array => [$p[0], $p[1], $i + 1, 1],
        $directiva,
        array_keys($directiva)
    ));

    return $pdo;
}
