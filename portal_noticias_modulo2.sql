-- =====================================================================
-- Portal de Noticias - Módulo 2: estructura de tablas del portal
-- Equivalente a las migraciones 2026_10_14_000001 a 000008.
--
-- USO: solo si NO corriste `php artisan migrate`. Si ya migraste, no lo ejecutes
--      (las tablas ya existen). Requiere que la tabla `users` de Laravel exista.
-- =====================================================================

USE portal_noticias;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- users: campos de autor
-- ---------------------------------------------------------------------
ALTER TABLE users
    ADD COLUMN firma  VARCHAR(120) NULL AFTER name,
    ADD COLUMN avatar VARCHAR(255) NULL AFTER email,
    ADD COLUMN bio    TEXT NULL AFTER avatar,
    ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER bio;

-- ---------------------------------------------------------------------
-- categorias
-- ---------------------------------------------------------------------
CREATE TABLE categorias (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(80)  NOT NULL,
    slug        VARCHAR(100) NOT NULL,
    descripcion VARCHAR(255) NULL,
    color       VARCHAR(7)   NOT NULL DEFAULT '#d62828',
    orden       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    en_menu     TINYINT(1)   NOT NULL DEFAULT 1,
    en_portada  TINYINT(1)   NOT NULL DEFAULT 1,
    activa      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY categorias_slug_unique (slug),
    KEY categorias_activa_orden_index (activa, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- noticias
-- ---------------------------------------------------------------------
CREATE TABLE noticias (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    categoria_id     BIGINT UNSIGNED NOT NULL,
    user_id          BIGINT UNSIGNED NULL,
    volanta          VARCHAR(80)  NULL,
    titulo           VARCHAR(200) NOT NULL,
    slug             VARCHAR(220) NOT NULL,
    bajada           TEXT NULL,
    cuerpo           LONGTEXT NOT NULL,
    imagen           VARCHAR(255) NULL,
    epigrafe         VARCHAR(255) NULL,
    video_url        VARCHAR(255) NULL,
    estado           ENUM('borrador','publicada') NOT NULL DEFAULT 'borrador',
    publicada_at     DATETIME NULL,
    destacada        TINYINT(1) NOT NULL DEFAULT 0,
    orden_portada    SMALLINT UNSIGNED NULL,
    visitas          INT UNSIGNED NOT NULL DEFAULT 0,
    meta_titulo      VARCHAR(255) NULL,
    meta_descripcion VARCHAR(255) NULL,
    created_at       TIMESTAMP NULL,
    updated_at       TIMESTAMP NULL,
    deleted_at       TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY noticias_slug_unique (slug),
    KEY noticias_estado_publicada_at_index (estado, publicada_at),
    KEY noticias_categoria_id_estado_publicada_at_index (categoria_id, estado, publicada_at),
    KEY noticias_destacada_publicada_at_index (destacada, publicada_at),
    KEY noticias_user_id_foreign (user_id),
    CONSTRAINT noticias_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE RESTRICT,
    CONSTRAINT noticias_user_id_foreign      FOREIGN KEY (user_id)      REFERENCES users (id)      ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- tags y noticia_tag
-- ---------------------------------------------------------------------
CREATE TABLE tags (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre     VARCHAR(80)  NOT NULL,
    slug       VARCHAR(100) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY tags_slug_unique (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE noticia_tag (
    noticia_id BIGINT UNSIGNED NOT NULL,
    tag_id     BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (noticia_id, tag_id),
    KEY noticia_tag_tag_id_foreign (tag_id),
    CONSTRAINT noticia_tag_noticia_id_foreign FOREIGN KEY (noticia_id) REFERENCES noticias (id) ON DELETE CASCADE,
    CONSTRAINT noticia_tag_tag_id_foreign     FOREIGN KEY (tag_id)     REFERENCES tags (id)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- visitas_diarias
-- ---------------------------------------------------------------------
CREATE TABLE visitas_diarias (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    noticia_id BIGINT UNSIGNED NOT NULL,
    fecha      DATE NOT NULL,
    cantidad   INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY visitas_diarias_noticia_id_fecha_unique (noticia_id, fecha),
    KEY visitas_diarias_fecha_cantidad_index (fecha, cantidad),
    CONSTRAINT visitas_diarias_noticia_id_foreign FOREIGN KEY (noticia_id) REFERENCES noticias (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- publicidades
-- ---------------------------------------------------------------------
CREATE TABLE publicidades (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre        VARCHAR(120) NOT NULL,
    posicion      ENUM('header','sidebar','entre_bloques','nota','footer') NOT NULL,
    imagen        VARCHAR(255) NOT NULL,
    imagen_mobile VARCHAR(255) NULL,
    url           VARCHAR(255) NULL,
    desde         DATE NULL,
    hasta         DATE NULL,
    orden         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    clics         INT UNSIGNED NOT NULL DEFAULT 0,
    activa        TINYINT(1) NOT NULL DEFAULT 1,
    created_at    TIMESTAMP NULL,
    updated_at    TIMESTAMP NULL,
    PRIMARY KEY (id),
    KEY publicidades_posicion_activa_orden_index (posicion, activa, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- cotizaciones
-- ---------------------------------------------------------------------
CREATE TABLE cotizaciones (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo         VARCHAR(40) NOT NULL,
    nombre         VARCHAR(60) NOT NULL,
    compra         DECIMAL(14,2) NULL,
    venta          DECIMAL(14,2) NULL,
    actualizada_at DATETIME NULL,
    orden          SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    visible        TINYINT(1) NOT NULL DEFAULT 1,
    created_at     TIMESTAMP NULL,
    updated_at     TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY cotizaciones_codigo_unique (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- configuraciones
-- ---------------------------------------------------------------------
CREATE TABLE configuraciones (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    clave       VARCHAR(80) NOT NULL,
    valor       TEXT NULL,
    grupo       VARCHAR(40) NOT NULL DEFAULT 'general',
    descripcion VARCHAR(255) NULL,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,
    PRIMARY KEY (id),
    UNIQUE KEY configuraciones_clave_unique (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Registrar las migraciones como ejecutadas (para que Laravel no intente
-- crearlas de nuevo al correr `php artisan migrate` más adelante)
-- ---------------------------------------------------------------------
SET @lote = (SELECT IFNULL(MAX(batch), 0) + 1 FROM migrations);

INSERT INTO migrations (migration, batch) VALUES
('2026_10_14_000001_add_campos_autor_to_users_table', @lote),
('2026_10_14_000002_create_categorias_table',         @lote),
('2026_10_14_000003_create_noticias_table',           @lote),
('2026_10_14_000004_create_tags_table',               @lote),
('2026_10_14_000005_create_visitas_diarias_table',    @lote),
('2026_10_14_000006_create_publicidades_table',       @lote),
('2026_10_14_000007_create_cotizaciones_table',       @lote),
('2026_10_14_000008_create_configuraciones_table',    @lote);
