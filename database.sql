CREATE DATABASE IF NOT EXISTS drs
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE drs;

CREATE TABLE IF NOT EXISTS residents (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  first_name    VARCHAR(50)  NOT NULL,
  last_name     VARCHAR(50)  NOT NULL,
  email         VARCHAR(100) NOT NULL UNIQUE,
  phone         VARCHAR(15)  NOT NULL,
  age           TINYINT UNSIGNED NOT NULL,
  address       VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admins (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_login_attempts (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip           VARCHAR(45)  NOT NULL,
  attempted_at DATETIME     NOT NULL,
  INDEX idx_ip (ip)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(100) NOT NULL,
  token_hash CHAR(64)     NOT NULL,
  expires_at DATETIME     NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_token (token_hash),
  INDEX idx_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS document_types (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  code            CHAR(2)      NOT NULL UNIQUE,
  name            VARCHAR(80)  NOT NULL,
  description     VARCHAR(200) NOT NULL,
  category        VARCHAR(30)  NOT NULL,
  processing_time VARCHAR(30)  NOT NULL,
  icon            VARCHAR(20)  NOT NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO document_types (code, name, description, category, processing_time, icon) VALUES
  ('BC', 'Barangay Clearance',          'Official clearance issued by the barangay for legal and administrative purposes.',        'Certificates', '2-3 working days', 'file'),
  ('CR', 'Certificate of Residency',    'Proof of residency within the barangay for various transactions.',                        'Certificates', '1-2 working days', 'home'),
  ('IC', 'Indigency Certificate',       'Certification for residence in need of financial assistance or other benefits.',          'Certificates', '1-2 working days', 'users'),
  ('BP', 'Business Permit Endorsement', 'Endorsement required for applying or renewing a business permit.',                        'Permits',      '2-3 working days', 'store'),
  ('CD', 'Cedula',                      'Official certificate issued to residents as proof of community tax payment.',             'IDs',          '1 working day',    'card'),
  ('BI', 'Barangay ID',                 'Official identification card issued by the barangay to verify resident identity and residency.', 'IDs',   '3-5 working days', 'id');

CREATE TABLE IF NOT EXISTS requests (
  id               INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  reference_no     VARCHAR(20)  NULL UNIQUE,
  resident_id      INT UNSIGNED NOT NULL,
  document_type_id INT UNSIGNED NOT NULL,
  purpose          VARCHAR(60)  NOT NULL,
  copies           TINYINT UNSIGNED NOT NULL DEFAULT 1,
  notes            VARCHAR(255) NOT NULL DEFAULT '',
  status           ENUM('In Progress', 'Completed', 'Declined') NOT NULL DEFAULT 'In Progress',
  decline_reason   VARCHAR(255) NULL,
  verified_at      DATETIME     NULL,
  ready_at         DATETIME     NULL,
  created_at       DATETIME     NOT NULL,
  updated_at       DATETIME     NOT NULL,
  INDEX idx_resident (resident_id),
  INDEX idx_status (status),
  CONSTRAINT fk_request_resident FOREIGN KEY (resident_id)
    REFERENCES residents (id) ON DELETE CASCADE,
  CONSTRAINT fk_request_type FOREIGN KEY (document_type_id)
    REFERENCES document_types (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  resident_id INT UNSIGNED NOT NULL,
  request_id  INT UNSIGNED NULL,
  title       VARCHAR(100) NOT NULL,
  message     VARCHAR(255) NOT NULL,
  is_read     TINYINT(1)   NOT NULL DEFAULT 0,
  created_at  DATETIME     NOT NULL,
  INDEX idx_resident (resident_id, is_read),
  CONSTRAINT fk_notification_resident FOREIGN KEY (resident_id)
    REFERENCES residents (id) ON DELETE CASCADE,
  CONSTRAINT fk_notification_request FOREIGN KEY (request_id)
    REFERENCES requests (id) ON DELETE CASCADE
) ENGINE=InnoDB;
