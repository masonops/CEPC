<?php
/**
 * CEPC · Sitio público
 * includes/roomies/acciones.php — Lo que Roomies escribe: publicar un aviso
 * (con fotos), confirmar el correo, administrarlo con el enlace privado y
 * escribirle a quien publica.
 *
 * Sólo lo cargan las páginas que reciben formularios (roomie.php,
 * roomies-publicar.php, roomies-verificar.php, roomies-gestionar.php).
 *
 * Seguridad:
 *   · Sólo correos de los dominios de config.php → roomies.dominios.
 *   · El aviso no se ve hasta abrir el enlace que llega a ese correo.
 *   · Formularios con token CSRF, campo trampa para bots y límites por
 *     persona (tabla roomies_registro, con la IP resumida en un hash).
 *   · Las fotos se vuelven a dibujar con GD: lo que se guarda es siempre un
 *     JPEG limpio, sin metadatos ni ubicación GPS.
 */

declare(strict_types=1);

/* --------------------------------------------------------------------- */
/*  Utilidades                                                            */
/* --------------------------------------------------------------------- */

function roomies_ajuste(string $clave, mixed $defecto = null): mixed
{
    return sitio('roomies', [])[$clave] ?? $defecto;
}

function roomies_sesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_name('roomies');
        session_start();
    }
}

function roomies_csrf(): string
{
    roomies_sesion();
    return $_SESSION['roomies_csrf'] ??= bin2hex(random_bytes(16));
}

function roomies_csrf_valido(?string $token): bool
{
    roomies_sesion();
    return is_string($token) && isset($_SESSION['roomies_csrf']) && hash_equals($_SESSION['roomies_csrf'], $token);
}

/** Campo oculto con el token y la trampa para bots. */
function roomies_campos_seguridad(): string
{
    return '<input type="hidden" name="csrf" value="' . esc(roomies_csrf()) . '">'
        . '<div class="trampa" aria-hidden="true"><label>No llenar <input type="text" name="sitio_web" tabindex="-1" autocomplete="off"></label></div>';
}

/** ¿Llenó el campo trampa? Entonces es un bot. */
function roomies_es_bot(array $post): bool
{
    return trim((string) ($post['sitio_web'] ?? '')) !== '';
}

/** Huella de quien visita: hash de la IP (no se guarda la IP). */
function roomies_huella(): string
{
    return sha1('roomies|' . ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . (sitio('roomies_sal', '') ?: SITIO_RAIZ));
}

/** ¿Puede hacer otra vez esta acción? (máximo $max en los últimos $minutos). */
function roomies_dentro_del_limite(string $accion, int $max, int $minutos, ?int $roomieId = null): bool
{
    $sql = 'SELECT COUNT(*) AS n FROM roomies_registro WHERE accion = ? AND huella = ? AND creado_en >= ?';
    $parametros = [$accion, roomies_huella(), date('Y-m-d H:i:s', time() - $minutos * 60)];
    if ($roomieId !== null) {
        $sql .= ' AND roomie_id = ?';
        $parametros[] = $roomieId;
    }
    return (int) (datos_filas($sql, $parametros)[0]['n'] ?? 0) < $max;
}

function roomies_registrar(string $accion, ?int $roomieId = null): void
{
    roomies_ejecutar(
        'INSERT INTO roomies_registro (accion, roomie_id, huella, creado_en) VALUES (?, ?, ?, ?)',
        [$accion, $roomieId, roomies_huella(), date('Y-m-d H:i:s')]
    );
}

/** INSERT/UPDATE/DELETE. Devuelve el id insertado (o 1) si resultó, 0 si no. */
function roomies_ejecutar(string $sql, array $parametros = []): int
{
    $pdo = datos_pdo();
    if ($pdo === null) {
        return 0;
    }
    try {
        $consulta = $pdo->prepare($sql);
        $consulta->execute($parametros);
        return str_starts_with(ltrim($sql), 'INSERT') ? (int) $pdo->lastInsertId() : 1;
    } catch (Throwable $e) {
        error_log('[sitio CEPC · roomies] ' . $e->getMessage());
        return 0;
    }
}

/** ¿Están creadas las tablas? */
function roomies_instalado(): bool
{
    $pdo = datos_pdo();
    if ($pdo === null) {
        return false;
    }
    try {
        $pdo->query('SELECT 1 FROM roomies LIMIT 1');
        return true;
    } catch (Throwable) {
        return false;
    }
}

