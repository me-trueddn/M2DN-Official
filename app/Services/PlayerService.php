<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use App\Core\ServerManager;
use PDO;

final class PlayerService
{
    private const JOBS = [
        0 => [
            'name' => 'Savaşçı',
            'icon' => 'fa-khanda',
            'gender' => 'E',
            'gif' => 'img/classes/warrior_m.gif',
        ],
        1 => [
            'name' => 'Ninja',
            'icon' => 'fa-wind',
            'gender' => 'K',
            'gif' => 'img/classes/ninja_f.gif',
        ],
        2 => [
            'name' => 'Sura',
            'icon' => 'fa-skull',
            'gender' => 'E',
            'gif' => 'img/classes/sura_m.gif',
        ],
        3 => [
            'name' => 'Şaman',
            'icon' => 'fa-leaf',
            'gender' => 'K',
            'gif' => 'img/classes/shaman_f.gif',
        ],
        4 => [
            'name' => 'Savaşçı',
            'icon' => 'fa-khanda',
            'gender' => 'K',
            'gif' => 'img/classes/warrior_f.gif',
        ],
        5 => [
            'name' => 'Ninja',
            'icon' => 'fa-wind',
            'gender' => 'E',
            'gif' => 'img/classes/ninja_m.gif',
        ],
        6 => [
            'name' => 'Sura',
            'icon' => 'fa-skull',
            'gender' => 'K',
            'gif' => 'img/classes/sura_f.gif',
        ],
        7 => [
            'name' => 'Şaman',
            'icon' => 'fa-leaf',
            'gender' => 'E',
            'gif' => 'img/classes/shaman_m.gif',
        ],
        8 => [
            'name' => 'Lycan',
            'icon' => 'fa-paw',
            'gender' => 'E',
            'gif' => 'img/classes/lycan.gif',
        ],
    ];

    private const EMPIRES = [
        0 => 'Seçilmedi',
        1 => 'Shinsoo',
        2 => 'Chunjo',
        3 => 'Jinno',
    ];

    /**
     * @return array{
     *   account: array,
     *   characters: list<array>,
     *   primary: ?array,
     *   max_level: int,
     *   character_count: int,
     *   total_yang: int,
     *   open_tickets: int
     * }
     */
    public static function dashboard(int $accountId, ?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: ServerManager::current()['key'] ?? null;
        $account = self::loadAccount($accountId, $serverKey);
        $characters = self::loadCharacters($accountId, $serverKey);
        $maxLevel = self::resolveMaxLevel($serverKey);

        $primary = $characters[0] ?? null;
        $totalYang = 0;
        foreach ($characters as $ch) {
            $totalYang += (int) ($ch['gold'] ?? 0);
        }

        $openTickets = TicketService::openCountForAccount($accountId);

        return [
            'account' => $account,
            'characters' => $characters,
            'primary' => $primary,
            'max_level' => $maxLevel,
            'character_count' => count($characters),
            'total_yang' => $totalYang,
            'open_tickets' => $openTickets,
        ];
    }

    public static function jobLabel(int $job): string
    {
        $info = self::JOBS[$job] ?? ['name' => 'Bilinmiyor', 'gender' => ''];
        $g = $info['gender'] !== '' ? ' (' . $info['gender'] . ')' : '';
        return $info['name'] . $g;
    }

    public static function jobIcon(int $job): string
    {
        return self::JOBS[$job]['icon'] ?? 'fa-user';
    }

    public static function jobGif(int $job): string
    {
        $path = self::JOBS[$job]['gif'] ?? '';
        if ($path === '') {
            return '';
        }
        return \asset($path);
    }

    public static function empireLabel(int $empire): string
    {
        return self::EMPIRES[$empire] ?? 'Bilinmiyor';
    }

