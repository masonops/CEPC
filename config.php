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
    /* --- Identidad ------------------------------------------------------ */
    'nombre'    => 'Centro de Estudiantes de Plan Común',
    'sigla'     => 'CEPC',
    'facultad'  => 'Facultad de Ciencias Físicas y Matemáticas, Universidad de Chile',
    'direccion' => 'Beauchef 851, Santiago',

    /* --- Contacto ------------------------------------------------------- */
    // Si el panel tiene «instagram» o «correo_contacto» en su tabla
    // `configuracion`, se usan esos valores y estos quedan de respaldo.
    'instagram' => 'cepc.uchile',
    'correo'    => 'cepc@uchile.cl',

    /* --- Enlaces -------------------------------------------------------- */
    // Carpeta pública del banco de apuntes (Drive, U-Cursos, etc.).
    // Si se deja vacío, la tarjeta de apuntes no muestra el botón.
    'banco_apuntes_url' => '',

    /* --- Conexión con el panel ----------------------------------------- */
    // Carpeta donde vive el panel en el servidor. Vacío = se busca solo en
    // ./panel (como se sube a SparkedHost) y en ../cepc-panel (este repo).
    'panel_ruta' => '',
    // Dirección del panel para el enlace «Acceso directiva». Vacío = /panel/
    // si el panel está dentro del sitio, o se oculta el enlace.
    'panel_url'  => '',

    /* --- Portada -------------------------------------------------------- */
    // Cuántos días hacia adelante se miran para elegir el evento destacado.
    'destacado_dias_evento'  => 21,
    // Antigüedad máxima (en días) de una publicación de Instagram para ser
    // destacada cuando no hay un evento importante cerca.
    'destacado_dias_post'    => 10,
    // Cuántas fechas muestra la agenda de la portada.
    'agenda_cantidad'        => 8,
];
