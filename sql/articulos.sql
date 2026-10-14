-- ===========================================================================
--  CEPC · Sitio público — Tabla de publicaciones (propuestas y artículos)
--
--  La crea el panel cuando se agregue el módulo para escribir publicaciones.
--  El sitio público la lee en sólo lectura (includes/datos-articulos.php)
--  y, mientras no exista, muestra la sección vacía con un aviso amable.
--
--  categoria:  propuesta · fechas · noticia
--  estado:     borrador · publicado (el sitio sólo muestra «publicado»)
--  cuerpo:     texto simple. Párrafos separados por una línea en blanco;
--              «## » al inicio de una línea es un subtítulo y «- » un punto
--              de una lista.
-- ===========================================================================

-- MySQL / MariaDB
CREATE TABLE IF NOT EXISTS articulos (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  titulo       VARCHAR(160) NOT NULL,
  resumen      VARCHAR(400) NULL,
  cuerpo       TEXT         NULL,
  categoria    VARCHAR(20)  NOT NULL DEFAULT 'propuesta',
  imagen       VARCHAR(255) NULL,
  autor        VARCHAR(80)  NULL,
  destacado    TINYINT(1)   NOT NULL DEFAULT 0,
  estado       VARCHAR(20)  NOT NULL DEFAULT 'borrador',
  publicado_en DATETIME     NULL,
  creado_en    DATETIME     NULL,
  KEY idx_articulos_estado (estado, publicado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SQLite (preview local del panel): misma tabla
-- CREATE TABLE IF NOT EXISTS articulos (
--   id INTEGER PRIMARY KEY AUTOINCREMENT, titulo TEXT NOT NULL, resumen TEXT,
--   cuerpo TEXT, categoria TEXT NOT NULL DEFAULT 'propuesta', imagen TEXT,
--   autor TEXT, destacado INTEGER NOT NULL DEFAULT 0,
--   estado TEXT NOT NULL DEFAULT 'borrador', publicado_en TEXT, creado_en TEXT
-- );
