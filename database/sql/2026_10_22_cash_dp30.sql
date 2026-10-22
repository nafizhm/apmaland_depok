-- APMALAND: kolom harga DP 30%. Pilih database aplikasi sebelum import.

-- Skema disetujui: DP = 30% harga cash, total bertahap tetap, cicilan = (total - DP) / tenor.
-- Pembulatan ke rupiah terdekat, cicilan terakhir menyesuaikan sisa total.
-- Jalankan sesudah 2026_10_22_harga_paramita_booking.sql, lalu upload kode terbaru.

SET NAMES utf8mb4;

SET @apma_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kavling_peta' AND COLUMN_NAME = 'dp_cash_bertahap_30') = 0, 'ALTER TABLE `kavling_peta` ADD COLUMN `dp_cash_bertahap_30` BIGINT UNSIGNED NULL', 'SELECT 1');

PREPARE apma_stmt FROM @apma_sql;

EXECUTE apma_stmt;

DEALLOCATE PREPARE apma_stmt;

SET @apma_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kavling_peta' AND COLUMN_NAME = 'harga_bertahap_3_dp30') = 0, 'ALTER TABLE `kavling_peta` ADD COLUMN `harga_bertahap_3_dp30` BIGINT UNSIGNED NULL', 'SELECT 1');

PREPARE apma_stmt FROM @apma_sql;

EXECUTE apma_stmt;

DEALLOCATE PREPARE apma_stmt;

SET @apma_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kavling_peta' AND COLUMN_NAME = 'cicilan_3_dp30') = 0, 'ALTER TABLE `kavling_peta` ADD COLUMN `cicilan_3_dp30` BIGINT UNSIGNED NULL', 'SELECT 1');

PREPARE apma_stmt FROM @apma_sql;

EXECUTE apma_stmt;

DEALLOCATE PREPARE apma_stmt;

SET @apma_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kavling_peta' AND COLUMN_NAME = 'harga_bertahap_6_dp30') = 0, 'ALTER TABLE `kavling_peta` ADD COLUMN `harga_bertahap_6_dp30` BIGINT UNSIGNED NULL', 'SELECT 1');

PREPARE apma_stmt FROM @apma_sql;

EXECUTE apma_stmt;

DEALLOCATE PREPARE apma_stmt;

SET @apma_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kavling_peta' AND COLUMN_NAME = 'cicilan_6_dp30') = 0, 'ALTER TABLE `kavling_peta` ADD COLUMN `cicilan_6_dp30` BIGINT UNSIGNED NULL', 'SELECT 1');

PREPARE apma_stmt FROM @apma_sql;

EXECUTE apma_stmt;

DEALLOCATE PREPARE apma_stmt;

SET @apma_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kavling_peta' AND COLUMN_NAME = 'harga_bertahap_12_dp30') = 0, 'ALTER TABLE `kavling_peta` ADD COLUMN `harga_bertahap_12_dp30` BIGINT UNSIGNED NULL', 'SELECT 1');

PREPARE apma_stmt FROM @apma_sql;

EXECUTE apma_stmt;

DEALLOCATE PREPARE apma_stmt;

SET @apma_sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'kavling_peta' AND COLUMN_NAME = 'cicilan_12_dp30') = 0, 'ALTER TABLE `kavling_peta` ADD COLUMN `cicilan_12_dp30` BIGINT UNSIGNED NULL', 'SELECT 1');

PREPARE apma_stmt FROM @apma_sql;

EXECUTE apma_stmt;

DEALLOCATE PREPARE apma_stmt;

INSERT INTO migrations (migration, batch) SELECT '2026_10_22_170000_add_cash_dp30_pricing', (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations) WHERE NOT EXISTS (SELECT 1 FROM migrations WHERE migration = '2026_10_22_170000_add_cash_dp30_pricing');

