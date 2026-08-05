<?php

declare(strict_types=1);

/**
 * 5 lonca oluştur; m2dn01–20 karakterlerini dağıt (usta + üyeler).
 * Lonca başına ortalama ≥ 3 kişi (20 / 5 = 4).
 * Eksik kalırsa ek hesap/karakter üretir.
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
$passwordMd5 = Security::hashAccountPassword($passwordPlain);

$guildDefs = [
    ['name' => 'EjderKan',   'level' => 5, 'ladder' => 1200],
    ['name' => 'GolgeSavas', 'level' => 4, 'ladder' => 980],
    ['name' => 'AlevKalkan', 'level' => 6, 'ladder' => 1450],
    ['name' => 'DemirYumruk','level' => 3, 'ladder' => 720],
    ['name' => 'AyIsigiLon', 'level' => 4, 'ladder' => 890],
];

$gradeDefaults = [
    15 => ['Lider', 'ADD_MEMBER,REMOVE_MEMEBER,NOTICE,USE_SKILL'],
    14 => ['Subay', 'ADD_MEMBER,NOTICE,USE_SKILL'],
    13 => ['Usta', 'NOTICE,USE_SKILL'],
    12 => ['Kıdemli', 'USE_SKILL'],
    11 => ['Üye11', 'USE_SKILL'],
    10 => ['Üye10', 'USE_SKILL'],
    9  => ['Üye9', 'USE_SKILL'],
    8  => ['Üye8', 'USE_SKILL'],
    7  => ['Üye7', 'USE_SKILL'],
    6  => ['Üye6', 'USE_SKILL'],
    5  => ['Üye5', 'USE_SKILL'],
    4  => ['Üye4', 'USE_SKILL'],
    3  => ['Üye3', 'USE_SKILL'],
    2  => ['Üye2', 'USE_SKILL'],
    1  => ['Üye', 'USE_SKILL'],
];

$jobStats = [
    0 => [6, 4, 2, 1, 800, 200, 0],
    1 => [2, 3, 6, 2, 550, 280, 1],
    2 => [3, 3, 3, 4, 620, 360, 2],
    3 => [1, 3, 2, 6, 480, 520, 3],
    4 => [6, 4, 2, 1, 780, 210, 0],
    5 => [2, 3, 6, 2, 560, 270, 1],
    6 => [3, 3, 3, 4, 600, 380, 2],
    7 => [1, 3, 2, 6, 470, 540, 3],
];
$empireSpawn = [
    1 => ['map' => 1,  'x' => 474300, 'y' => 954700],
    2 => ['map' => 21, 'x' => 96900,  'y' => 231900],
    3 => ['map' => 41, 'x' => 863800, 'y' => 246000],
];

$extraCharNames = [
    'KaraRuzgar', 'TasDuvar', 'GumusOk', 'BuzNefes', 'KizilSur',
    'YesilYaprak', 'DemirOk', 'SessizGece', 'AlevRuh', 'GumusKilic',
];

function ensureMinPlayers(PDO $pdo, int $need, string $passwordMd5, array $jobStats, array $empireSpawn, array $extraCharNames): array
{
    $stmt = $pdo->query(
        "SELECT p.id AS player_id, p.account_id, p.name, p.level, a.login
         FROM player.player p
         INNER JOIN account.account a ON a.id = p.account_id
         WHERE a.login <> 'trueddn'
         ORDER BY p.level DESC, p.id ASC"
    );
    $players = $stmt->fetchAll();

    if (count($players) >= $need) {
        return $players;
    }

    $missing = $need - count($players);
    echo "Eksik karakter: {$missing} — ek hesap/karakter oluşturuluyor…\n";

    $insAccount = $pdo->prepare(
        "INSERT INTO account.account
            (login, password, social_id, email, create_time, status, empire, cash, mileage, channel_company, ip)
         VALUES (?, ?, ?, ?, NOW(), 'OK', ?, 1000, 0, 'M2DN', '127.0.0.1')"
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
         VALUES (?, ?, 0, 0, 0, ?)
         ON DUPLICATE KEY UPDATE pid1 = VALUES(pid1), empire = VALUES(empire)"
    );

    $usedNames = $pdo->query('SELECT name FROM player.player')->fetchAll(PDO::FETCH_COLUMN) ?: [];
    $usedLogins = $pdo->query('SELECT login FROM account.account')->fetchAll(PDO::FETCH_COLUMN) ?: [];
    $nameIdx = 0;
    $n = 0;
    while ($n < $missing) {
        $login = 'm2dnG' . str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT);
        while (in_array($login, $usedLogins, true)) {
            $login = 'm2dnG' . substr(md5((string) microtime(true) . $n), 0, 4);
        }
        $charName = $extraCharNames[$nameIdx % count($extraCharNames)] ?? ('Uye' . ($n + 1));
        $nameIdx++;
        $suffix = 0;
        $base = $charName;
        while (in_array($charName, $usedNames, true) || mb_strlen($charName) > 24) {
            $suffix++;
            $charName = mb_substr($base, 0, 20) . $suffix;
        }

        $job = $n % 8;
        $empire = ($n % 3) + 1;
        $level = 35 + ($n % 40);
        $gold = 100000 + ($n * 5000);
        $spawn = $empireSpawn[$empire];
        [$st, $ht, $dx, $iq, $hp, $mp, $partBase] = $jobStats[$job];
        $hp += $level * 8;
        $mp += $level * 4;

        $insAccount->execute([
            $login,
            $passwordMd5,
            (string) (3000001 + $n),
            $login . '@test.local',
            $empire,
        ]);
        $accountId = (int) $pdo->lastInsertId();
        $insPlayer->execute([
            $accountId, $charName, $job,
            $spawn['x'], $spawn['y'], $spawn['map'],
            $spawn['x'], $spawn['y'], $spawn['map'],
            $hp, $mp, $level,
            $st, $ht, $dx, $iq, $gold, $partBase,
        ]);
        $playerId = (int) $pdo->lastInsertId();
        $insIndex->execute([$accountId, $playerId, $empire]);

        $usedLogins[] = $login;
        $usedNames[] = $charName;
        $players[] = [
            'player_id' => $playerId,
            'account_id' => $accountId,
            'name' => $charName,
            'level' => $level,
            'login' => $login,
        ];
        $n++;
        echo "  + {$login} / {$charName} (#{$playerId})\n";
    }

    return $players;
}

$pdo->beginTransaction();
try {
    // Temiz başlangıç: lonca tabloları
    $pdo->exec('DELETE FROM player.guild_member');
    $pdo->exec('DELETE FROM player.guild_grade');
    $pdo->exec('DELETE FROM player.guild_comment');
    $pdo->exec('DELETE FROM player.guild_war');
    $pdo->exec('DELETE FROM player.guild_war_bet');
    $pdo->exec('DELETE FROM player.guild_war_reservation');
    $pdo->exec('DELETE FROM player.guild');

    $minNeeded = count($guildDefs) * 3; // ortalama ≥ 3
    $players = ensureMinPlayers($pdo, $minNeeded, $passwordMd5, $jobStats, $empireSpawn, $extraCharNames);

    // Öncelik: m2dn01–20 karakterleri (seviyeye göre sıralı zaten)
    // 5 usta = en yüksek 5; kalanlar üye
    $masters = array_slice($players, 0, 5);
    $members = array_slice($players, 5);

    $insGuild = $pdo->prepare(
        'INSERT INTO player.guild (name, sp, master, level, exp, skill_point, win, draw, loss, ladder_point, gold)
         VALUES (?, 1000, ?, ?, 0, 0, ?, 0, ?, ?, ?)'
    );
    $insMember = $pdo->prepare(
        'INSERT INTO player.guild_member (pid, guild_id, grade, is_general, offer)
         VALUES (?, ?, ?, ?, 0)'
    );
    $insGrade = $pdo->prepare(
        'INSERT INTO player.guild_grade (guild_id, grade, name, auth) VALUES (?, ?, ?, ?)'
    );

    $created = [];
    $buckets = array_fill(0, 5, []);

    foreach ($masters as $i => $m) {
        $buckets[$i][] = ['player' => $m, 'is_master' => true];
    }
    foreach ($members as $idx => $m) {
        $buckets[$idx % 5][] = ['player' => $m, 'is_master' => false];
    }

    // Her loncada en az 3 kişi garantisi
    foreach ($buckets as $i => $bucket) {
        if (count($bucket) < 3) {
            throw new RuntimeException('Lonca #' . ($i + 1) . ' için yeterli üye yok (' . count($bucket) . ').');
        }
    }

    foreach ($guildDefs as $i => $def) {
        $bucket = $buckets[$i];
        $master = null;
        foreach ($bucket as $row) {
            if (!empty($row['is_master'])) {
                $master = $row['player'];
                break;
            }
        }
        if ($master === null) {
            $master = $bucket[0]['player'];
        }

        $wins = 2 + $i;
        $losses = $i;
        $gold = 500000 + ($i * 100000);

        $insGuild->execute([
            $def['name'],
            (int) $master['player_id'],
            $def['level'],
            $wins,
            $losses,
            $def['ladder'],
            $gold,
        ]);
        $guildId = (int) $pdo->lastInsertId();

        foreach ($gradeDefaults as $grade => [$gName, $auth]) {
            $insGrade->execute([$guildId, $grade, $gName, $auth]);
        }

        $memberList = [];
        foreach ($bucket as $row) {
            $p = $row['player'];
            $isMaster = ((int) $p['player_id'] === (int) $master['player_id']);
            $grade = $isMaster ? 15 : 1;
            $insMember->execute([
                (int) $p['player_id'],
                $guildId,
                $grade,
                $isMaster ? 1 : 0,
            ]);
            $memberList[] = [
                'name' => $p['name'],
                'login' => $p['login'],
                'pid' => (int) $p['player_id'],
                'role' => $isMaster ? 'Usta' : 'Üye',
            ];
        }

        $created[] = [
            'id' => $guildId,
            'name' => $def['name'],
            'master' => $master['name'],
            'master_login' => $master['login'],
            'count' => count($memberList),
            'members' => $memberList,
        ];
    }

    $pdo->commit();

    echo "OK — " . count($created) . " lonca oluşturuldu\n";
    echo str_repeat('=', 60) . "\n";
    foreach ($created as $g) {
        echo sprintf(
            "#%d %s · Usta: %s (%s) · Üye: %d\n",
            $g['id'],
            $g['name'],
            $g['master'],
            $g['master_login'],
            $g['count']
        );
        foreach ($g['members'] as $m) {
            echo "   - [{$m['role']}] {$m['name']} / {$m['login']} (#{$m['pid']})\n";
        }
        echo "\n";
    }
    $avg = array_sum(array_column($created, 'count')) / max(1, count($created));
    echo 'Ortalama üye: ' . number_format($avg, 1) . "\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, 'HATA: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