/** ¿El correo es de un dominio permitido? */
function roomies_correo_valido(string $correo): bool
{
    if (filter_var($correo, FILTER_VALIDATE_EMAIL) === false) {
        return false;
    }
    $dominio = strtolower(substr(strrchr($correo, '@') ?: '', 1));
    return in_array($dominio, array_map('strtolower', (array) roomies_ajuste('dominios', [])), true);
}

/** Dirección completa de una página del sitio, para los correos. */
function roomies_url_absoluta(string $ruta): string
{
    $base = rtrim((string) sitio('url_sitio', ''), '/');
    if ($base === '') {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $host  = preg_replace('/[^a-z0-9.:-]/i', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?: 'localhost';
        $dir   = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/'))), '/');
        $base  = ($https ? 'https://' : 'http://') . $host . $dir;
    }
    return $base . '/' . ltrim($ruta, '/');
}

/** Quita saltos de línea (evita que alguien meta cabeceras en el correo). */
function roomies_linea(string $texto): string
{
    return trim(preg_replace('/[\r\n]+/', ' ', $texto) ?? '');
}

/**
 * Envía un correo de texto. Si la variable de entorno CEPC_CORREO_ARCHIVO
 * existe, lo escribe en ese archivo en vez de enviarlo (para probar en tu
 * computador, donde mail() no funciona).
 */
function roomies_enviar_correo(string $para, string $asunto, string $texto, ?string $responderA = null, ?string $nombreRespuesta = null): bool
{
    $remitente = (string) roomies_ajuste('remitente', '');
    if ($remitente === '') {
        $remitente = 'no-responder@' . preg_replace('/^www\./', '', preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost')) ?? 'localhost');
    }
    $cabeceras = [
        'From: ' . mb_encode_mimeheader(marca() . ' · Roomies', 'UTF-8') . ' <' . roomies_linea($remitente) . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
    ];
    if ($responderA !== null && filter_var($responderA, FILTER_VALIDATE_EMAIL)) {
        $cabeceras[] = 'Reply-To: ' . ($nombreRespuesta ? mb_encode_mimeheader(roomies_linea($nombreRespuesta), 'UTF-8') . ' ' : '') . '<' . roomies_linea($responderA) . '>';
    }
    $asunto = mb_encode_mimeheader(roomies_linea($asunto), 'UTF-8');

    $archivo = getenv('CEPC_CORREO_ARCHIVO');
    if (is_string($archivo) && $archivo !== '') {
        return (bool) file_put_contents($archivo, "Para: $para\n" . implode("\n", $cabeceras) . "\nAsunto: $asunto\n\n$texto\n\n-----\n", FILE_APPEND);
    }
    return mail(roomies_linea($para), $asunto, $texto, implode("\r\n", $cabeceras), '-f' . roomies_linea($remitente));
}

/* --------------------------------------------------------------------- */
/*  Publicar                                                              */
/* --------------------------------------------------------------------- */

/** Lee y valida el formulario. Devuelve [datos, errores (campo → texto)]. */
function roomies_validar(array $p): array
{
    $texto = static fn(string $c, int $max): string => mb_substr(trim(preg_replace('/[ \t]+/', ' ', (string) ($p[$c] ?? '')) ?? ''), 0, $max);
    $entero = static fn(string $c, int $min, int $max, ?int $defecto = null): ?int =>
        (isset($p[$c]) && $p[$c] !== '' && is_numeric($p[$c])) ? max($min, min($max, (int) $p[$c])) : $defecto;
    $opcion = static fn(string $c, array $validas, string $defecto): string =>
        isset($validas[(string) ($p[$c] ?? '')]) ? (string) $p[$c] : $defecto;
    $varias = static fn(string $c, array $validas): string =>
        implode(',', array_values(array_filter((array) ($p[$c] ?? []), static fn($v): bool => is_string($v) && isset($validas[$v]))));

    $d = [
        'tipo'             => $opcion('tipo', ROOMIES_TIPOS, 'pieza'),
        'titulo'           => $texto('titulo', 90),
        'descripcion'      => mb_substr(trim(str_replace("\r", '', (string) ($p['descripcion'] ?? ''))), 0, 3000),
        'comuna'           => in_array($p['comuna'] ?? '', ROOMIES_COMUNAS, true) ? $p['comuna'] : '',
        'calle'            => $texto('calle', 80),
        'lat'              => is_numeric($p['lat'] ?? null) ? round((float) $p['lat'], 6) : null,
        'lng'              => is_numeric($p['lng'] ?? null) ? round((float) $p['lng'], 6) : null,
        'precio'           => $entero('precio', 0, 3000000, 0),
        'gastos_comunes'   => $entero('gastos_comunes', 0, 1000000, 0),
        'garantia_meses'   => in_array($p['garantia_meses'] ?? '', ['0', '0.5', '1', '2'], true) ? (float) $p['garantia_meses'] : 1.0,
        'incluye'          => $varias('incluye', ROOMIES_INCLUYE),
        'disponible_desde' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($p['disponible_desde'] ?? '')) ? $p['disponible_desde'] : null,
        'estadia_minima'   => $entero('estadia_minima', 0, 24, 0),
        'dormitorios'      => $entero('dormitorios', 1, 10, 1),
        'banos'            => $entero('banos', 1, 6, 1),
        'm2'               => $entero('m2', 8, 1000),
        'piso'             => $entero('piso', -3, 60),
        'piezas_libres'    => $entero('piezas_libres', 1, 8, 1),
        'roomies_actuales' => $entero('roomies_actuales', 0, 10, 0),
        'comodidades'      => $varias('comodidades', ROOMIES_COMODIDADES),
        'mascotas'         => $opcion('mascotas', ROOMIES_REGLAS['mascotas']['valores'], 'consultar'),
        'fumar'            => $opcion('fumar', ROOMIES_REGLAS['fumar']['valores'], 'no'),
        'visitas'          => $opcion('visitas', ROOMIES_REGLAS['visitas']['valores'], 'aviso'),
        'preferencia'      => $opcion('preferencia', ROOMIES_REGLAS['preferencia']['valores'], 'todos'),
        'autor_nombre'     => $texto('autor_nombre', 40),
        'autor_correo'     => strtolower($texto('autor_correo', 120)),
        'autor_carrera'    => $texto('autor_carrera', 60),
        'autor_ingreso'    => $entero('autor_ingreso', 1990, (int) date('Y') + 1),
        'autor_edad'       => $entero('autor_edad', 16, 99),
        'autor_sobre'      => mb_substr(trim(str_replace("\r", '', (string) ($p['autor_sobre'] ?? ''))), 0, 600),
        'autor_habitos'    => $varias('autor_habitos', ROOMIES_HABITOS),
        'whatsapp'         => preg_replace('/\D/', '', (string) ($p['whatsapp'] ?? '')) ?: null,
        'mostrar_whatsapp' => !empty($p['mostrar_whatsapp']) ? 1 : 0,
        'instagram'        => ltrim($texto('instagram', 40), '@') ?: null,
    ];
    if ($d['instagram'] !== null && !preg_match('/^[A-Za-z0-9._]{1,30}$/', $d['instagram'])) {
        $d['instagram'] = null;
    }
    // WhatsApp chileno: 9 dígitos (9XXXXXXXX) → 569XXXXXXXX.
    if ($d['whatsapp'] !== null) {
        $d['whatsapp'] = preg_match('/^9\d{8}$/', $d['whatsapp']) ? '56' . $d['whatsapp'] : $d['whatsapp'];
        if (!preg_match('/^569\d{8}$/', $d['whatsapp'])) {
            $d['whatsapp'] = null;
        }
    }
    if ($d['whatsapp'] === null) {
        $d['mostrar_whatsapp'] = 0;
    }

    $e = [];
    if (mb_strlen($d['autor_nombre']) < 2) {
        $e['autor_nombre'] = 'Escribe tu nombre.';
    }
    if (!roomies_correo_valido($d['autor_correo'])) {
        $e['autor_correo'] = 'Usa tu correo de la Universidad (' . implode(' o ', array_map(static fn($x) => '@' . $x, (array) roomies_ajuste('dominios', []))) . ').';
    }
    if (mb_strlen($d['titulo']) < 10) {
        $e['titulo'] = 'Escribe un título de al menos 10 letras.';
    }
    if (mb_strlen($d['descripcion']) < 40) {
        $e['descripcion'] = 'Cuenta un poco más del lugar (al menos 40 letras).';
    }
    if ($d['comuna'] === '') {
        $e['comuna'] = 'Elige la comuna.';
    }
    // El punto tiene que estar en el Gran Santiago.
    if ($d['lat'] === null || $d['lng'] === null || $d['lat'] < -33.75 || $d['lat'] > -33.25 || $d['lng'] < -70.95 || $d['lng'] > -70.45) {
        $e['ubicacion'] = 'Marca en el mapa dónde queda (aproximado está bien).';
    }
    if ($d['precio'] < 50000) {
        $e['precio'] = 'Indica el arriendo mensual por persona.';
    }
    if (empty($p['acepto'])) {
        $e['acepto'] = 'Tienes que aceptar las condiciones.';
    }
    return [$d, $e];
}

/**
 * Revisa las fotos subidas (input name="fotos[]"). Devuelve
 * [lista de rutas temporales válidas, error o null].
 */
function roomies_revisar_fotos(array $archivos): array
{
    $max = (int) roomies_ajuste('max_fotos', 8);
    $validas = [];
    $nombres = (array) ($archivos['tmp_name'] ?? []);
    $errores = (array) ($archivos['error'] ?? []);
    foreach ($nombres as $i => $tmp) {
        if (($errores[$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if (($errores[$i] ?? 1) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $tmp)) {
            return [[], 'Una de las fotos no se pudo subir. Prueba con fotos más livianas.'];
        }
        $info = @getimagesize((string) $tmp);
        if ($info === false || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return [[], 'Las fotos tienen que ser JPG, PNG o WebP.'];
        }
        if ($info[0] < 400 || $info[1] < 300) {
            return [[], 'Alguna foto es muy chica (mínimo 400 × 300 píxeles).'];
        }
        $validas[] = (string) $tmp;
    }
    if (count($validas) < 2) {
        return [[], 'Sube al menos 2 fotos: la pieza y algún espacio común.'];
    }
    return [array_slice($validas, 0, $max), null];
}

/** Abre una imagen con GD, enderezada según su EXIF. */
function roomies_abrir_imagen(string $ruta): ?GdImage
{
    $tipo = @getimagesize($ruta)[2] ?? null;
    $img = match ($tipo) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($ruta),
        IMAGETYPE_PNG  => @imagecreatefrompng($ruta),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($ruta) : false,
        default        => false,
    };
    if (!$img instanceof GdImage) {
        return null;
    }
    if ($tipo === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $orientacion = (int) (@exif_read_data($ruta)['Orientation'] ?? 1);
        $girada = match ($orientacion) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => $img,
        };
        if ($girada instanceof GdImage) {
            $img = $girada;
        }
    }
    return $img;
}

/** Guarda la versión grande (máx. 1600 px) y la miniatura 3:2 (640 × 427). */
function roomies_guardar_foto(string $tmp, string $base): ?array
{
    $img = roomies_abrir_imagen($tmp);
    if ($img === null) {
        return null;
    }
    $w = imagesx($img);
    $h = imagesy($img);

    $escala = min(1, 1600 / max($w, $h));
    $gw = (int) round($w * $escala);
    $gh = (int) round($h * $escala);
    $grande = imagecreatetruecolor($gw, $gh);
    imagefill($grande, 0, 0, imagecolorallocate($grande, 255, 255, 255));
    imagecopyresampled($grande, $img, 0, 0, 0, 0, $gw, $gh, $w, $h);
    imageinterlace($grande, true);

    // Miniatura recortada al centro en 3:2.
    $mw = 640;
    $mh = 427;
    $proporcion = $mw / $mh;
    [$cw, $ch] = $w / $h > $proporcion ? [(int) round($h * $proporcion), $h] : [$w, (int) round($w / $proporcion)];
    $mini = imagecreatetruecolor($mw, $mh);
    imagefill($mini, 0, 0, imagecolorallocate($mini, 255, 255, 255));
    imagecopyresampled($mini, $img, 0, 0, (int) (($w - $cw) / 2), (int) (($h - $ch) / 2), $mw, $mh, $cw, $ch);
    imageinterlace($mini, true);

    $ok = imagejpeg($grande, SITIO_RAIZ . '/' . $base . '-g.jpg', 80) && imagejpeg($mini, SITIO_RAIZ . '/' . $base . '-m.jpg', 76);
    return $ok ? ['archivo' => $base, 'ancho' => $gw, 'alto' => $gh] : null;
}

/** Borra las fotos de un aviso (archivos y filas). */
function roomies_borrar_fotos(int $id): void
{
    $carpeta = SITIO_RAIZ . '/uploads/roomies/' . $id;
    foreach (glob($carpeta . '/*.jpg') ?: [] as $archivo) {
        @unlink($archivo);
    }
    @rmdir($carpeta);
    roomies_ejecutar('DELETE FROM roomies_fotos WHERE roomie_id = ?', [$id]);
}

/**
 * Publica un aviso: valida, guarda, procesa las fotos y manda el correo de
 * verificación. Devuelve ['ok' => bool, 'errores' => [...], 'datos' => [...]].
 */
function roomies_publicar(array $post, array $archivos): array
{
    [$d, $errores] = roomies_validar($post);
    $resultado = static fn(bool $ok, array $errores = []) => ['ok' => $ok, 'errores' => $errores, 'datos' => $d];

    if (!roomies_csrf_valido($post['csrf'] ?? null)) {
        return $resultado(false, ['general' => 'La página estuvo abierta mucho rato. Vuelve a enviar el formulario.']);
    }
    if (roomies_es_bot($post)) {
        return $resultado(true);
    }
    [$fotos, $errorFotos] = roomies_revisar_fotos($archivos['fotos'] ?? []);
    if ($errorFotos !== null) {
        $errores['fotos'] = $errorFotos;
    }
    if ($errores !== []) {
        return $resultado(false, $errores);
    }
    if (!roomies_dentro_del_limite('publicar', 4, 24 * 60)) {
        return $resultado(false, ['general' => 'Ya publicaste varios avisos hoy. Intenta mañana.']);
    }
    $activos = (int) (datos_filas(
        "SELECT COUNT(*) AS n FROM roomies WHERE autor_correo = ? AND estado IN ('pendiente', 'revision', 'publicado', 'pausado')",
        [$d['autor_correo']]
    )[0]['n'] ?? 0);
    if ($activos >= (int) roomies_ajuste('max_por_correo', 2)) {
        return $resultado(false, ['autor_correo' => 'Ya tienes avisos activos con este correo. Administra uno desde el enlace que te llegó.']);
    }

    $ahora = date('Y-m-d H:i:s');
    $tokenVerificacion = bin2hex(random_bytes(20));
    $tokenGestion = bin2hex(random_bytes(20));
    $columnas = array_merge(array_keys($d), ['estado', 'token_verificacion', 'token_gestion', 'creado_en']);
    $valores = array_merge(array_values($d), ['pendiente', $tokenVerificacion, $tokenGestion, $ahora]);
    $id = roomies_ejecutar(
        'INSERT INTO roomies (' . implode(', ', $columnas) . ') VALUES (' . implode(', ', array_fill(0, count($columnas), '?')) . ')',
        $valores
    );
    if ($id === 0) {
        return $resultado(false, ['general' => 'No pudimos guardar el aviso. Intenta de nuevo en un rato.']);
    }

    $carpeta = 'uploads/roomies/' . $id;
    if (!is_dir(SITIO_RAIZ . '/' . $carpeta) && !@mkdir(SITIO_RAIZ . '/' . $carpeta, 0755, true)) {
        roomies_ejecutar('DELETE FROM roomies WHERE id = ?', [$id]);
        return $resultado(false, ['general' => 'No pudimos guardar las fotos (carpeta uploads sin permisos).']);
    }
    foreach ($fotos as $orden => $tmp) {
        $foto = roomies_guardar_foto($tmp, $carpeta . '/' . bin2hex(random_bytes(6)));
        if ($foto !== null) {
            roomies_ejecutar(
                'INSERT INTO roomies_fotos (roomie_id, archivo, ancho, alto, orden) VALUES (?, ?, ?, ?, ?)',
                [$id, $foto['archivo'], $foto['ancho'], $foto['alto'], $orden]
            );
        }
    }
    roomies_registrar('publicar', $id);

    $enlaceVerificar = roomies_url_absoluta('roomies-verificar.php?token=' . $tokenVerificacion);
    $enlaceGestion = roomies_url_absoluta('roomies-gestionar.php?token=' . $tokenGestion);
    $texto = "Hola, {$d['autor_nombre']}:\n\n"
        . "Recibimos tu aviso «{$d['titulo']}» en Roomies.\n\n"
        . "Para publicarlo, confirma que este correo es tuyo:\n$enlaceVerificar\n\n"
        . "Guarda también este enlace privado. Con él puedes pausar, renovar o borrar el aviso cuando quieras:\n$enlaceGestion\n\n"
        . "No compartas estos enlaces. Si no fuiste tú, ignora este correo y el aviso no se publicará.\n\n"
        . marca() . " · Roomies";
    $enviado = roomies_enviar_correo($d['autor_correo'], 'Confirma tu aviso en Roomies', $texto);
    if (!$enviado) {
        error_log('[sitio CEPC · roomies] No se pudo enviar el correo de verificación del aviso ' . $id);
    }
    return ['ok' => true, 'errores' => [], 'datos' => $d, 'id' => $id, 'enviado' => $enviado];
}

/* --------------------------------------------------------------------- */
/*  Verificar y administrar                                               */
/* --------------------------------------------------------------------- */

/** Confirma el correo. Devuelve el aviso, o null si el enlace no sirve. */
function roomies_verificar(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{40}$/', $token)) {
        return null;
    }
    $filas = datos_filas("SELECT * FROM roomies WHERE token_verificacion = ? AND estado = 'pendiente'", [$token]);
    if ($filas === []) {
        return null;
    }
    $ahora = date('Y-m-d H:i:s');
    $estado = roomies_ajuste('moderar', false) ? 'revision' : 'publicado';
    roomies_ejecutar(
        'UPDATE roomies SET estado = ?, token_verificacion = NULL, verificado_en = ?, publicado_en = ?, expira_en = ?, actualizado_en = ? WHERE id = ?',
        [$estado, $ahora, $ahora, date('Y-m-d H:i:s', strtotime('+' . (int) roomies_ajuste('dias_vigencia', 60) . ' days')), $ahora, (int) $filas[0]['id']]
    );
    return ['id' => (int) $filas[0]['id'], 'estado' => $estado, 'token_gestion' => $filas[0]['token_gestion'], 'titulo' => $filas[0]['titulo']];
}

