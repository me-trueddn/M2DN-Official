<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

/**
 * Lonca savaşları — panel & admin ortak (salt okunur).
 *
 * Aktif: player.guild_war
 * Geçmiş / rezervasyon / ganimet: player.guild_war_reservation
 * Bahisler: player.guild_war_bet
 * Lonca skorları: guild.win / draw / loss
 */
final class GuildWarService
{
    /** @return list<array> */
    public static function listActive(?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $stmt = $pdo->query(
                "SELECT gw.id_from, gw.id_to,
                        g1.name AS from_name, g1.level AS from_level, g1.ladder_point AS from_ladder,
                        g1.win AS from_win, g1.draw AS from_draw, g1.loss AS from_loss,
                        g2.name AS to_name, g2.level AS to_level, g2.ladder_point AS to_ladder,
                        g2.win AS to_win, g2.draw AS to_draw, g2.loss AS to_loss,
                        p1.name AS from_master_name,
                        p2.name AS to_master_name,
                        r.id AS reservation_id, r.type AS war_type, r.warprice, r.initscore,
                        r.started, r.winner, r.power1, r.power2, r.handicap,
                        r.result1, r.result2, r.bet_from, r.bet_to, r.time AS reserved_at
                 FROM guild_war gw
                 LEFT JOIN guild g1 ON g1.id = gw.id_from
                 LEFT JOIN guild g2 ON g2.id = gw.id_to
                 LEFT JOIN player p1 ON p1.id = g1.master
                 LEFT JOIN player p2 ON p2.id = g2.master
                 LEFT JOIN guild_war_reservation r
                   ON r.started = 1
                  AND (
                        (r.guild1 = gw.id_from AND r.guild2 = gw.id_to)
                     OR (r.guild1 = gw.id_to AND r.guild2 = gw.id_from)
                  )
                 ORDER BY gw.id_from ASC, gw.id_to ASC"
            );
            $rows = $stmt ? ($stmt->fetchAll() ?: []) : [];
        } catch (\Throwable) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $mapped = self::mapWarRow($row, true);
            $rid = (int) ($row['reservation_id'] ?? 0);
            $mapped['bets'] = $rid > 0 ? self::betsForWar($rid, $serverKey) : [];
            $mapped['bet_total'] = (int) ($row['bet_from'] ?? 0) + (int) ($row['bet_to'] ?? 0);
            if ($mapped['bet_total'] <= 0 && $mapped['bets'] !== []) {
                $mapped['bet_total'] = array_sum(array_column($mapped['bets'], 'gold'));
            }
            $out[] = $mapped;
        }
        return $out;
    }

    /**
     * Tamamlanmış / geçmiş savaşlar (rezervasyon kayıtları).
     * @return list<array>
     */
    public static function listHistory(int $limit = 40, ?string $serverKey = null): array
    {
        $limit = max(1, min(100, $limit));
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            // Aktif savaş çiftlerini hariç tut (hâlâ guild_war'da olanlar)
            $stmt = $pdo->query(
                "SELECT r.id AS reservation_id, r.guild1 AS id_from, r.guild2 AS id_to,
                        r.type AS war_type, r.warprice, r.initscore, r.started, r.winner,
                        r.power1, r.power2, r.handicap, r.result1, r.result2,
                        r.bet_from, r.bet_to, r.time AS reserved_at,
                        g1.name AS from_name, g1.level AS from_level, g1.ladder_point AS from_ladder,
                        g1.win AS from_win, g1.draw AS from_draw, g1.loss AS from_loss,
                        g2.name AS to_name, g2.level AS to_level, g2.ladder_point AS to_ladder,
                        g2.win AS to_win, g2.draw AS to_draw, g2.loss AS to_loss,
                        p1.name AS from_master_name, p2.name AS to_master_name,
                        gw_win.name AS winner_name
                 FROM guild_war_reservation r
                 LEFT JOIN guild g1 ON g1.id = r.guild1
                 LEFT JOIN guild g2 ON g2.id = r.guild2
                 LEFT JOIN player p1 ON p1.id = g1.master
                 LEFT JOIN player p2 ON p2.id = g2.master
                 LEFT JOIN guild gw_win ON gw_win.id = r.winner
                 WHERE NOT EXISTS (
                     SELECT 1 FROM guild_war gw
                     WHERE (gw.id_from = r.guild1 AND gw.id_to = r.guild2)
                        OR (gw.id_from = r.guild2 AND gw.id_to = r.guild1)
                 )
                 ORDER BY r.time DESC, r.id DESC
                 LIMIT {$limit}"
            );
            $rows = $stmt ? ($stmt->fetchAll() ?: []) : [];
        } catch (\Throwable) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $mapped = self::mapWarRow($row, false);
            $rid = (int) ($row['reservation_id'] ?? 0);
            $mapped['bets'] = $rid > 0 ? self::betsForWar($rid, $serverKey) : [];
            $mapped['bet_total'] = (int) ($row['bet_from'] ?? 0) + (int) ($row['bet_to'] ?? 0);
            if ($mapped['bet_total'] <= 0 && $mapped['bets'] !== []) {
                $mapped['bet_total'] = array_sum(array_column($mapped['bets'], 'gold'));
            }
            $mapped['winner_id'] = (int) ($row['winner'] ?? 0);
            $mapped['winner_name'] = (string) ($row['winner_name'] ?? '');
            if ($mapped['winner_name'] === '' && $mapped['winner_id'] > 0) {
                $mapped['winner_name'] = '#' . $mapped['winner_id'];
            }
            $mapped['status_label'] = $mapped['winner_id'] > 0 ? 'Tamamlandı' : ((int) ($row['started'] ?? 0) === 1 ? 'Başladı' : 'Planlandı');
            $out[] = $mapped;
        }
        return $out;
    }

    /**
     * Lonca savaş istatistikleri (halka açık).
     * @return array{wars:int, win:int, draw:int, loss:int, win_rate:float, record_label:string}
     */
    public static function guildStats(int $guildId, ?string $serverKey = null): array
    {
        $empty = ['wars' => 0, 'win' => 0, 'draw' => 0, 'loss' => 0, 'win_rate' => 0.0, 'record_label' => '0 / 0 / 0'];
        if ($guildId <= 0) {
            return $empty;
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $stmt = $pdo->prepare('SELECT win, draw, loss FROM guild WHERE id = ? LIMIT 1');
            $stmt->execute([$guildId]);
            $row = $stmt->fetch();
            if (!$row) {
                return $empty;
            }
            $win = (int) ($row['win'] ?? 0);
            $draw = (int) ($row['draw'] ?? 0);
            $loss = (int) ($row['loss'] ?? 0);
            $wars = $win + $draw + $loss;
            $rate = $wars > 0 ? round(($win / $wars) * 100, 1) : 0.0;
            return [
                'wars' => $wars,
                'win' => $win,
                'draw' => $draw,
                'loss' => $loss,
                'win_rate' => $rate,
                'record_label' => $win . ' / ' . $draw . ' / ' . $loss,
            ];
        } catch (\Throwable) {
            return $empty;
        }
    }

    /**
     * Lonca kartı (panel/admin ortak detay özeti).
     * @return array|null
     */
    public static function publicGuildCard(int $guildId, ?string $serverKey = null): ?array
    {
        if ($guildId <= 0) {
            return null;
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $stmt = $pdo->prepare(
                "SELECT g.id, g.name, g.level, g.ladder_point, g.gold, g.win, g.draw, g.loss, g.master,
                        p.name AS master_name,
                        (SELECT COUNT(*) FROM guild_member gm WHERE gm.guild_id = g.id) AS member_count
                 FROM guild g
                 LEFT JOIN player p ON p.id = g.master
                 WHERE g.id = ?
                 LIMIT 1"
            );
            $stmt->execute([$guildId]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        $stats = self::guildStats($guildId, $serverKey);
        $recent = self::historyForGuild($guildId, 15, $serverKey);

        return [
            'id' => (int) $row['id'],
            'name' => (string) ($row['name'] ?? ''),
            'level' => (int) ($row['level'] ?? 0),
            'ladder_point' => (int) ($row['ladder_point'] ?? 0),
            'gold' => (int) ($row['gold'] ?? 0),
            'master_name' => (string) ($row['master_name'] ?? '') !== '' ? (string) $row['master_name'] : '—',
            'member_count' => (int) ($row['member_count'] ?? 0),
            'war_stats' => $stats,
            'recent_wars' => $recent,
        ];
    }

    /**
     * @return list<array>
     */
    public static function historyForGuild(int $guildId, int $limit = 15, ?string $serverKey = null): array
    {
        $limit = max(1, min(50, $limit));
        if ($guildId <= 0) {
            return [];
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $stmt = $pdo->prepare(
                "SELECT r.id AS reservation_id, r.guild1 AS id_from, r.guild2 AS id_to,
                        r.type AS war_type, r.warprice, r.initscore, r.started, r.winner,
                        r.power1, r.power2, r.handicap, r.result1, r.result2,
                        r.bet_from, r.bet_to, r.time AS reserved_at,
                        g1.name AS from_name, g1.level AS from_level, g1.ladder_point AS from_ladder,
                        g2.name AS to_name, g2.level AS to_level, g2.ladder_point AS to_ladder,
                        p1.name AS from_master_name, p2.name AS to_master_name,
                        gw_win.name AS winner_name
                 FROM guild_war_reservation r
                 LEFT JOIN guild g1 ON g1.id = r.guild1
                 LEFT JOIN guild g2 ON g2.id = r.guild2
                 LEFT JOIN player p1 ON p1.id = g1.master
                 LEFT JOIN player p2 ON p2.id = g2.master
                 LEFT JOIN guild gw_win ON gw_win.id = r.winner
                 WHERE r.guild1 = ? OR r.guild2 = ?
                 ORDER BY r.time DESC, r.id DESC
                 LIMIT {$limit}"
            );
            $stmt->execute([$guildId, $guildId]);
            $rows = $stmt->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            $mapped = self::mapWarRow($row, false);
            $mapped['winner_id'] = (int) ($row['winner'] ?? 0);
            $mapped['winner_name'] = (string) ($row['winner_name'] ?? '');
            $mapped['is_win'] = $mapped['winner_id'] === $guildId;
            $mapped['is_loss'] = $mapped['winner_id'] > 0 && $mapped['winner_id'] !== $guildId;
            $mapped['status_label'] = $mapped['winner_id'] > 0
                ? ($mapped['is_win'] ? 'Galibiyet' : 'Mağlubiyet')
                : ((int) ($row['started'] ?? 0) === 1 ? 'Devam / sonuçsuz' : 'Planlandı');
            $out[] = $mapped;
        }
        return $out;
    }

    /**
     * @return list<array{login:string, gold:int, guild:int, war_id:int}>
     */
    public static function betsForWar(int $warId, ?string $serverKey = null): array
    {
        if ($warId <= 0) {
            return [];
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $stmt = $pdo->prepare(
                'SELECT login, gold, guild, war_id FROM guild_war_bet WHERE war_id = ? ORDER BY gold DESC LIMIT 100'
            );
            $stmt->execute([$warId]);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $out[] = [
                    'login' => (string) ($row['login'] ?? ''),
                    'gold' => (int) ($row['gold'] ?? 0),
                    'guild' => (int) ($row['guild'] ?? 0),
                    'war_id' => (int) ($row['war_id'] ?? 0),
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return list<array> Lonca sıralaması (savaş kaydı) */
    public static function leaderboard(int $limit = 30, ?string $serverKey = null): array
    {
        $limit = max(1, min(100, $limit));
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $stmt = $pdo->query(
                "SELECT g.id, g.name, g.level, g.ladder_point, g.win, g.draw, g.loss,
                        p.name AS master_name,
                        (g.win + g.draw + g.loss) AS wars
                 FROM guild g
                 LEFT JOIN player p ON p.id = g.master
                 WHERE (g.win + g.draw + g.loss) > 0 OR g.ladder_point > 0
                 ORDER BY g.win DESC, g.ladder_point DESC, g.loss ASC, g.id ASC
                 LIMIT {$limit}"
            );
            $rows = $stmt ? ($stmt->fetchAll() ?: []) : [];
        } catch (\Throwable) {
            return [];
        }

        $out = [];
        $rank = 1;
        foreach ($rows as $row) {
            $win = (int) ($row['win'] ?? 0);
            $draw = (int) ($row['draw'] ?? 0);
            $loss = (int) ($row['loss'] ?? 0);
            $wars = (int) ($row['wars'] ?? ($win + $draw + $loss));
            $out[] = [
                'rank' => $rank++,
                'id' => (int) ($row['id'] ?? 0),
                'name' => (string) ($row['name'] ?? ''),
                'level' => (int) ($row['level'] ?? 0),
                'ladder_point' => (int) ($row['ladder_point'] ?? 0),
                'master_name' => (string) ($row['master_name'] ?? '') !== '' ? (string) $row['master_name'] : '—',
                'wars' => $wars,
                'win' => $win,
                'draw' => $draw,
                'loss' => $loss,
                'win_rate' => $wars > 0 ? round(($win / $wars) * 100, 1) : 0.0,
                'record_label' => $win . ' / ' . $draw . ' / ' . $loss,
            ];
        }
        return $out;
    }

    public static function typeLabel(?int $type): string
    {
        return match ($type) {
            0 => 'Alan savaşı',
            1 => 'Kule savaşı',
            2 => 'Bayrak savaşı',
            null => 'Savaş',
            default => 'Tür #' . $type,
        };
    }

    /** @param array $row */
    private static function mapWarRow(array $row, bool $isLive): array
    {
        $fromId = (int) ($row['id_from'] ?? 0);
        $toId = (int) ($row['id_to'] ?? 0);
        $type = array_key_exists('war_type', $row) && $row['war_type'] !== null ? (int) $row['war_type'] : null;
        $warprice = (int) ($row['warprice'] ?? 0);

        return [
            'live' => $isLive,
            'from_id' => $fromId,
            'to_id' => $toId,
            'from_name' => (string) ($row['from_name'] ?? '') !== '' ? (string) $row['from_name'] : ('#' . $fromId),
            'to_name' => (string) ($row['to_name'] ?? '') !== '' ? (string) $row['to_name'] : ('#' . $toId),
            'from_level' => (int) ($row['from_level'] ?? 0),
            'to_level' => (int) ($row['to_level'] ?? 0),
            'from_ladder' => (int) ($row['from_ladder'] ?? 0),
            'to_ladder' => (int) ($row['to_ladder'] ?? 0),
            'from_win' => (int) ($row['from_win'] ?? 0),
            'from_draw' => (int) ($row['from_draw'] ?? 0),
            'from_loss' => (int) ($row['from_loss'] ?? 0),
            'to_win' => (int) ($row['to_win'] ?? 0),
            'to_draw' => (int) ($row['to_draw'] ?? 0),
            'to_loss' => (int) ($row['to_loss'] ?? 0),
            'from_master_name' => (string) ($row['from_master_name'] ?? '') !== '' ? (string) $row['from_master_name'] : '—',
            'to_master_name' => (string) ($row['to_master_name'] ?? '') !== '' ? (string) $row['to_master_name'] : '—',
            'reservation_id' => (int) ($row['reservation_id'] ?? 0),
            'war_type' => $type,
            'war_type_label' => self::typeLabel($type),
            'warprice' => $warprice,
            'warprice_label' => $warprice > 0 ? number_format($warprice, 0, ',', '.') : '—',
            'initscore' => (int) ($row['initscore'] ?? 0),
            'result1' => isset($row['result1']) ? (int) $row['result1'] : null,
            'result2' => isset($row['result2']) ? (int) $row['result2'] : null,
            'power1' => (int) ($row['power1'] ?? 0),
            'power2' => (int) ($row['power2'] ?? 0),
            'handicap' => (int) ($row['handicap'] ?? 0),
            'score_label' => self::scoreLabel($row),
            'reserved_at' => (string) ($row['reserved_at'] ?? ''),
            'reserved_label' => self::timeLabel((string) ($row['reserved_at'] ?? '')),
            'status_label' => $isLive ? 'Canlı' : 'Kayıt',
        ];
    }

    /** @param array $row */
    private static function scoreLabel(array $row): string
    {
        if (!array_key_exists('result1', $row) && !array_key_exists('result2', $row)) {
            return '—';
        }
        if ($row['result1'] === null && $row['result2'] === null) {
            return '—';
        }
        return (int) ($row['result1'] ?? 0) . ' : ' . (int) ($row['result2'] ?? 0);
    }

    private static function timeLabel(string $time): string
    {
        if ($time === '' || $time === '0000-00-00 00:00:00') {
            return '—';
        }
        $ts = strtotime($time);
        return $ts ? date('d.m.Y H:i', $ts) : '—';
    }
}
