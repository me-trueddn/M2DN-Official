-- M2DN test hesapları
-- Şifre düz metin: 12345
-- MD5 YALNIZCA account.password kolonunda: 827ccb0eea8a706c4c34a16891f84e7b
-- login / email / social_id düz metin kalır.
SET NAMES utf8mb4;
SET sql_mode = 'NO_ENGINE_SUBSTITUTION';

START TRANSACTION;

-- Temiz başlangıç (sadece bu seed'in oluşturacağı login'ler)
DELETE FROM player.player WHERE account_id IN (SELECT id FROM account.account WHERE login LIKE 'm2dn%');
DELETE FROM player.player_index WHERE id IN (SELECT id FROM account.account WHERE login LIKE 'm2dn%');
DELETE FROM account.account WHERE login LIKE 'm2dn%';

-- -------------------- HESAPLAR --------------------
INSERT INTO account.account
  (login, password, social_id, email, create_time, status, empire, cash, mileage, channel_company, ip)
VALUES
  ('m2dn01', '827ccb0eea8a706c4c34a16891f84e7b', '1000001', 'm2dn01@test.local', NOW(), 'OK', 1, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn02', '827ccb0eea8a706c4c34a16891f84e7b', '1000002', 'm2dn02@test.local', NOW(), 'OK', 1, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn03', '827ccb0eea8a706c4c34a16891f84e7b', '1000003', 'm2dn03@test.local', NOW(), 'OK', 1, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn04', '827ccb0eea8a706c4c34a16891f84e7b', '1000004', 'm2dn04@test.local', NOW(), 'OK', 1, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn05', '827ccb0eea8a706c4c34a16891f84e7b', '1000005', 'm2dn05@test.local', NOW(), 'OK', 2, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn06', '827ccb0eea8a706c4c34a16891f84e7b', '1000006', 'm2dn06@test.local', NOW(), 'OK', 2, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn07', '827ccb0eea8a706c4c34a16891f84e7b', '1000007', 'm2dn07@test.local', NOW(), 'OK', 2, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn08', '827ccb0eea8a706c4c34a16891f84e7b', '1000008', 'm2dn08@test.local', NOW(), 'OK', 2, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn09', '827ccb0eea8a706c4c34a16891f84e7b', '1000009', 'm2dn09@test.local', NOW(), 'OK', 3, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn10', '827ccb0eea8a706c4c34a16891f84e7b', '1000010', 'm2dn10@test.local', NOW(), 'OK', 3, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn11', '827ccb0eea8a706c4c34a16891f84e7b', '1000011', 'm2dn11@test.local', NOW(), 'OK', 3, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn12', '827ccb0eea8a706c4c34a16891f84e7b', '1000012', 'm2dn12@test.local', NOW(), 'OK', 3, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn13', '827ccb0eea8a706c4c34a16891f84e7b', '1000013', 'm2dn13@test.local', NOW(), 'OK', 1, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn14', '827ccb0eea8a706c4c34a16891f84e7b', '1000014', 'm2dn14@test.local', NOW(), 'OK', 1, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn15', '827ccb0eea8a706c4c34a16891f84e7b', '1000015', 'm2dn15@test.local', NOW(), 'OK', 2, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn16', '827ccb0eea8a706c4c34a16891f84e7b', '1000016', 'm2dn16@test.local', NOW(), 'OK', 2, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn17', '827ccb0eea8a706c4c34a16891f84e7b', '1000017', 'm2dn17@test.local', NOW(), 'OK', 3, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn18', '827ccb0eea8a706c4c34a16891f84e7b', '1000018', 'm2dn18@test.local', NOW(), 'OK', 3, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn19', '827ccb0eea8a706c4c34a16891f84e7b', '1000019', 'm2dn19@test.local', NOW(), 'OK', 1, 1000, 0, 'M2DN', '127.0.0.1'),
  ('m2dn20', '827ccb0eea8a706c4c34a16891f84e7b', '1000020', 'm2dn20@test.local', NOW(), 'OK', 2, 1000, 0, 'M2DN', '127.0.0.1');

COMMIT;
