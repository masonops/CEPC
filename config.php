<?php
/**
 * CEPC · Sitio público
 * config.php — Ajustes del sitio. Es el único archivo que hace falta tocar
 * al subirlo a un servidor nuevo.
 *
 * Los datos que cambian seguido (fechas, tutorías, publicaciones, directiva)
 * NO van aquí: el sitio los lee del panel de control, así que basta con
 * cargarlos allá para que aparezcan en la página.
 */

declare(strict_types=1);

return [
    /* --- Modo del sitio -------------------------------------------------- */
    // 'lista': sitio de campaña de la lista (antes y durante las elecciones).
    //          Habla como lista, no como CEPC, y muestra las propuestas.
    // 'cepc':  sitio del centro de estudiantes, si la lista gana.
    'modo' => 'lista',

    'lista' => [
        'nombre'      => '∀∃!',
        'lema'        => 'Para todos existe un único',
        'descripcion' => 'Lista al Centro de Estudiantes de Plan Común',
    ],

    /* --- Identidad del centro de estudiantes ---------------------------- */
    'nombre'    => 'Centro de Estudiantes de Plan Común',
    'sigla'     => 'CEPC',
    'facultad'  => 'Facultad de Ciencias Físicas y Matemáticas, Universidad de Chile',
    'direccion' => 'Beauchef 851, Santiago',

    /* --- Contacto ------------------------------------------------------- */
    // En modo 'lista' se usan SÓLO estos dos (los de la lista). En modo
    // 'cepc', si el panel tiene «instagram» o «correo_contacto» en su tabla
    // `configuracion`, mandan esos. Vacío = no se muestra.
    'instagram' => '',
    'correo'    => '',

    /* --- Enlaces -------------------------------------------------------- */
    // Carpeta pública del banco de apuntes (Drive, U-Cursos, etc.).
    // Si se deja vacío, la tarjeta de apuntes no muestra el botón.
    'banco_apuntes_url' => '',

    // Dónde ver horarios, secciones y profesores de un ramo. {codigo} se
    // reemplaza por el código (MA1001). Ajústalo al buscador real de U-Campus.
    'ucampus_ramo_url'  => 'https://ucampus.uchile.cl/m/fcfm_catalogo/',

    /* --- Conexión con el panel ----------------------------------------- */
    // Carpeta donde vive el panel en el servidor. Vacío = se busca solo en
    // ./panel (como se sube a SparkedHost) y en ../cepc-panel (este repo).
    'panel_ruta' => '',
    // Dirección del panel para el enlace «Acceso directiva». Vacío = /panel/
    // si el panel está dentro del sitio, o se oculta el enlace.
    'panel_url'  => '',

    /* --- Roomies -------------------------------------------------------- */
    // Dirección pública del sitio, con https y sin / al final
    // (ej. https://paratodos.cl). Se usa en los enlaces de los correos.
    // Vacío = se deduce de la visita.
    'url_sitio' => '',

    'roomies' => [
        // false = la página muestra «muy pronto» y no se puede publicar.
        'activo'         => true,
        // Sólo estos correos pueden publicar y escribir (verificación de estudiante).
        'dominios'       => ['ug.uchile.cl', 'uchile.cl'],
        // true = los avisos esperan aprobación del equipo antes de verse.
        'moderar'        => false,
        // Remitente de los correos. Debe ser una casilla del mismo dominio
        // del sitio (créala en el hosting) o los correos llegan a spam.
        'remitente'      => '',
        // Dónde llegan los reportes de avisos. Vacío = el 'correo' de arriba.
        'correo_reportes' => '',
        // Días que un aviso queda publicado antes de pedir renovarlo.
        'dias_vigencia'  => 60,
        'max_fotos'      => 8,
        // Avisos activos a la vez por correo.
        'max_por_correo' => 2,
    ],

    /* --- Portada -------------------------------------------------------- */
    // Cuántos días hacia adelante se miran para elegir el evento destacado.
    'destacado_dias_evento'  => 21,
    // Antigüedad máxima (en días) de una publicación de Instagram para ser
    // destacada cuando no hay un evento importante cerca.
    'destacado_dias_post'    => 10,
    // Cuántas fechas muestra la agenda de la portada.
    'agenda_cantidad'        => 8,
];
