<?php

declare(strict_types=1);

/**
 * 20 test hesabı + her birine 1 farklı karakter.
 * MD5 yalnızca account.password kolonuna yazılır.
 * login / email / social_id / karakter adı düz metin kalır.
 */

require dirname(__DIR__) . '/app/Bootstrap.php';

use App\Core\Security;

$pdo = new PDO(
    'mysql:host=127.0.0.1;port=3306;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_turkish_ci");
$pdo->exec("SET sql_mode = 'NO_ENGINE_SUBSTITUTION'");

$passwordPlain = '12345';
$passwordMd5 = Security::hashAccountPassword($passwordPlain); // sadece password kolonu

// job: 0 Savaşçı E, 1 Ninja K, 2 Sura E, 3 Şaman K, 4 Savaşçı K, 5 Ninja E, 6 Sura K, 7 Şaman E
$characters = [
    ['login' => 'm2dn01', 'name' => 'KaraKilic',   'job' => 0, 'empire' => 1, 'level' => 55, 'gold' => 500000],
    ['login' => 'm2dn02', 'name' => 'GolgeAdim',   'job' => 1, 'empire' => 1, 'level' => 48, 'gold' => 420000],
    ['login' => 'm2dn03', 'name' => 'SuraHan',     'job' => 2, 'empire' => 1, 'level' => 62, 'gold' => 680000],
    ['login' => 'm2dn04', 'name' => 'YesilNefes',  'job' => 3, 'empire' => 1, 'level' => 40, 'gold' => 310000],
    ['login' => 'm2dn05', 'name' => 'DemirKol',    'job' => 4, 'empire' => 2, 'level' => 71, 'gold' => 900000],
    ['login' => 'm2dn06', 'name' => 'RuzgarBic',   'job' => 5, 'empire' => 2, 'level' => 33, 'gold' => 180000],
    ['login' => 'm2dn07', 'name' => 'KaranlikRuh', 'job' => 6, 'empire' => 2, 'level' => 88, 'gold' => 1500000],
    ['login' => 'm2dn08', 'name' => 'AyIsigi',     'job' => 7, 'empire' => 2, 'level' => 29, 'gold' => 95000],
    ['login' => 'm2dn09', 'name' => 'AlevKalkan',  'job' => 0, 'empire' => 3, 'level' => 95, 'gold' => 2200000],
    ['login' => 'm2dn10', 'name' => 'SessizOk',    'job' => 1, 'empire' => 3, 'level' => 51, 'gold' => 470000],
    ['login' => 'm2dn11', 'name' => 'KanBuyucu',   'job' => 2, 'empire' => 3, 'level' => 77, 'gold' => 1100000],
    ['login' => 'm2dn12', 'name' => 'SifaRuzgari', 'job' => 3, 'empire' => 3, 'level' => 44, 'gold' => 260000],
    ['login' => 'm2dn13', 'name' => 'CelikYumruk', 'job' => 4, 'empire' => 1, 'level' => 66, 'gold' => 740000],
    ['login' => 'm2dn14', 'name' => 'HizliBicak',  'job' => 5, 'empire' => 1, 'level' => 38, 'gold' => 210000],
    ['login' => 'm2dn15', 'name' => 'GeceLordu',   'job' => 6, 'empire' => 2, 'level' => 81, 'gold' => 1300000],
    ['login' => 'm2dn16', 'name' => 'DogaAna',     'job' => 7, 'empire' => 2, 'level' => 57, 'gold' => 530000],
    ['login' => 'm2dn17', 'name' => 'FirtinaKral', 'job' => 0, 'empire' => 3, 'level' => 99, 'gold' => 3500000],
    ['login' => 'm2dn18', 'name' => 'GizemliGol',  'job' => 1, 'empire' => 3, 'level' => 22, 'gold' => 45000],
    ['login' => 'm2dn19', 'name' => 'AteşEjderi',  'job' => 2, 'empire' => 1, 'level' => 73, 'gold' => 980000],
    ['login' => 'm2dn20', 'name' => 'IşıkTapınak', 'job' => 3, 'empire' => 2, 'level' => 60, 'gold' => 610000],
];

$jobStats = [
    // st, ht, dx, iq, hp, mp, part_base
    0 => [6, 4, 2, 1, 800, 200, 0], // Savaşçı E
    1 => [2, 3, 6, 2, 550, 280, 1], // Ninja K
    2 => [3, 3, 3, 4, 620, 360, 2], // Sura E
    3 => [1, 3, 2, 6, 480, 520, 3], // Şaman K
    4 => [6, 4, 2, 1, 780, 210, 0], // Savaşçı K
    5 => [2, 3, 6, 2, 560, 270, 1], // Ninja E
    6 => [3, 3, 3, 4, 600, 380, 2], // Sura K
    7 => [1, 3, 2, 6, 470, 540, 3], // Şaman E
];

$empireSpawn = [
    1 => ['map' => 1,  'x' => 474300, 'y' => 954700],
    2 => ['map' => 21, 'x' => 96900,  'y' => 231900],
    3 => ['map' => 41, 'x' => 863800, 'y' => 246000],
];

$jobNames = [
    0 => 'Savaşçı (E)',
    1 => 'Ninja (K)',
    2 => 'Sura (E)',
    3 => 'Şaman (K)',
    4 => 'Savaşçı (K)',
    5 => 'Ninja (E)',
    6 => 'Sura (K)',
    7 => 'Şaman (E)',
];

$pdo->beginTransaction();

try {
    // Eski seed temizliği
    $logins = array_column($characters, 'login');
    $placeholders = implode(',', array_fill(0, count($logins), '?'));

    $ids = $pdo->prepare("SELECT id FROM account.account WHERE login IN ($placeholders)");
    $ids->execute($logins);
    $oldIds = $ids->fetchAll(PDO::FETCH_COLUMN);

    if ($oldIds) {
        $idPlace = implode(',', array_fill(0, count($oldIds), '?'));
        $pdo->prepare("DELETE FROM player.player WHERE account_id IN ($idPlace)")->execute($oldIds);
        $pdo->prepare("DELETE FROM player.player_index WHERE id IN ($idPlace)")->execute($oldIds);
        $pdo->prepare("DELETE FROM account.account WHERE id IN ($idPlace)")->execute($oldIds);
    }

    $insAccount = $pdo->prepare(
        "INSERT INTO account.account
            (login, password, social_id, email, create_time, status, empire, cash, mileage, channel_company, ip)
         VALUES
            (?, ?, ?, ?, NOW(), 'OK', ?, 1000, 0, 'M2DN', '127.0.0.1')"
    );

    $insPlayer = $pdo->prepare(
        "INSERT INTO player.player
            (account_id, name, job, voice, dir, x, y, z, map_index,
             exit_x, exit_y, exit_map_index, hp, mp, stamina,
             random_hp, random_sp, playtime, level, level_step,
             st, ht, dx, iq, exp, gold, stat_point, skill_point,
             part_main, part_base, part_hair, skill_group, alignment,
             last_play, change_name, sub_skill_point, stat_reset_count,
             horse_hp, horse_stamina, horse_level, horse_hp_droptime, horse_riding, horse_skill_point, ip)
         VALUES
            (?, ?, ?, 0, 0, ?, ?, 0, ?,
             ?, ?, ?, ?, ?, 1000,
             0, 0, 0, ?, 0,
             ?, ?, ?, ?, 0, ?, 0, 0,
             0, ?, 0, 0, 0,
             NOW(), 0, 0, 0,
             0, 0, 0, 0, 0, 0, '127.0.0.1')"
    );

    $insIndex = $pdo->prepare(
        "INSERT INTO player.player_index (id, pid1, pid2, pid3, pid4, empire)
         VALUES (?, ?, 0, 0, 0, ?)"
    );

    $created = [];

    foreach ($characters as $i => $c) {
        $social = (string) (1000001 + $i);
        $email = $c['login'] . '@test.local';

        $insAccount->execute([
            $c['login'],
            $passwordMd5,
            $social,
            $email,
            $c['empire'],
        ]);

        $accountId = (int) $pdo->lastInsertId();
        $spawn = $empireSpawn[$c['empire']];
        [$st, $ht, $dx, $iq, $hp, $mp, $partBase] = $jobStats[$c['job']];

        // Level'e göre kaba HP/MP artışı
        $hp = $hp + ($c['level'] * 8);
        $mp = $mp + ($c['level'] * 4);

        $insPlayer->execute([
            $accountId,
            $c['name'],
            $c['job'],
            $spawn['x'],
            $spawn['y'],
            $spawn['map'],
            $spawn['x'],
            $spawn['y'],
            $spawn['map'],
            $hp,
            $mp,
            $c['level'],
            $st,
            $ht,
            $dx,
            $iq,
            $c['gold'],
            $partBase,
        ]);

        $playerId = (int) $pdo->lastInsertId();
        $insIndex->execute([$accountId, $playerId, $c['empire']]);

        $created[] = [
            'login' => $c['login'],
            'password' => $passwordPlain,
            'account_id' => $accountId,
            'player_id' => $playerId,
            'name' => $c['name'],
            'job' => $jobNames[$c['job']],
            'empire' => $c['empire'],
            'level' => $c['level'],
        ];
    }

    $pdo->commit();

    echo "OK — " . count($created) . " hesap + karakter oluşturuldu\n";
    echo str_pad('Login', 10) . str_pad('Karakter', 14) . str_pad('Sınıf', 14) . str_pad('Emp', 5) . "Lv\n";
    echo str_repeat('-', 50) . "\n";
    foreach ($created as $row) {
        echo str_pad($row['login'], 10)
            . str_pad($row['name'], 14)
            . str_pad($row['job'], 14)
            . str_pad((string) $row['empire'], 5)
            . $row['level'] . "\n";
    }
    echo "\nŞifre (hepsi): {$passwordPlain}\n";
    echo "account.password (yalnızca bu kolon MD5): {$passwordMd5}\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'HATA: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
