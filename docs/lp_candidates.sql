-- ============================================================
--  Table lp_candidates — leads franchise
--  À importer dans atelierby_db via PHPMyAdmin
-- ============================================================

CREATE TABLE IF NOT EXISTS lp_candidates (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    first_name  VARCHAR(100)    NOT NULL,
    last_name   VARCHAR(100)    NOT NULL,
    email       VARCHAR(255)    NOT NULL,
    phone       VARCHAR(40)     DEFAULT NULL,
    area        VARCHAR(120)    DEFAULT NULL,   -- zone géographique souhaitée
    message     TEXT            DEFAULT NULL,
    lang        ENUM('fr','nl') NOT NULL DEFAULT 'fr',
    status      ENUM('new','contacted','qualified','rejected') NOT NULL DEFAULT 'new',
    notes       TEXT            DEFAULT NULL,   -- notes internes équipe
    ip          VARCHAR(45)     DEFAULT NULL,
    user_agent  VARCHAR(255)    DEFAULT NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_email  (email),
    KEY idx_status (status),
    KEY idx_date   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
