<?php
/**
 * CEPC · Sitio público
 * includes/mapa/edificios.php — Nombres, descripción, pisos y salas de cada
 * edificio del mapa. La forma de cada uno está en geometria.php (misma clave).
 *
 * Cómo agregar una sala: búscala en su edificio y piso, y suma una fila:
 *     ['codigo' => 'B12', 'nombre' => 'Sala B12', 'tipo' => 'sala', 'capacidad' => 90],
 * Tipos de sala: sala · auditorio · laboratorio · oficina · servicio · social.
 *
 * Campos opcionales:
 *   'corto'      nombre corto para la etiqueta del mapa; "\n" lo parte en
 *                dos líneas. La etiqueta se ubica sola dentro del edificio y
 *                se gira o se esconde si no cabe.
 *   'servicios'  lista simple: baños, cajero, ascensor…
 *   'zonas'      partes interiores con nombre (la forma está en
 *                geometria.php → 'zonas'), por ejemplo la Biblioteca.
 *
 * IMPORTANTE: los PISOS Y SALAS de abajo son DE EJEMPLO (el plano de
 * referencia no trae subterráneos ni pisos altos). Mientras
 * MAPA_SALAS_DE_EJEMPLO sea true, el mapa lo avisa. Cámbialo a false
 * cuando estén cargadas las reales.
 */

declare(strict_types=1);

defined('MAPA_SALAS_DE_EJEMPLO') || define('MAPA_SALAS_DE_EJEMPLO', true);

/** Atajo para escribir salas en una línea. */
if (!function_exists('sala')) {
    function sala(string $codigo, string $nombre, string $tipo = 'sala', ?int $capacidad = null): array
    {
        return ['codigo' => $codigo, 'nombre' => $nombre, 'tipo' => $tipo, 'capacidad' => $capacidad];
    }
}

