USE db_ppdb_smk;

ALTER TABLE users
  MODIFY role ENUM('admin','panitia','verifikator','siswa') NOT NULL DEFAULT 'siswa';

ALTER TABLE users
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role,
  ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NULL,
  aktivitas VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_log_user (id_user),
  FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL
) ENGINE=InnoDB;
