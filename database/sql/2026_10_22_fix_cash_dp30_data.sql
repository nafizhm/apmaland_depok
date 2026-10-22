-- PERBAIKAN DATA DP 30% PARAMITA RESIDENCE

-- Jalankan setelah kolom pada 2026_10_22_cash_dp30.sql tersedia.

-- Mengisi DP 30% dari harga cash tersimpan, total bertahap tetap.

-- Tidak mengubah harga DP 50%, harga KPR, atau booking lama.

-- Tidak memakai variabel sesi atau tabel sementara.

SET NAMES utf8mb4;

START TRANSACTION;

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-11')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 49
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('A-15', 'A-16', 'B-16', 'B-17', 'B-18', 'B-21', 'B-22', 'B-28', 'B-29', 'B-30', 'B-31', 'B-32', 'B-33', 'B-34', 'B-35', 'B-36', 'B-37', 'C-01', 'C-02', 'C-03', 'C-04', 'C-05', 'C-06', 'C-07', 'C-08', 'C-09', 'C-10', 'C-12', 'C-13', 'C-14', 'C-15', 'C-18', 'C-19', 'C-20', 'C-21', 'C-22', 'C-23', 'D-02', 'D-03', 'D-04', 'D-05', 'D-06', 'D-07', 'D-08', 'D-09', 'D-10', 'D-11')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 50
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-04', 'B-05', 'B-19', 'B-20', 'B-27')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 51
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-03', 'B-08', 'B-13', 'B-14', 'B-23')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 52
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-02', 'B-07', 'C-16')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 53
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-12')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 54
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('C-17')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 56
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-24')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 57
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-25')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 58
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-01')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 60
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-26')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 63
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('D-01')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 69
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-06')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 70
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-15')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 73
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-09')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 84
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('D-12')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 86
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('C-11')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 87
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-10')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 89
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('A-14')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 90
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('A-13')
  AND k.luas_bangunan = 30 AND k.luas_tanah = 117
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('D-14')
  AND k.luas_bangunan = 36 AND k.luas_tanah = 60
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('D-20')
  AND k.luas_bangunan = 36 AND k.luas_tanah = 64
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('D-15')
  AND k.luas_bangunan = 36 AND k.luas_tanah = 65
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('D-19')
  AND k.luas_bangunan = 36 AND k.luas_tanah = 68
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('D-16')
  AND k.luas_bangunan = 36 AND k.luas_tanah = 70
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('D-18')
  AND k.luas_bangunan = 36 AND k.luas_tanah = 73
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('D-17')
  AND k.luas_bangunan = 36 AND k.luas_tanah = 74
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('D-13')
  AND k.luas_bangunan = 36 AND k.luas_tanah = 82
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('A-07', 'A-08', 'A-09', 'A-10', 'A-11', 'A-12')
  AND k.luas_bangunan = 40 AND k.luas_tanah = 72
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('A-01')
  AND k.luas_bangunan = 40 AND k.luas_tanah = 74
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('A-02', 'A-03', 'A-05')
  AND k.luas_bangunan = 40 AND k.luas_tanah = 75
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('A-04')
  AND k.luas_bangunan = 40 AND k.luas_tanah = 76
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

UPDATE kavling_peta k
JOIN lokasi_kavling l ON l.id = k.id_lokasi
SET k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30),
    k.harga_bertahap_3_dp30 = k.harga_bertahap_3,
    k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3),
    k.harga_bertahap_6_dp30 = k.harga_bertahap_6,
    k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6),
    k.harga_bertahap_12_dp30 = k.harga_bertahap_12,
    k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12)
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('A-06')
  AND k.luas_bangunan = 40 AND k.luas_tanah = 120
  AND k.harga_cash > 0 AND k.harga_bertahap_3 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_6 > ROUND(k.harga_cash * 0.30) AND k.harga_bertahap_12 > ROUND(k.harga_cash * 0.30);

COMMIT;

-- Hasil yang diharapkan: jumlah_unit = 96 dan data_dp30_benar = 96.

SELECT COUNT(*) AS jumlah_unit,
       SUM(CASE WHEN k.dp_cash_bertahap_30 = ROUND(k.harga_cash * 0.30)
          AND k.harga_bertahap_3_dp30 = k.harga_bertahap_3
          AND k.cicilan_3_dp30 = ROUND((k.harga_bertahap_3 - ROUND(k.harga_cash * 0.30)) / 3)
          AND k.harga_bertahap_6_dp30 = k.harga_bertahap_6
          AND k.cicilan_6_dp30 = ROUND((k.harga_bertahap_6 - ROUND(k.harga_cash * 0.30)) / 6)
          AND k.harga_bertahap_12_dp30 = k.harga_bertahap_12
          AND k.cicilan_12_dp30 = ROUND((k.harga_bertahap_12 - ROUND(k.harga_cash * 0.30)) / 12) THEN 1 ELSE 0 END) AS data_dp30_benar
FROM kavling_peta k JOIN lokasi_kavling l ON l.id = k.id_lokasi
WHERE LOWER(TRIM(l.nama_kavling)) IN ('pramita residence', 'paramita residence')
  AND CONCAT(LEFT(UPPER(TRIM(k.kode_kavling)), 1), '-', LPAD(CAST(SUBSTRING(REPLACE(UPPER(TRIM(k.kode_kavling)), '-', ''), 2) AS UNSIGNED), 2, '0')) IN ('B-11', 'A-15', 'A-16', 'B-16', 'B-17', 'B-18', 'B-21', 'B-22', 'B-28', 'B-29', 'B-30', 'B-31', 'B-32', 'B-33', 'B-34', 'B-35', 'B-36', 'B-37', 'C-01', 'C-02', 'C-03', 'C-04', 'C-05', 'C-06', 'C-07', 'C-08', 'C-09', 'C-10', 'C-12', 'C-13', 'C-14', 'C-15', 'C-18', 'C-19', 'C-20', 'C-21', 'C-22', 'C-23', 'D-02', 'D-03', 'D-04', 'D-05', 'D-06', 'D-07', 'D-08', 'D-09', 'D-10', 'D-11', 'B-04', 'B-05', 'B-19', 'B-20', 'B-27', 'B-03', 'B-08', 'B-13', 'B-14', 'B-23', 'B-02', 'B-07', 'C-16', 'B-12', 'C-17', 'B-24', 'B-25', 'B-01', 'B-26', 'D-01', 'B-06', 'B-15', 'B-09', 'D-12', 'C-11', 'B-10', 'A-14', 'A-13', 'D-14', 'D-20', 'D-15', 'D-19', 'D-16', 'D-18', 'D-17', 'D-13', 'A-07', 'A-08', 'A-09', 'A-10', 'A-11', 'A-12', 'A-01', 'A-02', 'A-03', 'A-05', 'A-04', 'A-06');