    public static function formatPlaytime(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 saat';
        }
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        if ($hours >= 24) {
            $days = intdiv($hours, 24);
            $hours = $hours % 24;
            return $days . 'g ' . $hours . 's';
        }
        if ($hours > 0) {
            return $hours . ' saat' . ($mins > 0 ? ' ' . $mins . ' dk' : '');
        }
        return $mins . ' dk';
    }

    public static function levelProgressPercent(int $level, int $maxLevel, int $levelStep = 0): int
    {
        $maxLevel = max(1, $maxLevel);
        $level = max(0, min($level, $maxLevel));

        // Seviye / maks seviye ana ilerleme
        $base = ($level / $maxLevel) * 100;

        // level_step (0-3) varsa mevcut seviye içinde ince ayar
        if ($level < $maxLevel && $levelStep > 0) {
            $step = min(3, max(0, $levelStep));
            $perLevel = 100 / $maxLevel;
            $base = (($level - 1) / $maxLevel) * 100 + (($step + 1) / 4) * $perLevel;
        }

        return (int) max(0, min(100, round($base)));
    }

    /**
     * Sadece hesaba ait karakterlerde isim araması.
     *
     * @return list<array>
     */
    public static function searchOwnedCharacters(int $accountId, string $query, ?string $serverKey = null): array
    {
        $query = trim($query);
        if ($query === '' || strlen($query) < 2) {
            return [];
        }

        $serverKey = $serverKey ?: ServerManager::current()['key'] ?? null;
        $chars = self::loadCharacters($accountId, $serverKey);
        $out = [];
        foreach ($chars as $ch) {
            if (stripos((string) $ch['name'], $query) !== false) {
                $out[] = $ch;
            }
        }
        return $out;
    }

    /**
     * Karakter yalnızca hesap sahibi ise döner; aksi halde null.
     */
    public static function getOwnedCharacter(int $accountId, int $characterId, ?string $serverKey = null): ?array
    {
        if ($characterId <= 0) {
            return null;
        }

        $serverKey = $serverKey ?: ServerManager::current()['key'] ?? null;
        $pdo = Database::player($serverKey);

        $sql = 'SELECT p.id, p.name, p.job, p.level, p.level_step, p.exp, p.gold, p.playtime, p.last_play,
                       p.account_id, g.name AS guild_name
                FROM player p
                LEFT JOIN guild_member gm ON gm.pid = p.id
                LEFT JOIN guild g ON g.id = gm.guild_id
                WHERE p.id = ? AND p.account_id = ?
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$characterId, $accountId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $empire = 0;
        try {
            $idx = $pdo->prepare('SELECT empire FROM player_index WHERE id = ? LIMIT 1');
            $idx->execute([$accountId]);
            $empire = (int) ($idx->fetchColumn() ?: 0);
        } catch (\Throwable) {
            $empire = 0;
        }

        $job = (int) $row['job'];
        $character = [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'job' => $job,
            'job_label' => self::jobLabel($job),
            'job_icon' => self::jobIcon($job),
            'job_gif' => self::jobGif($job),
            'level' => (int) $row['level'],
            'level_step' => (int) $row['level_step'],
            'exp' => (int) $row['exp'],
            'gold' => (int) $row['gold'],
            'playtime' => (int) $row['playtime'],
            'playtime_label' => self::formatPlaytime((int) $row['playtime']),
            'last_play' => (string) $row['last_play'],
            'guild' => $row['guild_name'] !== null && $row['guild_name'] !== '' ? (string) $row['guild_name'] : null,
            'empire' => $empire,
            'empire_label' => self::empireLabel($empire),
            'max_level' => self::resolveMaxLevel($serverKey),
        ];
        $enriched = MarriageService::attachSpouses([$character], $serverKey);

        return $enriched[0] ?? $character;
    }

    private static function resolveMaxLevel(?string $serverKey): int
    {
        $configured = Config::get('game_limits.max_level');
        $base = ($configured !== null && (int) $configured > 0) ? (int) $configured : 99;

        try {
            $pdo = Database::player($serverKey);
            $dbMax = (int) $pdo->query('SELECT MAX(`level`) FROM player')->fetchColumn();
            if ($dbMax > $base) {
                return $dbMax;
            }
        } catch (\Throwable) {
            // ignore
        }

        return $base;
    }

    private static function loadAccount(int $accountId, ?string $serverKey): array
    {
        $pdo = Database::account($serverKey);
        $stmt = $pdo->prepare(
            'SELECT id, login, email, status, create_time, cash, mileage, total_cash, ip, WebPermission
             FROM account WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$accountId]);
        $row = $stmt->fetch() ?: [];

        $st = strtoupper((string) ($row['status'] ?? ''));
        $isBanned = $st === 'BLOCK';

        return [
            'id' => (int) ($row['id'] ?? $accountId),
            'login' => (string) ($row['login'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'status' => $st,
            'status_label' => $isBanned ? 'Banlı' : ($st === 'OK' ? 'Aktif' : ($st !== '' ? $st : '—')),
            'status_badge' => $isBanned ? 'banned' : 'online',
            'is_banned' => $isBanned,
            'create_time' => (string) ($row['create_time'] ?? ''),
            'cash' => (int) ($row['cash'] ?? 0),
            'mileage' => (int) ($row['mileage'] ?? 0),
            'total_cash' => (int) ($row['total_cash'] ?? 0),
            'ip' => (string) ($row['ip'] ?? ''),
            'web_permission' => AuthService::normalizePermission($row['WebPermission'] ?? null),
        ];
    }

    /** @return list<array> */
    private static function loadCharacters(int $accountId, ?string $serverKey): array
    {
        $pdo = Database::player($serverKey);

        $index = null;
        try {
            $idxStmt = $pdo->prepare('SELECT pid1, pid2, pid3, pid4, empire FROM player_index WHERE id = ? LIMIT 1');
            $idxStmt->execute([$accountId]);
            $index = $idxStmt->fetch() ?: null;
        } catch (\Throwable) {
            $index = null;
        }

        $empire = (int) ($index['empire'] ?? 0);

        $sql = 'SELECT p.id, p.name, p.job, p.level, p.level_step, p.exp, p.gold, p.playtime, p.last_play,
                       g.name AS guild_name
                FROM player p
                LEFT JOIN guild_member gm ON gm.pid = p.id
                LEFT JOIN guild g ON g.id = gm.guild_id
                WHERE p.account_id = ?
                ORDER BY p.level DESC, p.id ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$accountId]);
        $rows = $stmt->fetchAll();

        // player_index sırasına göre öncelik (pid1 önce)
        $order = [];
        if ($index) {
            foreach (['pid1', 'pid2', 'pid3', 'pid4'] as $slot) {
                $pid = (int) ($index[$slot] ?? 0);
                if ($pid > 0) {
                    $order[$pid] = count($order);
                }
            }
        }

        $chars = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $job = (int) $row['job'];
            $chars[] = [
                'id' => $id,
                'name' => (string) $row['name'],
                'job' => $job,
                'job_label' => self::jobLabel($job),
                'job_icon' => self::jobIcon($job),
                'job_gif' => self::jobGif($job),
                'level' => (int) $row['level'],
                'level_step' => (int) $row['level_step'],
                'exp' => (int) $row['exp'],
                'gold' => (int) $row['gold'],
                'playtime' => (int) $row['playtime'],
                'playtime_label' => self::formatPlaytime((int) $row['playtime']),
                'last_play' => (string) $row['last_play'],
                'guild' => $row['guild_name'] !== null && $row['guild_name'] !== '' ? (string) $row['guild_name'] : null,
                'empire' => $empire,
                'empire_label' => self::empireLabel($empire),
                'slot_order' => $order[$id] ?? 99,
            ];
        }

        usort($chars, static function (array $a, array $b): int {
            if ($a['slot_order'] !== $b['slot_order']) {
                return $a['slot_order'] <=> $b['slot_order'];
            }
            return $b['level'] <=> $a['level'];
        });

        return MarriageService::attachSpouses($chars, $serverKey);
    }
}
