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
        CREATE TABLE roomies (
            id INTEGER PRIMARY KEY AUTOINCREMENT, estado TEXT, tipo TEXT, titulo TEXT, descripcion TEXT,
            comuna TEXT, calle TEXT, lat REAL, lng REAL, precio INTEGER, gastos_comunes INTEGER,
            garantia_meses REAL, incluye TEXT, disponible_desde TEXT, estadia_minima INTEGER,
            dormitorios INTEGER, banos INTEGER, m2 INTEGER, piso INTEGER, piezas_libres INTEGER,
            roomies_actuales INTEGER, comodidades TEXT, mascotas TEXT, fumar TEXT, visitas TEXT,
            preferencia TEXT, autor_nombre TEXT, autor_correo TEXT, autor_carrera TEXT,
            autor_ingreso INTEGER, autor_edad INTEGER, autor_sobre TEXT, autor_habitos TEXT,
            whatsapp TEXT, mostrar_whatsapp INTEGER, instagram TEXT, token_verificacion TEXT,
            token_gestion TEXT, verificado_en TEXT, publicado_en TEXT, expira_en TEXT,
            creado_en TEXT, actualizado_en TEXT
        );
        CREATE TABLE roomies_fotos (
            id INTEGER PRIMARY KEY AUTOINCREMENT, roomie_id INTEGER, archivo TEXT, ancho INTEGER,
            alto INTEGER, orden INTEGER, credito TEXT
        );
        CREATE TABLE roomies_registro (
            id INTEGER PRIMARY KEY AUTOINCREMENT, accion TEXT, roomie_id INTEGER, huella TEXT, creado_en TEXT
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
        ['Banco de Apuntes digital', 'Digitalización y orden del repositorio de apuntes y tareas.', 180000, 'en_curso'],
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
            'Roomies: con quién vivir cerca de Beauchef',
            'Una sección para que estudiantes foráneos encuentren pieza o roomies, con avisos verificados por estudiantes.',
            "Llegar a Santiago a estudiar es caro y solitario. Buscar pieza en grupos de Facebook o en portales de arriendo es una lotería: no sabes quién publica ni qué tan lejos queda de verdad.\n\n## Qué proponemos\n- Roomies: avisos de piezas y deptos publicados sólo por estudiantes, verificados con su correo de la Universidad.\n- Para cada aviso, cuánto se demora a pie a Beauchef y cuál es el metro más cercano.\n- Consejos para arrendar tranquilo y una forma de reportar avisos raros.\n\nYa lo puedes probar en la sección Roomies de esta página.",
            'propuesta', 3, 0,
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

    // Roomies: avisos de ejemplo. Fotos de Unsplash (licencia Unsplash) en
    // assets/img/ejemplo/roomies/; las personas son inventadas.
    $roomies = [
        [
            'tipo' => 'pieza', 'titulo' => 'Pieza amoblada a 10 minutos caminando de Beauchef',
            'descripcion' => "Se libera una pieza en nuestro depto de tres dormitorios, a pasos del metro Toesca. Somos dos estudiantes de la Chile (Ingeniería y Derecho) y buscamos a alguien tranquilo para el próximo semestre.\n\nLa pieza tiene cama de plaza y media, clóset, escritorio y una ventana grande que da a la calle. El depto tiene living con mesa grande para estudiar y la cocina está completa.\n\nEl edificio tiene conserjería 24 h, ascensor y bicicletero.",
            'comuna' => 'Santiago', 'calle' => 'San Ignacio', 'lat' => -33.45535, 'lng' => -70.65762,
            'precio' => 230000, 'gastos_comunes' => 25000, 'garantia_meses' => 1, 'incluye' => 'agua,internet',
            'disponible' => 0, 'estadia_minima' => 6, 'dormitorios' => 3, 'banos' => 2, 'm2' => 75, 'piso' => 8,
            'piezas_libres' => 1, 'roomies_actuales' => 2,
            'comodidades' => 'amoblada,closet,escritorio,ventana,cocina,living,lavadora,ascensor,conserjeria,bicicletero',
            'mascotas' => 'no', 'fumar' => 'no', 'visitas' => 'aviso', 'preferencia' => 'todos',
            'autor_nombre' => 'Catalina', 'autor_carrera' => 'Ingeniería · Plan Común', 'autor_ingreso' => 2025, 'autor_edad' => 19,
            'autor_sobre' => 'Soy de Talca. Estudio harto en el living y los fines de semana cocino para todos. Busco a alguien que respete los horarios de estudio.',
            'autor_habitos' => 'ordenado,estudia,cocina', 'publicado' => 2, 'grupo' => 1, 'fotos' => 5,
        ],
        [
            'tipo' => 'departamento', 'titulo' => 'Depto de 3 dormitorios en Barrio República: busco 2 roomies',
            'descripcion' => "Llego en marzo desde Antofagasta y quiero arrendar este depto con dos personas más. Ya hablé con la dueña: nos lo arrienda a los tres con un solo contrato.\n\nEstá a dos cuadras del metro República y a 15 minutos caminando de Beauchef. Tiene balcón, calefacción y estacionamiento (que podemos subarrendar).\n\nEl arriendo total es de \$780.000; dividido en tres, queda en lo que ves arriba.",
            'comuna' => 'Santiago', 'calle' => 'Av. España', 'lat' => -33.44918, 'lng' => -70.66672,
            'precio' => 260000, 'gastos_comunes' => 18000, 'garantia_meses' => 1, 'incluye' => '',
            'disponible' => 138, 'estadia_minima' => 12, 'dormitorios' => 3, 'banos' => 2, 'm2' => 82, 'piso' => 4,
            'piezas_libres' => 2, 'roomies_actuales' => 1,
            'comodidades' => 'closet,ventana,cocina,living,lavadora,balcon,calefaccion,ascensor,estacionamiento',
            'mascotas' => 'si', 'fumar' => 'no', 'visitas' => 'si', 'preferencia' => 'todos',
            'autor_nombre' => 'Tomás', 'autor_carrera' => 'Ingeniería · Plan Común', 'autor_ingreso' => 2026, 'autor_edad' => 18,
            'autor_sobre' => 'Me gusta salir a correr temprano y jugar fútbol los fines de semana. Soy ordenado con lo común y prefiero dividir los gastos con una app.',
            'autor_habitos' => 'madrugador,deporte,sociable', 'publicado' => 1, 'grupo' => 2, 'fotos' => 5,
        ],
        [
            'tipo' => 'pieza', 'titulo' => 'Pieza en casa antigua del Barrio Yungay, todo incluido',
            'descripcion' => "Vivimos tres estudiantes en una casa grande de techos altos, con patio y un gato muy tranquilo (Pascal). Se libera la pieza del segundo piso.\n\nEl precio incluye agua, luz, gas, internet de fibra y los gastos de la casa. La cocina se comparte y cada uno tiene su repisa.\n\nPara llegar a Beauchef: unos 30 minutos caminando o 10 en bici por Cumming.",
            'comuna' => 'Santiago', 'calle' => 'Libertad', 'lat' => -33.44152, 'lng' => -70.67538,
            'precio' => 190000, 'gastos_comunes' => 0, 'garantia_meses' => 0.5, 'incluye' => 'agua,luz,gas,internet,gastos',
            'disponible' => 0, 'estadia_minima' => 4, 'dormitorios' => 5, 'banos' => 2, 'm2' => 140, 'piso' => 2,
            'piezas_libres' => 1, 'roomies_actuales' => 3,
            'comodidades' => 'amoblada,closet,ventana,cocina,living,lavadora,calefaccion,bicicletero',
            'mascotas' => 'si', 'fumar' => 'terraza', 'visitas' => 'si', 'preferencia' => 'todos',
            'autor_nombre' => 'Josefa', 'autor_carrera' => 'Geología', 'autor_ingreso' => 2023, 'autor_edad' => 22,
            'autor_sobre' => 'Toco guitarra (con audífonos de noche, prometido). Nos turnamos para cocinar los domingos y hacemos aseo los sábados.',
            'autor_habitos' => 'tranquilo,cocina,musica', 'publicado' => 5, 'grupo' => 3, 'fotos' => 4,
        ],
        [
            'tipo' => 'pieza', 'titulo' => 'Pieza con baño privado junto al metro Parque Almagro',
            'descripcion' => "Pieza en suite en depto de dos dormitorios, piso 12 con vista a la cordillera. Comparto el depto con una sola persona: yo.\n\nLa pieza tiene baño propio, clóset grande y escritorio. El edificio tiene gimnasio, conserjería 24 h y lavandería.\n\nBusco a una estudiante tranquila, idealmente de la facultad.",
            'comuna' => 'Santiago', 'calle' => 'San Diego', 'lat' => -33.45362, 'lng' => -70.65215,
            'precio' => 280000, 'gastos_comunes' => 35000, 'garantia_meses' => 1, 'incluye' => 'internet',
            'disponible' => 20, 'estadia_minima' => 6, 'dormitorios' => 2, 'banos' => 2, 'm2' => 58, 'piso' => 12,
            'piezas_libres' => 1, 'roomies_actuales' => 1,
            'comodidades' => 'bano_privado,closet,escritorio,ventana,cocina,living,lavadora,ascensor,conserjeria,gimnasio',
            'mascotas' => 'no', 'fumar' => 'no', 'visitas' => 'aviso', 'preferencia' => 'mujeres',
            'autor_nombre' => 'Antonia', 'autor_carrera' => 'Ingeniería Civil Industrial', 'autor_ingreso' => 2024, 'autor_edad' => 20,
            'autor_sobre' => 'Soy de Concepción. Paso el día en Beauchef y llego a estudiar o descansar, así que el depto es tranquilo.',
            'autor_habitos' => 'ordenado,estudia,tranquilo', 'publicado' => 9, 'grupo' => 4, 'fotos' => 4,
        ],
        [
            'tipo' => 'pieza', 'titulo' => 'Pieza amoblada frente al metro Unión Latinoamericana',
            'descripcion' => "Depto de tres piezas en piso 15, frente al metro. Somos dos estudiantes de Plan Común y buscamos un tercero.\n\nLa pieza viene con cama, escritorio y silla. El precio incluye agua y gas; la luz y el internet se dividen (unos \$15.000 por persona).\n\nBeauchef queda a 20 minutos caminando o a una estación de metro hasta Los Héroes.",
            'comuna' => 'Estación Central', 'calle' => 'Av. Libertador Bernardo O\'Higgins', 'lat' => -33.45081, 'lng' => -70.67352,
            'precio' => 210000, 'gastos_comunes' => 20000, 'garantia_meses' => 1, 'incluye' => 'agua,gas',
            'disponible' => 0, 'estadia_minima' => 5, 'dormitorios' => 3, 'banos' => 1, 'm2' => 64, 'piso' => 15,
            'piezas_libres' => 1, 'roomies_actuales' => 2,
            'comodidades' => 'amoblada,closet,escritorio,cocina,living,lavadora,ascensor,conserjeria',
            'mascotas' => 'consultar', 'fumar' => 'no', 'visitas' => 'aviso', 'preferencia' => 'hombres',
            'autor_nombre' => 'Matías', 'autor_carrera' => 'Ingeniería · Plan Común', 'autor_ingreso' => 2025, 'autor_edad' => 19,
            'autor_sobre' => 'Soy de La Serena. Estudio de noche y juego en línea con audífonos. Buena onda y ordenado con la cocina.',
            'autor_habitos' => 'nocturno,gamer,sociable', 'publicado' => 12, 'grupo' => 5, 'fotos' => 4,
        ],
        [
            'tipo' => 'departamento', 'titulo' => 'Depto de 2 dormitorios por Beauchef, a pasos del Parque O\'Higgins',
            'descripcion' => "Me quedo en el depto y busco roomie para la otra pieza. Está en Av. Beauchef, a 8 minutos caminando de la facultad y frente al Parque O'Higgins (ideal para salir a correr).\n\nEl depto tiene balcón, lavadora y bicicletero en el edificio. La pieza que se libera no viene amoblada, pero puedo prestar un escritorio.",
            'comuna' => 'Santiago', 'calle' => 'Av. Beauchef', 'lat' => -33.46195, 'lng' => -70.66105,
            'precio' => 240000, 'gastos_comunes' => 22000, 'garantia_meses' => 1, 'incluye' => 'internet',
            'disponible' => 24, 'estadia_minima' => 6, 'dormitorios' => 2, 'banos' => 2, 'm2' => 55, 'piso' => 6,
            'piezas_libres' => 1, 'roomies_actuales' => 1,
            'comodidades' => 'closet,ventana,cocina,living,lavadora,balcon,ascensor,conserjeria,bicicletero',
            'mascotas' => 'si', 'fumar' => 'no', 'visitas' => 'si', 'preferencia' => 'todos',
            'autor_nombre' => 'Diego', 'autor_carrera' => 'Ingeniería Eléctrica', 'autor_ingreso' => 2024, 'autor_edad' => 20,
            'autor_sobre' => 'Soy de Puerto Montt. Me levanto temprano, entreno en el parque y los fines de semana a veces viajo al sur.',
            'autor_habitos' => 'madrugador,deporte,ordenado', 'publicado' => 15, 'grupo' => 6, 'fotos' => 4,
        ],
    ];
    $columnasRoomie = ['estado', 'tipo', 'titulo', 'descripcion', 'comuna', 'calle', 'lat', 'lng', 'precio', 'gastos_comunes',
        'garantia_meses', 'incluye', 'disponible_desde', 'estadia_minima', 'dormitorios', 'banos', 'm2', 'piso', 'piezas_libres',
        'roomies_actuales', 'comodidades', 'mascotas', 'fumar', 'visitas', 'preferencia', 'autor_nombre', 'autor_correo',
        'autor_carrera', 'autor_ingreso', 'autor_edad', 'autor_sobre', 'autor_habitos', 'mostrar_whatsapp', 'token_gestion',
        'verificado_en', 'publicado_en', 'expira_en', 'creado_en'];
    foreach ($roomies as $n => $r) {
        $publicado = $dia(-$r['publicado']) . ' 12:00:00';
        $insertar($pdo, 'roomies', $columnasRoomie, [[
            'publicado', $r['tipo'], $r['titulo'], $r['descripcion'], $r['comuna'], $r['calle'], $r['lat'], $r['lng'],
            $r['precio'], $r['gastos_comunes'], $r['garantia_meses'], $r['incluye'], $r['disponible'] ? $dia($r['disponible']) : null,
            $r['estadia_minima'], $r['dormitorios'], $r['banos'], $r['m2'], $r['piso'], $r['piezas_libres'], $r['roomies_actuales'],
            $r['comodidades'], $r['mascotas'], $r['fumar'], $r['visitas'], $r['preferencia'], $r['autor_nombre'],
            'ejemplo' . ($n + 1) . '@ug.uchile.cl', $r['autor_carrera'], $r['autor_ingreso'], $r['autor_edad'], $r['autor_sobre'],
            $r['autor_habitos'], 0, sha1('ejemplo-' . $n), $publicado, $publicado, $dia(60 - $r['publicado']) . ' 12:00:00', $publicado,
        ]]);
        $insertar($pdo, 'roomies_fotos', ['roomie_id', 'archivo', 'orden', 'credito'], array_map(
            static fn(int $k): array => [$n + 1, 'assets/img/ejemplo/roomies/' . $r['grupo'] . '-' . $k, $k, 'Foto de ejemplo: Unsplash'],
            range(1, $r['fotos'])
        ));
    }

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