/** El aviso de un enlace de administración (cualquier estado menos eliminado). */
function roomies_por_gestion(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{40}$/', $token)) {
        return null;
    }
    $filas = datos_filas("SELECT * FROM roomies WHERE token_gestion = ? AND estado <> 'eliminado'", [$token]);
    if ($filas === []) {
        return null;
    }
    $id = (int) $filas[0]['id'];
    return roomie_preparar($filas[0], roomies_fotos([$id])[$id] ?? []) + ['estado_real' => $filas[0]['estado']];
}

/** pausar · reactivar · arrendado · renovar · eliminar. Devuelve el mensaje para mostrar. */
function roomies_gestionar(array $roomie, string $accion): string
{
    $ahora = date('Y-m-d H:i:s');
    $id = (int) $roomie['id'];
    $vence = date('Y-m-d H:i:s', strtotime('+' . (int) roomies_ajuste('dias_vigencia', 60) . ' days'));
    switch ($accion) {
        case 'pausar':
            roomies_ejecutar("UPDATE roomies SET estado = 'pausado', actualizado_en = ? WHERE id = ? AND estado = 'publicado'", [$ahora, $id]);
            return 'Pausamos tu aviso. Nadie lo verá hasta que lo reactives.';
        case 'reactivar':
            roomies_ejecutar("UPDATE roomies SET estado = 'publicado', expira_en = ?, actualizado_en = ? WHERE id = ? AND estado IN ('pausado', 'arrendado')", [$vence, $ahora, $id]);
            return 'Tu aviso está publicado otra vez.';
        case 'renovar':
            roomies_ejecutar("UPDATE roomies SET expira_en = ?, actualizado_en = ? WHERE id = ? AND estado = 'publicado'", [$vence, $ahora, $id]);
            return 'Renovado: queda publicado ' . (int) roomies_ajuste('dias_vigencia', 60) . ' días más.';
        case 'arrendado':
            roomies_ejecutar("UPDATE roomies SET estado = 'arrendado', actualizado_en = ? WHERE id = ?", [$ahora, $id]);
            return '¡Qué bien! Marcamos el aviso como arrendado y ya no aparece en Roomies.';
        case 'eliminar':
            roomies_borrar_fotos($id);
            // Se borran los datos personales; queda sólo el registro vacío.
            roomies_ejecutar(
                "UPDATE roomies SET estado = 'eliminado', descripcion = NULL, calle = NULL, autor_correo = '', autor_sobre = NULL,
                        whatsapp = NULL, instagram = NULL, token_verificacion = NULL, actualizado_en = ? WHERE id = ?",
                [$ahora, $id]
            );
            return 'Borramos tu aviso y sus fotos.';
    }
    return '';
}

