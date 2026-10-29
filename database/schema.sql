-- ==========================================
-- database/schema.sql
-- Skema database PARKIRKU UP
-- Jalankan sekali untuk setup awal
-- Untuk database yang sudah ada, jalankan migration 20261022_add_civitas_study_fields.sql
-- dan 20261026_add_member_profile_fields.sql satu kali.
-- ==========================================

CREATE DATABASE IF NOT EXISTS parkirku_up
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE parkirku_up;

-- -----------------------------------------------
-- Tabel users_civitas (mahasiswa, dosen, staf)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS users_civitas (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nama        VARCHAR(150)    NOT NULL,
    email       VARCHAR(254)    NOT NULL UNIQUE,
    foto_profil VARCHAR(255)    NULL,
    npm         VARCHAR(20)     NOT NULL UNIQUE,         -- NPM / NIP / NIDN
    password    VARCHAR(255)    NOT NULL,                -- bcrypt hash
    peran       ENUM('Mahasiswa','Dosen','Tenaga Kependidikan') NOT NULL DEFAULT 'Mahasiswa',
    fakultas    VARCHAR(100)    NOT NULL DEFAULT 'Fakultas Teknik',
    jenjang     VARCHAR(3)      NULL,
    program_studi VARCHAR(150)  NULL,
    plat        VARCHAR(20)     NOT NULL,
    tipe        ENUM('Mobil','Motor') NOT NULL DEFAULT 'Mobil',
    berlaku     VARCHAR(50)     NOT NULL DEFAULT '1 Semester (6 Bulan)',
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------
-- Tabel users_mitra (vendor/mitra kampus)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS users_mitra (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    id_mitra    VARCHAR(20)     NOT NULL UNIQUE,         -- MTR-UP-xxx
    nama        VARCHAR(150)    NOT NULL,                -- nama usaha
    pic         VARCHAR(150)    NOT NULL,                -- penanggung jawab
    email       VARCHAR(254)    NOT NULL UNIQUE,
    foto_profil VARCHAR(255)    NULL,
    password    VARCHAR(255)    NOT NULL,
    plat        VARCHAR(20)     NOT NULL,
    tipe        ENUM('Mobil','Motor') NOT NULL DEFAULT 'Mobil',
    berlaku     VARCHAR(50)     NOT NULL DEFAULT '1 Bulan (Aktif)',
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------
-- Tabel users_admin (petugas / administrator)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS users_admin (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)     NOT NULL UNIQUE,
    nama        VARCHAR(150)    NOT NULL,
    password    VARCHAR(255)    NOT NULL,
    role        ENUM('Admin','Petugas') NOT NULL DEFAULT 'Admin',
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------
-- Seed: akun admin default
-- password: admin123
-- -----------------------------------------------
INSERT IGNORE INTO users_admin (username, nama, password, role)
VALUES (
    'admin',
    'Administrator Parkir UP',
    '$2y$10$rq3A7KY8Yfk86F1NOWp9ZeDy3UrOXqF1mJHg/bi9XceAPs2zkx/4e',
    'Admin'
);

-- -----------------------------------------------
-- Tabel parkir_log (opsional - riwayat keluar-masuk)
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS parkir_log (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    plat            VARCHAR(20)     NOT NULL,
    tipe            ENUM('Mobil','Motor') NOT NULL,
    peran           VARCHAR(50)     NOT NULL DEFAULT 'Umum',
    waktu_masuk     DATETIME        NOT NULL,
    waktu_keluar    DATETIME        NULL,
    durasi_jam      TINYINT UNSIGNED NULL,
    biaya           INT UNSIGNED    NOT NULL DEFAULT 0,
    status          ENUM('Parkir','Keluar') NOT NULL DEFAULT 'Parkir',
    created_at      TIMESTAMP       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
