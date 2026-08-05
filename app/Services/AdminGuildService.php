<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;
use PDO;

final class AdminGuildService
{
    public const PER_PAGE_OPTIONS = [10, 20, 30, 50, 100];
    public const MASTER_GRADE = 15;
    public const MAX_NAME_LEN = 12;

    /**
     * @return array{
     *   guilds: list<array>,
     *   total: int,
     *   page: int,
     *   per_page: int,
     *   pages: int,
     *   q: string,
     *   per_page_options: list<int>
     * }
     */
    public static function listGuilds(string $q = '', int $page = 1, int $perPage = 10, ?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        $q = trim($q);
        $page = max(1, $page);
        $perPage = self::normalizePerPage($perPage);

        $empty = [
            'guilds' => [],
            'total' => 0,
            'page' => 1,
            'per_page' => $perPage,
            'pages' => 1,
            'q' => $q,
            'per_page_options' => self::PER_PAGE_OPTIONS,
        ];

        try {
            $pdo = Database::player($serverKey);
        } catch (\Throwable) {
            return $empty;
        }

        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = 'g.name LIKE ?';
            $params[] = '%' . $q . '%';
        }
        $whereSql = implode(' AND ', $where);

        try {
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM guild g WHERE {$whereSql}");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = max(1, (int) ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;

            $stmt = $pdo->prepare(
                "SELECT g.id, g.name, g.master, g.level, g.exp, g.sp, g.skill_point,
                        g.win, g.draw, g.loss, g.ladder_point, g.gold,
                        (SELECT COUNT(*) FROM guild_member gm WHERE gm.guild_id = g.id) AS member_count,
                        p.name AS master_name, p.level AS master_level, p.job AS master_job,
                        p.account_id AS master_account_id,
                        gm.grade AS master_grade,
                        gg.name AS master_grade_name
                 FROM guild g
                 LEFT JOIN player p ON p.id = g.master
                 LEFT JOIN guild_member gm ON gm.guild_id = g.id AND gm.pid = g.master
                 LEFT JOIN guild_grade gg ON gg.guild_id = g.id AND gg.grade = gm.grade
                 WHERE {$whereSql}
                 ORDER BY g.ladder_point DESC, g.level DESC, g.id ASC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $rows = $stmt->fetchAll() ?: [];
        } catch (\Throwable) {
            return $empty;
        }

        $guilds = [];
        foreach ($rows as $row) {
            $guilds[] = self::mapListRow($row);
        }

        return [
            'guilds' => $guilds,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => $pages,
            'q' => $q,
            'per_page_options' => self::PER_PAGE_OPTIONS,
        ];
    }