return [

    /* --- Beauchef 850 ---------------------------------------------------- */

    'escuela' => [
        'nombre'      => 'Edificio Escuela',
        'corto'       => 'Escuela',
        'campus'      => '850',
        'descripcion' => 'El edificio frente a Beauchef 850. Un pasillo central lo cruza desde la entrada hasta la Sobria y lo divide en dos alas: hacia el norte la Biblioteca y hacia el sur el Hall Sur. A cada lado del pasillo hay escaleras al segundo piso, y junto a la del lado norte, una bajada a la Cafeta.',
        'servicios'   => ['Baños', 'Auditorio Gorbea', 'Escaleras al 2.º piso', 'Bajada a la Cafeta'],
        'zonas' => [
            'biblioteca' => ['nombre' => 'Biblioteca', 'descripcion' => 'Biblioteca y salas de estudio, en el extremo norte.'],
            'hall-sur'   => ['nombre' => 'Hall Sur', 'descripcion' => 'Extremo sur del edificio.'],
            'pasillo'    => ['nombre' => 'Pasillo central', 'descripcion' => 'Va de la entrada de Beauchef 850 a la Sobria.'],
        ],
        'pisos' => [
            ['nivel' => 1, 'salas' => [
                sala('GORBEA', 'Auditorio Gorbea', 'auditorio', 300),
                sala('B11', 'Sala B11', 'sala', 90),
                sala('B12', 'Sala B12', 'sala', 90),
                sala('B13', 'Sala B13', 'sala', 60),
            ]],
            ['nivel' => 2, 'salas' => [
                sala('B21', 'Sala B21', 'sala', 90),
                sala('B22', 'Sala B22', 'sala', 60),
                sala('B23', 'Sala B23', 'sala', 60),
            ]],
            ['nivel' => 3, 'salas' => [
                sala('B31', 'Sala B31', 'sala', 50),
                sala('B32', 'Sala B32', 'sala', 50),
            ]],
        ],
    ],

    'sobria' => [
        'nombre'      => 'Sobria y Cafeta',
        'corto'       => 'Sobria',
        'campus'      => '850',
        'descripcion' => 'Espacios para juntarse. En el primer piso, la Sobria; abajo, en el piso −1, la Cafeta. A la Cafeta se baja por escaleras en ambos extremos y desde el pasillo central de la Escuela.',
        'servicios'   => ['Bajadas a la Cafeta por ambos extremos'],
        'pisos' => [
            ['nivel' => 1, 'salas' => [sala('SOBRIA', 'Sobria', 'social')]],
            ['nivel' => -1, 'salas' => [sala('CAFETA', 'Cafeta', 'social')]],
        ],
    ],

    'fisica' => [
        'nombre'      => 'Departamento de Física',
        'corto'       => 'Física',
        'campus'      => '850',
        'descripcion' => 'Departamento de Física, hacia Blanco Encalada.',
        'servicios'   => ['Baños'],
        'pisos' => [
            ['nivel' => 1, 'salas' => [sala('A1', 'Sala A1', 'sala', 70), sala('A2', 'Sala A2', 'sala', 70)]],
            ['nivel' => 2, 'salas' => [sala('A3', 'Sala A3', 'sala', 60)]],
            ['nivel' => 3, 'salas' => [sala('LABFIS', 'Laboratorio de Física', 'laboratorio', 30)]],
        ],
    ],

    'quimica' => [
        'nombre'      => 'Química',
        'campus'      => '850',
        'descripcion' => 'Edificio de Química, hacia Tupper.',
        'servicios'   => ['Baños'],
        'pisos'       => [['nivel' => 1, 'salas' => []], ['nivel' => 2, 'salas' => []], ['nivel' => 3, 'salas' => []]],
    ],

    'justicia-espada' => [
        'nombre'      => 'Justicia Espada',
        'corto'       => "Justicia\nEspada",
        'campus'      => '850',
        'descripcion' => 'Edificio en el centro del campus, rodeado por los patios.',
        'servicios'   => ['Cajero automático', 'Baños'],
        'pisos' => [
            ['nivel' => 1, 'salas' => [sala('CONF', 'Sala de conferencias', 'auditorio', 80)]],
            ['nivel' => 2, 'salas' => []],
            ['nivel' => 3, 'salas' => []],
        ],
    ],

    'civil' => [
        'nombre'      => 'Ingeniería Civil y Geofísica',
        'corto'       => 'Civil',
        'campus'      => '850',
        'descripcion' => 'Departamentos de Ingeniería Civil y Geofísica, y el Centro Sismológico Nacional.',
        'pisos'       => [['nivel' => 1, 'salas' => []], ['nivel' => 2, 'salas' => []], ['nivel' => 3, 'salas' => []]],
    ],

    'electrica' => [
        'nombre'      => 'Ingeniería Eléctrica',
        'corto'       => 'Eléctrica',
        'campus'      => '850',
        'descripcion' => 'Departamento de Ingeniería Eléctrica, Centro de Energía y AMTC.',
        'pisos'       => [['nivel' => 1, 'salas' => []], ['nivel' => 2, 'salas' => []], ['nivel' => 3, 'salas' => []]],
    ],

    'geologia' => [
        'nombre'      => 'Geología',
        'campus'      => '850',
        'descripcion' => 'Departamento de Geología, hacia Plaza Ercilla.',
        'pisos'       => [['nivel' => 1, 'salas' => []], ['nivel' => 2, 'salas' => []]],
    ],

    'anexo-idiem' => [
        'nombre'      => 'Anexo IDIEM',
        'corto'       => 'Anexo',
        'campus'      => '850',
        'descripcion' => 'Anexo del IDIEM, con salida a Plaza Ercilla.',
        'pisos'       => [['nivel' => 1, 'salas' => []]],
    ],

    'idiem' => [
        'nombre'      => 'IDIEM',
        'campus'      => '850',
        'descripcion' => 'Centro de Investigación, Desarrollo e Innovación de Estructuras y Materiales.',
        'pisos'       => [['nivel' => 1, 'salas' => []], ['nivel' => 2, 'salas' => []]],
    ],

    'casino' => [
        'nombre'      => 'Casino',
        'campus'      => '850',
        'descripcion' => 'Casino de la facultad, al otro lado de Blanco Encalada.',
        'pisos'       => [],
    ],

    /* --- Beauchef 851 ---------------------------------------------------- */

    'industrial' => [
        'nombre'      => 'Beauchef 851 · Ingeniería Industrial',
        'corto'       => 'Industrial',
        'campus'      => '851',
        'descripcion' => 'Ala de Beauchef 851 que da a la calle Beauchef: Departamento de Ingeniería Industrial.',
        'servicios'   => ['Baños', 'Escalera a pisos inferiores'],
        'pisos' => [
            ['nivel' => -2, 'salas' => [sala('851-201', 'Sala -201', 'sala', 80)]],
            ['nivel' => -1, 'salas' => [sala('851-101', 'Sala -101', 'sala', 80), sala('851-102', 'Sala -102', 'sala', 80)]],
            ['nivel' => 1, 'salas' => []],
            ['nivel' => 2, 'salas' => []],
        ],
    ],

    'edificio-norte' => [
        'nombre'      => 'Edificio Norte',
        'corto'       => 'Edificio Norte',
        'campus'      => '851',
        'descripcion' => 'Beauchef 851. Departamentos de Ingeniería Matemática y Ciencias de la Computación, y el CMM.',
        'pisos' => [
            ['nivel' => 1, 'salas' => [sala('LAB1', 'Laboratorio de computación 1', 'laboratorio', 40), sala('LAB3', 'Lab 3', 'laboratorio', 40)]],
            ['nivel' => 2, 'salas' => []],
        ],
    ],

    'edificio-oriente' => [
        'nombre'      => 'Edificio Oriente',
        'corto'       => 'Edificio Oriente',
        'campus'      => '851',
        'descripcion' => 'Beauchef 851. Departamentos de Ingeniería Mecánica y de Ingeniería Química, Biotecnología y Materiales.',
        'pisos'       => [['nivel' => 1, 'salas' => []], ['nivel' => 2, 'salas' => []]],
    ],

    'etigny' => [
        'nombre'      => "Auditorio d'Etigny",
        'corto'       => "d'Etigny",
        'campus'      => '851',
        'descripcion' => 'Auditorio de Beauchef 851.',
        'pisos'       => [['nivel' => 1, 'salas' => [sala('ETIGNY', "Auditorio d'Etigny", 'auditorio', 250)]]],
    ],

    /* --- Espacios abiertos ----------------------------------------------- */

    'cancha' => [
        'nombre'      => 'Cancha',
        'campus'      => '850',
        'tipo'        => 'espacio',
        'descripcion' => 'Cancha en el patio central, entre la Sobria y Justicia Espada.',
        'pisos'       => [],
    ],

    'ebria' => [
        'nombre'      => 'Ebria',
        'campus'      => '850',
        'tipo'        => 'espacio',
        'descripcion' => 'Espacio para juntarse, entre la cancha y Justicia Espada.',
        'pisos'       => [],
    ],
];