-- DATA DP 30%
DROP TEMPORARY TABLE IF EXISTS apma_dp30_units;
CREATE TEMPORARY TABLE apma_dp30_units (kode VARCHAR(10) PRIMARY KEY, lb DECIMAL(10,1), lt DECIMAL(10,1));
INSERT INTO apma_dp30_units (kode, lb, lt) VALUES
('B-11', 30, 49),
('A-15', 30, 50),
('A-16', 30, 50),
('B-16', 30, 50),
('B-17', 30, 50),
('B-18', 30, 50),
('B-21', 30, 50),
('B-22', 30, 50),
('B-28', 30, 50),
('B-29', 30, 50),
('B-30', 30, 50),
('B-31', 30, 50),
('B-32', 30, 50),
('B-33', 30, 50),
('B-34', 30, 50),
('B-35', 30, 50),
('B-36', 30, 50),
('B-37', 30, 50),
('C-01', 30, 50),
('C-02', 30, 50),
('C-03', 30, 50),
('C-04', 30, 50),
('C-05', 30, 50),
('C-06', 30, 50),
('C-07', 30, 50),
('C-08', 30, 50),
('C-09', 30, 50),
('C-10', 30, 50),
('C-12', 30, 50),
('C-13', 30, 50),
('C-14', 30, 50),
('C-15', 30, 50),
('C-18', 30, 50),
('C-19', 30, 50),
('C-20', 30, 50),
('C-21', 30, 50),
('C-22', 30, 50),
('C-23', 30, 50),
('D-02', 30, 50),
('D-03', 30, 50),
('D-04', 30, 50),
('D-05', 30, 50),
('D-06', 30, 50),
('D-07', 30, 50),
('D-08', 30, 50),
('D-09', 30, 50),
('D-10', 30, 50),
('D-11', 30, 50),
('B-04', 30, 51),
('B-05', 30, 51),
('B-19', 30, 51),
('B-20', 30, 51),
('B-27', 30, 51),
('B-03', 30, 52),
('B-08', 30, 52),
('B-13', 30, 52),
('B-14', 30, 52),
('B-23', 30, 52),
('B-02', 30, 53),
('B-07', 30, 53),
('C-16', 30, 53),
('B-12', 30, 54),
('C-17', 30, 56),
('B-24', 30, 57),
('B-25', 30, 58),
('B-01', 30, 60),
('B-26', 30, 63),
('D-01', 30, 69),
('B-06', 30, 70),
('B-15', 30, 73),
('B-09', 30, 84),
('D-12', 30, 86),
('C-11', 30, 87),
('B-10', 30, 89),
('A-14', 30, 90),
('A-13', 30, 117),
('D-14', 36, 60),
('D-20', 36, 64),
('D-15', 36, 65),
('D-19', 36, 68),
('D-16', 36, 70),
('D-18', 36, 73),
('D-17', 36, 74),
('D-13', 36, 82),
('A-07', 40, 72),
('A-08', 40, 72),
('A-09', 40, 72),
('A-10', 40, 72),
('A-11', 40, 72),
('A-12', 40, 72),
('A-01', 40, 74),
('A-02', 40, 75),
('A-03', 40, 75),
('A-05', 40, 75),
('A-04', 40, 76),
('A-06', 40, 120);

START TRANSACTION;
SET @apma_dp30_locations = (SELECT COUNT(*) FROM lokasi_kavling WHERE LOWER(TRIM(nama_kavling)) = 'paramita residence');
SET @apma_dp30_matches = (SELECT COUNT(*) FROM kavling_peta k JOIN lokasi_kavling l ON l.id = k.id_lokasi JOIN apma_dp30_units u ON u.kode = CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) WHERE LOWER(TRIM(l.nama_kavling)) = 'paramita residence' AND k.luas_bangunan = u.lb AND k.luas_tanah = u.lt AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30));
SET @apma_dp30_distinct = (SELECT COUNT(DISTINCT u.kode) FROM kavling_peta k JOIN lokasi_kavling l ON l.id = k.id_lokasi JOIN apma_dp30_units u ON u.kode = CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) WHERE LOWER(TRIM(l.nama_kavling)) = 'paramita residence' AND k.luas_bangunan = u.lb AND k.luas_tanah = u.lt AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30));
UPDATE kavling_peta k JOIN lokasi_kavling l ON l.id = k.id_lokasi JOIN apma_dp30_units u ON u.kode = CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0'))
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) = 'paramita residence' AND k.luas_bangunan = u.lb AND k.luas_tanah = u.lt AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30)
AND @apma_dp30_locations = 1 AND @apma_dp30_matches = 96 AND @apma_dp30_distinct = 96;
COMMIT;
SELECT IF(@apma_dp30_locations = 1 AND @apma_dp30_matches = 96 AND @apma_dp30_distinct = 96, 'BERHASIL: DP 30% untuk 96 unit Paramita tersimpan', 'DP 30% BELUM DIUPDATE: cek lokasi, kode, luas, dan kelengkapan harga dasar') AS hasil_import, @apma_dp30_matches AS unit_cocok;
DROP TEMPORARY TABLE apma_dp30_units;
