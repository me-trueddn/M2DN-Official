<?php

declare(strict_types=1);

/**
 * trueddn hesabına 3 ek karakter (farklı sınıf / level / gold).
 * Mevcut karakter silinmez; player_index slotları güncellenir.
 */

require dirname(__DIR__) . '/app/Bootstrap.php';

$pdo = new PDO(
    'mysql:host=127.0.0.1;port=3306;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);
$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_turkish_ci");
$pdo->exec("SET sql_mode = 'NO_ENGINE_SUBSTITUTION'");

$login = 'trueddn';
$newChars = [
    // name, job, level, gold
    ['TrueSavasci', 0, 45, 250000],  // Savaşçı E
    ['TrueNinja',   5, 72, 890000],  // Ninja E
    ['TrueSaman',   3, 55, 410000],  // Şaman K
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
$jobNames = [
    0 => 'Savaşçı (E)', 1 => 'Ninja (K)', 2 => 'Sura (E)', 3 => 'Şaman (K)',
    4 => 'Savaşçı (K)', 5 => 'Ninja (E)', 6 => 'Sura (K)', 7 => 'Şaman (E)',
];

$acc = $pdo->prepare('SELECT id, empire FROM account.account WHERE login = ? LIMIT 1');
$acc->execute([$login]);
$row = $acc->fetch();
if (!$row) {
    fwrite(STDERR, "Hesap yok: {$login}\n");
    exit(1);
}
$accountId = (int) $row['id'];
$empire = (int) ($row['empire'] ?: 1);
$spawn = match ($empire) {
    2 => ['map' => 21, 'x' => 96900, 'y' => 231900],
    3 => ['map' => 41, 'x' => 863800, 'y' => 246000],
    default => ['map' => 1, 'x' => 474300, 'y' => 954700],
};

$pdo->beginTransaction();
try {
    $existing = $pdo->prepare('SELECT id, name FROM player.player WHERE account_id = ? ORDER BY id');
    $existing->execute([$accountId]);
    $have = $existing->fetchAll() ?: [];
    $haveNames = array_map(static fn ($r) => (string) $r['name'], $have);

    $ins = $pdo->prepare(
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

    $added = [];
    foreach ($newChars as [$name, $job, $level, $gold]) {
        if (in_array($name, $haveNames, true)) {
            echo "Atlandı (zaten var): {$name}\n";
            continue;
        }
        // Global isim çakışması
        $chk = $pdo->prepare('SELECT id FROM player.player WHERE name = ? LIMIT 1');
        $chk->execute([$name]);
        if ($chk->fetchColumn()) {
            echo "Atlandı (isim dolu): {$name}\n";
            continue;
        }
        [$st, $ht, $dx, $iq, $hp, $mp, $partBase] = $jobStats[$job];
        $hp += $level * 8;
        $mp += $level * 4;
        $ins->execute([
            $accountId, $name, $job,
            $spawn['x'], $spawn['y'], $spawn['map'],
            $spawn['x'], $spawn['y'], $spawn['map'],
            $hp, $mp, $level,
            $st, $ht, $dx, $iq, $gold, $partBase,
        ]);
        $pid = (int) $pdo->lastInsertId();
        $added[] = compact('name', 'job', 'level', 'gold') + ['id' => $pid];
        echo "Eklendi: {$name} (id={$pid}) {$jobNames[$job]} Lv{$level} gold={$gold}\n";
    }

    // player_index slotlarını güncelle (max 4)
    $all = $pdo->prepare('SELECT id FROM player.player WHERE account_id = ? ORDER BY id ASC LIMIT 4');
    $all->execute([$accountId]);
    $pids = array_map('intval', $all->fetchAll(PDO::FETCH_COLUMN) ?: []);
    while (count($pids) < 4) {
        $pids[] = 0;
    }
    $pdo->prepare(
        'INSERT INTO player.player_index (id, pid1, pid2, pid3, pid4, empire)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE pid1=VALUES(pid1), pid2=VALUES(pid2), pid3=VALUES(pid3), pid4=VALUES(pid4), empire=VALUES(empire)'
    )->execute([$accountId, $pids[0], $pids[1], $pids[2], $pids[3], $empire]);

    $pdo->commit();
    echo "OK — account_id={$accountId} slotlar: " . implode(',', $pids) . "\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
