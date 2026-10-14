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
        CREATE TABLE proyectos (
            id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT, descripcion TEXT, presupuesto REAL,
            estado TEXT, anio INTEGER
        );
        CREATE TABLE movimientos (
            id INTEGER PRIMARY KEY AUTOINCREMENT, tipo TEXT, monto REAL, descripcion TEXT,
            categoria TEXT, fecha TEXT, proyecto_id INTEGER
        );
        CREATE TABLE asambleas (
            id INTEGER PRIMARY KEY AUTOINCREMENT, titulo TEXT, fecha TEXT, hora TEXT, lugar TEXT,
            quorum_requerido INTEGER, asistentes INTEGER, acta_url TEXT, estado TEXT
        );
        CREATE TABLE votaciones (
            id INTEGER PRIMARY KEY AUTOINCREMENT, titulo TEXT, descripcion TEXT, tipo TEXT,
            fecha_inicio TEXT, fecha_fin TEXT, estado TEXT, padron INTEGER
        );
        CREATE TABLE opciones_votacion (
            id INTEGER PRIMARY KEY AUTOINCREMENT, votacion_id INTEGER, texto TEXT, votos INTEGER, orden INTEGER
        );
        CREATE TABLE articulos (
            id INTEGER PRIMARY KEY AUTOINCREMENT, titulo TEXT, resumen TEXT, cuerpo TEXT, categoria TEXT,
            imagen TEXT, autor TEXT, destacado INTEGER, estado TEXT, publicado_en TEXT
        );
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
        ['Debate de listas al CEPC', 'actividad', 6, '13:00', 'Auditorio Gorbea', null],
        ['Elecciones CEPC', 'eleccion', 10, '09:00', 'Hall Sur', null],
    ];
    $insertar($pdo, 'eventos', ['titulo', 'tipo', 'fecha', 'hora_inicio', 'lugar', 'ramo'], array_map(
        static fn(array $e): array => [$e[0], $e[1], $dia($e[2]), $e[3], $e[4], $e[5]],
        $eventos
    ));

    // [título, hace cuántos días, texto]
    $publicaciones = [
        ['Conoce a ∀∃!', 3, 'Quiénes somos, por qué nos presentamos y qué nos gustaría lograr en el CEPC.'],
        ['Calendario de solemnes del semestre', 6, 'Carrusel con todas las fechas de solemnes de Plan Común. Guárdalo para tenerlo a mano.'],
        ['Red de apoyo psicológico de la facultad', 20, 'Dónde pedir hora y qué esperar de la primera sesión.'],
        ['Elecciones CEPC: lo que tienes que saber', 9, 'Cuándo se vota, dónde y qué llevar. Link en la bio.'],
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

    // Proyectos del año: [nombre, descripción, presupuesto, estado]
    $anio = (int) date('Y');
    $proyectos = [
        ['Semana Novata', 'Bienvenida a la generación que entra: fiesta, feria de organizaciones y kit novato.', 1200000, 'finalizado'],
        ['Banco de Apuntes digital', 'Digitalización y orden del repositorio de apuntes y solemnes antiguas.', 180000, 'en_curso'],
        ['Campaña de salud mental', 'Ciclo de charlas y difusión de la red de apoyo psicológico de la facultad.', 350000, 'en_curso'],
        ['Torneo interno de fútbol', 'Campeonato entre secciones: arriendo de cancha, arbitraje e implementos.', 480000, 'en_curso'],
        ['Sede CEPC: equipamiento', 'Termoeléctrico, microondas y estantería para la sede.', 340000, 'finalizado'],
    ];
    $insertar($pdo, 'proyectos', ['nombre', 'descripcion', 'presupuesto', 'estado', 'anio'], array_map(
        static fn(array $p): array => [$p[0], $p[1], $p[2], $p[3], $anio],
        $proyectos
    ));

    // Movimientos: [tipo, monto, descripción, categoría, hace cuántos días, proyecto (1-5 o null)]
    $movimientos = [
        ['ingreso', 1800000, 'Aporte anual de la Escuela de Ingeniería', 'aporte_facultad', 170, null],
        ['ingreso', 640000, 'Recaudación fiesta de Semana Novata', 'fiesta', 96, 1],
        ['ingreso', 420000, 'Aporte FECh para actividades de bienestar', 'fecech', 88, 3],
        ['ingreso', 190000, 'Rifa solidaria de fin de semestre', 'rifa', 55, null],
        ['ingreso', 310000, 'Auspicio de preuniversitario para el torneo', 'auspicio', 22, 4],
        ['ingreso', 150000, 'Venta de empanadas en la jornada de septiembre', 'venta', 12, null],
        ['gasto', 520000, 'Arriendo de sonido e iluminación, Semana Novata', 'arriendo', 97, 1],
        ['gasto', 240000, 'Impresión de kits y credenciales novatos', 'impresiones', 110, 1],
        ['gasto', 310000, 'Termoeléctrico, microondas y estantería para la sede', 'mantencion', 42, 5],
        ['gasto', 145000, 'Fotocopias del banco de apuntes de MA1001 y FI1001', 'impresiones', 50, 2],
        ['gasto', 98000, 'Coffee break del conversatorio de salud mental', 'alimentacion', 26, 3],
        ['gasto', 175000, 'Arriendo de cancha, fecha 1 del torneo', 'deportes', 18, 4],
        ['gasto', 132000, 'Pelotas, petos y silbatos para el torneo', 'deportes', 6, 4],
        ['gasto', 128000, 'Lienzos y gráfica para la semana de disidencias', 'difusion', 35, null],
        ['gasto', 82000, 'Cajas de mercadería para apoyo de emergencia', 'bienestar', 11, null],
        ['gasto', 64000, 'Papelería y materiales para la sede', 'insumos', 15, null],
        ['gasto', 55000, 'Transporte de materiales a la jornada de septiembre', 'transporte', 9, null],
        ['gasto', 47000, 'Impresión de afiches de la semana de disidencias', 'difusion', 3, null],
    ];
    $insertar($pdo, 'movimientos', ['tipo', 'monto', 'descripcion', 'categoria', 'fecha', 'proyecto_id'], array_map(
        static fn(array $m): array => [$m[0], $m[1], $m[2], $m[3], max($anio . '-01-02', $dia(-$m[4])), $m[5]],
        $movimientos
    ));

    // Asambleas: [título, en cuántos días (negativo = pasada), hora, lugar, quórum, asistentes, estado]
    $asambleas = [
        ['Asamblea general de Plan Común', 3, '13:00', 'Auditorio Gorbea', 80, 0, 'convocada'],
        ['Asamblea extraordinaria por financiamiento', -32, '13:00', 'Auditorio Gorbea', 80, 96, 'realizada'],
        ['Asamblea general de inicio de semestre', -60, '13:00', 'Auditorio Gorbea', 80, 143, 'realizada'],
        ['Asamblea de evaluación de la Semana Novata', -75, '13:30', 'Sala B12', 60, 41, 'sin_quorum'],
    ];
    $insertar($pdo, 'asambleas', ['titulo', 'fecha', 'hora', 'lugar', 'quorum_requerido', 'asistentes', 'estado', 'acta_url'], array_map(
        static fn(array $a): array => [$a[0], $dia($a[1]), $a[2], $a[3], $a[4], $a[5], $a[6], null],
        $asambleas
    ));

    // Votaciones cerradas con sus resultados.
    $votaciones = [
        ['¿Adherimos al paro del día 20?', 'plebiscito', -18, -16, [
            ['Sí, adherimos con paro de un día', 214], ['Sí, pero sólo con jornada de reflexión', 168],
            ['No adherimos', 97], ['Abstención', 31],
        ]],
        ['Destino del excedente de la Semana Novata', 'plebiscito', -40, -38, [
            ['Fondo de emergencia para estudiantes', 241], ['Equipamiento de la sede', 186], ['Actividades deportivas', 88],
        ]],
    ];
    foreach ($votaciones as $v) {
        $insertar($pdo, 'votaciones', ['titulo', 'tipo', 'fecha_inicio', 'fecha_fin', 'estado', 'padron'], [
            [$v[0], $v[1], $dia($v[2]), $dia($v[3]), 'cerrada', 620],
        ]);
        $idVotacion = (int) $pdo->lastInsertId();
        $insertar($pdo, 'opciones_votacion', ['votacion_id', 'texto', 'votos', 'orden'], array_map(
            static fn(array $o, int $i): array => [$idVotacion, $o[0], $o[1], $i],
            $v[4],
            array_keys($v[4])
        ));
    }

    // Publicaciones: [título, resumen, cuerpo, categoría, hace cuántos días, destacada]
    $articulos = [
        [
            'Un calendario único de evaluaciones',
            'Todas las solemnes, controles y entregas de Plan Común en un solo lugar, con aviso cuando dos evaluaciones chocan.',
            "Cada semestre las fechas de evaluación llegan por muchos lados: U-Cursos, correos, historias de Instagram. Queremos juntarlas.\n\n## Qué proponemos\n- Un calendario público con todas las evaluaciones de Plan Común.\n- Aviso automático cuando dos solemnes caen el mismo día.\n- Llevar los topes a la Escuela antes de que ocurran, no después.\n\nEl calendario de esta página es un primer paso: ya puedes agregar cualquier fecha a tu teléfono.",
            'propuesta', 2, 1,
        ],
        [
            'Elecciones CEPC: fechas y cómo votar',
            'Dónde y cuándo se vota, y qué llevar. Las fechas oficiales también están en el calendario.',
            "Las elecciones del Centro de Estudiantes de Plan Común son abiertas a todo primer y segundo año.\n\n## Antes de votar\n- Revisa la fecha y el lugar en el calendario de esta página.\n- Lleva tu credencial universitaria.\n- Ven al debate de listas para conocer todas las propuestas.",
            'fechas', 4, 0,
        ],
        [
            'Cuentas claras desde el día uno',
            'Si somos CEPC, cada peso que entre y salga quedará publicado, con su boleta y su proyecto.',
            "La confianza se construye mostrando las cuentas, no pidiéndola.\n\n## Qué proponemos\n- Publicar cada ingreso y gasto en la página de transparencia.\n- Presupuesto por proyecto, con cuánto va gastado.\n- Actas de cada asamblea a la semana siguiente.",
            'propuesta', 7, 0,
        ],
        [
            'Un mapa del campus hecho por estudiantes',
            'Salas, escaleras, rutas desde la entrada y dónde comer cerca. Para que nadie llegue tarde a su primera clase.',
            "Llegar a Beauchef por primera vez es confuso: dos campus, subterráneos y nombres que nadie explica.\n\n## Qué proponemos\n- Completar el mapa con todas las salas y pisos.\n- Sumar los lugares para comer, con opciones veganas y que aceptan Junaeb.\n- Mantenerlo al día cada semestre.",
            'propuesta', 11, 0,
        ],
        [
            'Así funciona esta página',
            'Calendario, mapa, calculadora de notas y transparencia: qué hace cada sección y cómo usarla.',
            "Armamos esta página para que la información de Plan Común esté en un solo lugar.\n\n- El buscador de arriba encuentra ramos, salas y evaluaciones.\n- La calculadora te dice cuánto necesitas para aprobar.\n- El mapa te lleva de la entrada a tu sala.",
            'noticia', 15, 0,
        ],
    ];
    $insertar($pdo, 'articulos', ['titulo', 'resumen', 'cuerpo', 'categoria', 'destacado', 'estado', 'publicado_en', 'autor'], array_map(
        static fn(array $a): array => [$a[0], $a[1], $a[2], $a[3], $a[5], 'publicado', $dia(-$a[4]) . ' 10:00:00', null],
        $articulos
    ));

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
