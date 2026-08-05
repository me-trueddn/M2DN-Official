<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/Bootstrap.php';

use App\Core\Security;

$pdo = new PDO(
    'mysql:host=127.0.0.1;port=3306;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_turkish_ci");
$pdo->exec("SET sql_mode = 'NO_ENGINE_SUBSTITUTION'");

$login = 'trueddn';
$name = 'trueddn';
$pass = Security::hashAccountPassword('12345');
$job = 5; // Ninja (E)
$empire = 1;

$pdo->beginTransaction();
try {
    $old = $pdo->prepare('SELECT id FROM account.account WHERE login = ?');
    $old->execute([$login]);
    $id = $old->fetchColumn();
    if ($id) {
        $pdo->prepare('DELETE FROM player.player WHERE account_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM player.player_index WHERE id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM account.account WHERE id = ?')->execute([$id]);
    }

    $pdo->prepare(
        "INSERT INTO account.account
            (login, password, social_id, email, create_time, status, empire, cash, mileage, channel_company, ip)
         VALUES (?, ?, ?, ?, NOW(), 'OK', ?, 1000, 0, 'M2DN', '127.0.0.1')"
    )->execute([$login, $pass, '2000001', 'trueddn@test.local', $empire]);

    $accountId = (int) $pdo->lastInsertId();

    $pdo->prepare(
        "INSERT INTO player.player
            (account_id, name, job, voice, dir, x, y, z, map_index,
             exit_x, exit_y, exit_map_index, hp, mp, stamina,
             random_hp, random_sp, playtime, level, level_step,
             st, ht, dx, iq, exp, gold, stat_point, skill_point,
             part_main, part_base, part_hair, skill_group, alignment,
             last_play, change_name, sub_skill_point, stat_reset_count,
             horse_hp, horse_stamina, horse_level, horse_hp_droptime, horse_riding, horse_skill_point, ip)
         VALUES
            (?, ?, ?, 0, 0, 474300, 954700, 0, 1,
             474300, 954700, 1, 800, 400, 1000,
             0, 0, 0, 1, 0,
             2, 3, 6, 2, 0, 100000, 0, 0,
             0, 1, 0, 0, 0,
             NOW(), 0, 0, 0,
             0, 0, 0, 0, 0, 0, '127.0.0.1')"
    )->execute([$accountId, $name, $job]);

    $playerId = (int) $pdo->lastInsertId();

    $pdo->prepare(
        'INSERT INTO player.player_index (id, pid1, pid2, pid3, pid4, empire) VALUES (?, ?, 0, 0, 0, ?)'
    )->execute([$accountId, $playerId, $empire]);

    $pdo->commit();
    echo "OK\n";
    echo "Hesap: {$login}\n";
    echo "Karakter: {$name}\n";
    echo "Sınıf: Ninja\n";
    echo "Şifre: 12345 (MD5 yalnızca password)\n";
    echo "account_id={$accountId} player_id={$playerId}\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