    /**
     * @return array{guild:array, members:list<array>, comments:list<array>, grades:list<array>}|null
     */
    public static function guildDetail(int $guildId, ?string $serverKey = null): ?array
    {
        if ($guildId <= 0) {
            return null;
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $stmt = $pdo->prepare(
                "SELECT g.id, g.name, g.master, g.level, g.exp, g.sp, g.skill_point,
                        g.win, g.draw, g.loss, g.ladder_point, g.gold,
                        (SELECT COUNT(*) FROM guild_member gm WHERE gm.guild_id = g.id) AS member_count,
                        p.name AS master_name, p.level AS master_level, p.job AS master_job,
                        p.account_id AS master_account_id,
                        gm0.grade AS master_grade,
                        gg0.name AS master_grade_name
                 FROM guild g
                 LEFT JOIN player p ON p.id = g.master
                 LEFT JOIN guild_member gm0 ON gm0.guild_id = g.id AND gm0.pid = g.master
                 LEFT JOIN guild_grade gg0 ON gg0.guild_id = g.id AND gg0.grade = gm0.grade
                 WHERE g.id = ?
                 LIMIT 1"
            );
            $stmt->execute([$guildId]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }

            $membersStmt = $pdo->prepare(
                "SELECT gm.pid, gm.guild_id, gm.grade, gm.is_general, gm.offer,
                        p.name AS character_name, p.level, p.job, p.account_id,
                        gg.name AS grade_name
                 FROM guild_member gm
                 LEFT JOIN player p ON p.id = gm.pid
                 LEFT JOIN guild_grade gg ON gg.guild_id = gm.guild_id AND gg.grade = gm.grade
                 WHERE gm.guild_id = ?
                 ORDER BY gm.grade DESC, p.level DESC, p.name ASC"
            );
            $membersStmt->execute([$guildId]);
            $memberRows = $membersStmt->fetchAll() ?: [];

            $comments = [];
            try {
                $cStmt = $pdo->prepare(
                    'SELECT id, guild_id, name, notice, content, time
                     FROM guild_comment
                     WHERE guild_id = ?
                     ORDER BY time DESC, id DESC
                     LIMIT 200'
                );
                $cStmt->execute([$guildId]);
                foreach ($cStmt->fetchAll() ?: [] as $c) {
                    $ts = strtotime((string) ($c['time'] ?? ''));
                    $comments[] = [
                        'id' => (int) ($c['id'] ?? 0),
                        'name' => (string) ($c['name'] ?? ''),
                        'notice' => (int) ($c['notice'] ?? 0) === 1,
                        'content' => (string) ($c['content'] ?? ''),
                        'time' => (string) ($c['time'] ?? ''),
                        'time_label' => $ts ? date('d.m.Y H:i', $ts) : '—',
                    ];
                }
            } catch (\Throwable) {
                $comments = [];
            }

            $grades = [];
            try {
                $gStmt = $pdo->prepare(
                    'SELECT guild_id, grade, name, auth
                     FROM guild_grade
                     WHERE guild_id = ?
                     ORDER BY grade DESC'
                );
                $gStmt->execute([$guildId]);
                foreach ($gStmt->fetchAll() ?: [] as $gr) {
                    $authRaw = (string) ($gr['auth'] ?? '');
                    $authList = $authRaw !== '' ? array_values(array_filter(array_map('trim', explode(',', $authRaw)))) : [];
                    $grades[] = [
                        'grade' => (int) ($gr['grade'] ?? 0),
                        'name' => (string) ($gr['name'] ?? ''),
                        'auth' => $authRaw,
                        'auth_list' => $authList,
                        'grade_label' => self::gradeLabel((int) ($gr['grade'] ?? 0)),
                    ];
                }
            } catch (\Throwable) {
                $grades = [];
            }
        } catch (\Throwable) {
            return null;
        }

        $members = [];
        foreach ($memberRows as $m) {
            $job = (int) ($m['job'] ?? 0);
            $grade = (int) ($m['grade'] ?? 0);
            $pid = (int) ($m['pid'] ?? 0);
            $customGrade = trim((string) ($m['grade_name'] ?? ''));
            $members[] = [
                'pid' => $pid,
                'character_name' => (string) ($m['character_name'] ?? '') !== '' ? (string) $m['character_name'] : ('#' . $pid),
                'level' => (int) ($m['level'] ?? 0),
                'job' => $job,
                'job_label' => PlayerService::jobLabel($job),
                'account_id' => (int) ($m['account_id'] ?? 0),
                'grade' => $grade,
                'grade_name' => $customGrade,
                'grade_label' => $customGrade !== '' ? $customGrade : self::gradeLabel($grade),
                'is_general' => (int) ($m['is_general'] ?? 0) === 1,
                'offer' => (int) ($m['offer'] ?? 0),
                'is_master' => $pid === (int) ($row['master'] ?? 0),
            ];
        }

        $warStats = GuildWarService::guildStats($guildId, $serverKey);

        return [
            'guild' => self::mapListRow($row) + [
                'wars' => $warStats['wars'],
                'win_rate' => $warStats['win_rate'],
            ],
            'members' => $members,
            'comments' => $comments,
            'grades' => $grades,
            'war_stats' => $warStats,
            'recent_wars' => GuildWarService::historyForGuild($guildId, 20, $serverKey),
        ];
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function rename(int $guildId, string $newName, ?string $serverKey = null): array
    {
        $newName = trim($newName);
        if ($guildId <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz lonca.']];
        }
        if ($newName === '' || mb_strlen($newName) > self::MAX_NAME_LEN) {
            return ['ok' => false, 'errors' => ['Lonca adı 1–' . self::MAX_NAME_LEN . ' karakter olmalı.']];
        }
        if (!preg_match('/^[\p{L}\p{N}_\-\s]+$/u', $newName)) {
            return ['ok' => false, 'errors' => ['Lonca adında geçersiz karakter var.']];
        }

        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $cur = $pdo->prepare('SELECT id, name FROM guild WHERE id = ? LIMIT 1');
            $cur->execute([$guildId]);
            $guild = $cur->fetch();
            if (!$guild) {
                return ['ok' => false, 'errors' => ['Lonca bulunamadı.']];
            }
            if ((string) $guild['name'] === $newName) {
                return ['ok' => true, 'errors' => []];
            }

            $dup = $pdo->prepare('SELECT id FROM guild WHERE name = ? AND id <> ? LIMIT 1');
            $dup->execute([$newName, $guildId]);
            if ($dup->fetch()) {
                return ['ok' => false, 'errors' => ['Bu lonca adı zaten kullanılıyor. Farklı bir ad seçin.']];
            }

            $pdo->prepare('UPDATE guild SET name = ? WHERE id = ?')->execute([$newName, $guildId]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Lonca adı güncellenemedi.']];
        }
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function changeMaster(int $guildId, int $newMasterPid, ?string $serverKey = null): array
    {
        if ($guildId <= 0 || $newMasterPid <= 0) {
            return ['ok' => false, 'errors' => ['Geçersiz lonca veya karakter.']];
        }

        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $pdo->beginTransaction();

            $gStmt = $pdo->prepare('SELECT id, name, master FROM guild WHERE id = ? LIMIT 1 FOR UPDATE');
            $gStmt->execute([$guildId]);
            $guild = $gStmt->fetch();
            if (!$guild) {
                $pdo->rollBack();
                return ['ok' => false, 'errors' => ['Lonca bulunamadı.']];
            }

            $oldMaster = (int) ($guild['master'] ?? 0);
            if ($oldMaster === $newMasterPid) {
                $pdo->commit();
                return ['ok' => true, 'errors' => []];
            }

            $pStmt = $pdo->prepare('SELECT id, name FROM player WHERE id = ? LIMIT 1');
            $pStmt->execute([$newMasterPid]);
            $player = $pStmt->fetch();
            if (!$player) {
                $pdo->rollBack();
                return ['ok' => false, 'errors' => ['Karakter bulunamadı.']];
            }

            $memStmt = $pdo->prepare('SELECT pid, grade FROM guild_member WHERE guild_id = ? AND pid = ? LIMIT 1');
            $memStmt->execute([$guildId, $newMasterPid]);
            if (!$memStmt->fetch()) {
                $pdo->rollBack();
                return ['ok' => false, 'errors' => ['Yeni usta bu loncanın üyesi olmalı.']];
            }

            $other = $pdo->prepare('SELECT id, name FROM guild WHERE master = ? AND id <> ? LIMIT 1');
            $other->execute([$newMasterPid, $guildId]);
            $otherGuild = $other->fetch();
            if ($otherGuild) {
                $pdo->rollBack();
                return ['ok' => false, 'errors' => [
                    'Bu karakter zaten başka bir loncanın ustası: ' . (string) ($otherGuild['name'] ?? '#' . $otherGuild['id']),
                ]];
            }

            $pdo->prepare('UPDATE guild SET master = ? WHERE id = ?')->execute([$newMasterPid, $guildId]);
            $pdo->prepare(
                'UPDATE guild_member SET grade = ?, is_general = 1 WHERE guild_id = ? AND pid = ?'
            )->execute([self::MASTER_GRADE, $guildId, $newMasterPid]);

            if ($oldMaster > 0) {
                $pdo->prepare(
                    'UPDATE guild_member SET grade = 1 WHERE guild_id = ? AND pid = ? AND grade = ?'
                )->execute([$guildId, $oldMaster, self::MASTER_GRADE]);
            }

            $pdo->commit();
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            try {
                if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            } catch (\Throwable) {
            }
            return ['ok' => false, 'errors' => ['Lonca ustası değiştirilemedi.']];
        }
    }

    public static function normalizePerPage(int $perPage): int
    {
        if (in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            return $perPage;
        }
        return 10;
    }

    public static function gradeLabel(int $grade): string
    {
        if ($grade >= self::MASTER_GRADE) {
            return 'Usta';
        }
        if ($grade >= 10) {
            return 'Yetkili';
        }
        if ($grade >= 5) {
            return 'Üye+';
        }
        return 'Üye';
    }

    /** @param array $row */
    private static function mapListRow(array $row): array
    {
        $job = (int) ($row['master_job'] ?? 0);
        $masterGrade = (int) ($row['master_grade'] ?? 0);
        $masterGradeName = trim((string) ($row['master_grade_name'] ?? ''));
        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'master' => (int) ($row['master'] ?? 0),
            'master_name' => (string) ($row['master_name'] ?? '') !== ''
                ? (string) $row['master_name']
                : ('#' . (int) ($row['master'] ?? 0)),
            'master_level' => (int) ($row['master_level'] ?? 0),
            'master_job' => $job,
            'master_job_label' => PlayerService::jobLabel($job),
            'master_account_id' => (int) ($row['master_account_id'] ?? 0),
            'master_grade' => $masterGrade,
            'master_grade_name' => $masterGradeName,
            'master_grade_label' => $masterGradeName !== ''
                ? $masterGradeName
                : ($masterGrade > 0 ? self::gradeLabel($masterGrade) : '—'),
            'level' => (int) ($row['level'] ?? 0),
            'exp' => (int) ($row['exp'] ?? 0),
            'sp' => (int) ($row['sp'] ?? 0),
            'skill_point' => (int) ($row['skill_point'] ?? 0),
            'win' => (int) ($row['win'] ?? 0),
            'draw' => (int) ($row['draw'] ?? 0),
            'loss' => (int) ($row['loss'] ?? 0),
            'ladder_point' => (int) ($row['ladder_point'] ?? 0),
            'gold' => (int) ($row['gold'] ?? 0),
            'member_count' => (int) ($row['member_count'] ?? 0),
            'wars' => ((int) ($row['win'] ?? 0)) + ((int) ($row['draw'] ?? 0)) + ((int) ($row['loss'] ?? 0)),
            'record_label' => ((int) ($row['win'] ?? 0)) . ' / ' . ((int) ($row['draw'] ?? 0)) . ' / ' . ((int) ($row['loss'] ?? 0)),
        ];
    }
}