/* --------------------------------------------------------------------- */
/*  Contactar                                                             */
/* --------------------------------------------------------------------- */

/** Manda el mensaje a quien publicó. Devuelve ['ok' => bool, 'errores' => [...]]. */
function roomies_contactar(array $roomie, array $p): array
{
    $nombre  = mb_substr(roomies_linea((string) ($p['nombre'] ?? '')), 0, 40);
    $correo  = strtolower(mb_substr(trim((string) ($p['correo'] ?? '')), 0, 120));
    $mensaje = mb_substr(trim(str_replace("\r", '', (string) ($p['mensaje'] ?? ''))), 0, 1500);

    $errores = [];
    if (!roomies_csrf_valido($p['csrf'] ?? null)) {
        $errores['general'] = 'La página estuvo abierta mucho rato. Vuelve a enviar el mensaje.';
    }
    if (mb_strlen($nombre) < 2) {
        $errores['nombre'] = 'Escribe tu nombre.';
    }
    if (!roomies_correo_valido($correo)) {
        $errores['correo'] = 'Usa tu correo de la Universidad: así quien publica sabe que eres estudiante.';
    }
    if (mb_strlen($mensaje) < 20) {
        $errores['mensaje'] = 'Cuéntale un poco de ti (al menos 20 letras).';
    }
    if ($errores !== []) {
        return ['ok' => false, 'errores' => $errores];
    }
    if (roomies_es_bot($p)) {
        return ['ok' => true, 'errores' => []];
    }
    if (!roomies_dentro_del_limite('contactar', 8, 60) || !roomies_dentro_del_limite('contactar', 2, 24 * 60, (int) $roomie['id'])) {
        return ['ok' => false, 'errores' => ['general' => 'Enviaste varios mensajes seguidos. Espera un rato antes de escribir de nuevo.']];
    }
    $fila = datos_filas('SELECT autor_correo, autor_nombre FROM roomies WHERE id = ?', [(int) $roomie['id']])[0] ?? null;
    if ($fila === null || $fila['autor_correo'] === '') {
        return ['ok' => false, 'errores' => ['general' => 'Este aviso ya no recibe mensajes.']];
    }

    $texto = "Hola, {$fila['autor_nombre']}:\n\n"
        . "$nombre ($correo) te escribió por tu aviso «{$roomie['titulo']}»:\n\n"
        . "------------------------------\n$mensaje\n------------------------------\n\n"
        . "Para responder, contesta este correo: le llega directo a $nombre.\n\n"
        . "Consejos: conózcanse en persona y visiten el lugar antes de transferir cualquier monto.\n\n"
        . marca() . ' · Roomies';
    $enviado = roomies_enviar_correo((string) $fila['autor_correo'], 'Mensaje por tu aviso: ' . $roomie['titulo'], $texto, $correo, $nombre);
    if (!$enviado) {
        return ['ok' => false, 'errores' => ['general' => 'No pudimos enviar el mensaje. Intenta de nuevo en un rato.']];
    }
    roomies_registrar('contactar', (int) $roomie['id']);
    return ['ok' => true, 'errores' => []];
}

/** Reporta un aviso al equipo. */
function roomies_reportar(array $roomie, array $p): bool
{
    if (!roomies_csrf_valido($p['csrf'] ?? null) || roomies_es_bot($p) || !roomies_dentro_del_limite('reportar', 5, 24 * 60)) {
        return false;
    }
    $destino = (string) (roomies_ajuste('correo_reportes', '') ?: sitio('correo', ''));
    $motivo = mb_substr(trim((string) ($p['motivo'] ?? '')), 0, 600);
    roomies_registrar('reportar', (int) $roomie['id']);
    if ($destino === '' || !filter_var($destino, FILTER_VALIDATE_EMAIL)) {
        error_log('[sitio CEPC · roomies] Reporte del aviso ' . $roomie['id'] . ': ' . $motivo);
        return true;
    }
    return roomies_enviar_correo($destino, 'Reporte de aviso en Roomies #' . $roomie['id'], "Aviso: {$roomie['titulo']} (#{$roomie['id']})\n" . roomies_url_absoluta(enlace_roomie((int) $roomie['id'])) . "\n\nMotivo:\n$motivo\n");
}
