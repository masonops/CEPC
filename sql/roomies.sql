-- ===========================================================================
--  CEPC · Sitio público — Roomies (arriendos entre estudiantes)
--
--  Las escribe el propio sitio (roomies-publicar.php) y las lee roomies.php.
--  Se importan una vez en la base del panel (phpMyAdmin → Importar).
--
--  roomies.estado:
--    pendiente   recién enviada; falta confirmar el correo institucional
--    revision    correo confirmado; espera que el equipo la apruebe
--                (sólo si config.php → roomies.moderar = true)
--    publicado   visible en el sitio hasta expira_en
--    pausado     la persona la ocultó por un tiempo
--    arrendado   ya encontró roomie (se oculta)
--    eliminado   borrada por la persona o por el equipo
--
--  Listas separadas por comas (claves de includes/datos-roomies.php):
--    incluye       agua,luz,gas,internet,gastos
--    comodidades   amoblada,bano_privado,closet,escritorio,…
--    autor_habitos madrugador,nocturno,ordenado,…
--
--  lat/lng son el punto exacto que marcó la persona: el sitio nunca lo
--  muestra; publica un punto corrido unos 100 m (ver roomie_punto_publico()).
-- ===========================================================================

-- MySQL / MariaDB ----------------------------------------------------------

CREATE TABLE IF NOT EXISTS roomies (
  id                 INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
  estado             VARCHAR(12)   NOT NULL DEFAULT 'pendiente',
  tipo               VARCHAR(16)   NOT NULL DEFAULT 'pieza',
  titulo             VARCHAR(90)   NOT NULL,
  descripcion        TEXT          NULL,
  comuna             VARCHAR(40)   NOT NULL,
  calle              VARCHAR(80)   NULL,
  lat                DECIMAL(9,6)  NOT NULL,
  lng                DECIMAL(9,6)  NOT NULL,
  precio             INT UNSIGNED  NOT NULL,
  gastos_comunes     INT UNSIGNED  NOT NULL DEFAULT 0,
  garantia_meses     DECIMAL(3,1)  NOT NULL DEFAULT 1,
  incluye            VARCHAR(80)   NULL,
  disponible_desde   DATE          NULL,
  estadia_minima     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  dormitorios        TINYINT UNSIGNED NOT NULL DEFAULT 1,
  banos              TINYINT UNSIGNED NOT NULL DEFAULT 1,
  m2                 SMALLINT UNSIGNED NULL,
  piso               TINYINT       NULL,
  piezas_libres      TINYINT UNSIGNED NOT NULL DEFAULT 1,
  roomies_actuales   TINYINT UNSIGNED NOT NULL DEFAULT 0,
  comodidades        VARCHAR(255)  NULL,
  mascotas           VARCHAR(10)   NOT NULL DEFAULT 'consultar',
  fumar              VARCHAR(10)   NOT NULL DEFAULT 'no',
  visitas            VARCHAR(10)   NOT NULL DEFAULT 'aviso',
  preferencia        VARCHAR(10)   NOT NULL DEFAULT 'todos',
  autor_nombre       VARCHAR(40)   NOT NULL,
  autor_correo       VARCHAR(120)  NOT NULL,
  autor_carrera      VARCHAR(60)   NULL,
  autor_ingreso      SMALLINT UNSIGNED NULL,
  autor_edad         TINYINT UNSIGNED NULL,
  autor_sobre        VARCHAR(600)  NULL,
  autor_habitos      VARCHAR(160)  NULL,
  whatsapp           VARCHAR(20)   NULL,
  mostrar_whatsapp   TINYINT(1)    NOT NULL DEFAULT 0,
  instagram          VARCHAR(40)   NULL,
  token_verificacion CHAR(40)      NULL,
  token_gestion      CHAR(40)      NOT NULL,
  verificado_en      DATETIME      NULL,
  publicado_en       DATETIME      NULL,
  expira_en          DATETIME      NULL,
  creado_en          DATETIME      NOT NULL,
  actualizado_en     DATETIME      NULL,
  KEY idx_roomies_estado (estado, expira_en),
  KEY idx_roomies_correo (autor_correo),
  UNIQUE KEY idx_roomies_gestion (token_gestion),
  KEY idx_roomies_verificacion (token_verificacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- archivo: ruta sin la terminación; el sitio agrega «-g.jpg» (grande,
-- 1600 px) o «-m.jpg» (miniatura, 640 px). Ej.: uploads/roomies/12/a1b2c3
CREATE TABLE IF NOT EXISTS roomies_fotos (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  roomie_id  INT UNSIGNED NOT NULL,
  archivo    VARCHAR(160) NOT NULL,
  ancho      SMALLINT UNSIGNED NULL,
  alto       SMALLINT UNSIGNED NULL,
  orden      TINYINT UNSIGNED NOT NULL DEFAULT 0,
  credito    VARCHAR(120) NULL,
  KEY idx_roomies_fotos (roomie_id, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registro mínimo para limitar mensajes y publicaciones por persona.
-- huella = hash de la IP (no se guarda la IP).
CREATE TABLE IF NOT EXISTS roomies_registro (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  accion     VARCHAR(12)  NOT NULL,
  roomie_id  INT UNSIGNED NULL,
  huella     CHAR(40)     NOT NULL,
  creado_en  DATETIME     NOT NULL,
  KEY idx_roomies_registro (accion, huella, creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- SQLite (si el panel usa DB_DRIVER=sqlite) --------------------------------
-- Copia y ejecuta esto en vez de lo de arriba:
--
-- CREATE TABLE IF NOT EXISTS roomies (
--   id INTEGER PRIMARY KEY AUTOINCREMENT, estado TEXT NOT NULL DEFAULT 'pendiente',
--   tipo TEXT NOT NULL DEFAULT 'pieza', titulo TEXT NOT NULL, descripcion TEXT,
--   comuna TEXT NOT NULL, calle TEXT, lat REAL NOT NULL, lng REAL NOT NULL,
--   precio INTEGER NOT NULL, gastos_comunes INTEGER NOT NULL DEFAULT 0,
--   garantia_meses REAL NOT NULL DEFAULT 1, incluye TEXT, disponible_desde TEXT,
--   estadia_minima INTEGER NOT NULL DEFAULT 0, dormitorios INTEGER NOT NULL DEFAULT 1,
--   banos INTEGER NOT NULL DEFAULT 1, m2 INTEGER, piso INTEGER,
--   piezas_libres INTEGER NOT NULL DEFAULT 1, roomies_actuales INTEGER NOT NULL DEFAULT 0,
--   comodidades TEXT, mascotas TEXT NOT NULL DEFAULT 'consultar',
--   fumar TEXT NOT NULL DEFAULT 'no', visitas TEXT NOT NULL DEFAULT 'aviso',
--   preferencia TEXT NOT NULL DEFAULT 'todos', autor_nombre TEXT NOT NULL,
--   autor_correo TEXT NOT NULL, autor_carrera TEXT, autor_ingreso INTEGER,
--   autor_edad INTEGER, autor_sobre TEXT, autor_habitos TEXT, whatsapp TEXT,
--   mostrar_whatsapp INTEGER NOT NULL DEFAULT 0, instagram TEXT,
--   token_verificacion TEXT, token_gestion TEXT NOT NULL UNIQUE,
--   verificado_en TEXT, publicado_en TEXT, expira_en TEXT,
--   creado_en TEXT NOT NULL, actualizado_en TEXT
-- );
-- CREATE TABLE IF NOT EXISTS roomies_fotos (
--   id INTEGER PRIMARY KEY AUTOINCREMENT, roomie_id INTEGER NOT NULL,
--   archivo TEXT NOT NULL, ancho INTEGER, alto INTEGER,
--   orden INTEGER NOT NULL DEFAULT 0, credito TEXT
-- );
-- CREATE TABLE IF NOT EXISTS roomies_registro (
--   id INTEGER PRIMARY KEY AUTOINCREMENT, accion TEXT NOT NULL, roomie_id INTEGER,
--   huella TEXT NOT NULL, creado_en TEXT NOT NULL
-- );
