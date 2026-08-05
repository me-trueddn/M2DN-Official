<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

/**
 * Oyun log DB (log.*) okuma — seçilebilir tablo, son 10 kayıt.
 */
final class AdminGameLogService
{
    public const LIMIT = 10;

    /** @var array<string, string> Görünen etiketler */
    public const TABLE_LABELS = [
        'bootlog' => 'Boot (kanal açılış)',
        'change_name' => 'İsim değişimi',
        'command_log' => 'GM komutları',
        'cube' => 'Cube / craft',
        'dragon_slay_log' => 'Ejderha / boss',
        'fish_log' => 'Balıkçılık',
        'goldlog' => 'Yang (goldlog)',
        'hack_crc_log' => 'Hack CRC',
        'hack_log' => 'Hack log',
        'hackshield_log' => 'HackShield',
        'levellog' => 'Level atlama',
        'log' => 'Genel log',
        'loginlog' => 'Giriş / çıkış',
        'money_log' => 'Yang (money_log)',
        'pcbang_loginlog' => 'PCBang giriş',
        'playercount' => 'Oyuncu sayısı',
        'quest_reward_log' => 'Görev ödülü',
        'refinelog' => 'Refine / + basma',
        'shout_log' => 'Bağırma',
        'speed_hack' => 'Speed hack',
    ];

    /** @return list<array{key:string,label:string}> */
    public static function availableTables(?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::log($serverKey);
            $existing = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        } catch (\Throwable) {
            return [];
        }

        $out = [];
        foreach ($existing as $table) {
            $key = (string) $table;
            if ($key === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
                continue;
            }
            $out[] = [
                'key' => $key,
                'label' => self::TABLE_LABELS[$key] ?? $key,
            ];
        }
        usort($out, static fn(array $a, array $b): int => strcasecmp($a['label'], $b['label']));
        return $out;
    }

    /**
     * @return array{
     *   table: string,
     *   label: string,
     *   columns: list<string>,
     *   rows: list<array<string, mixed>>,
     *   error: string|null
     * }
     */
    public static function latest(string $table, int $limit = self::LIMIT, ?string $serverKey = null): array
    {
        $table = trim($table);
        $limit = max(1, min(self::LIMIT, $limit));
        $empty = [
            'table' => $table,
            'label' => self::TABLE_LABELS[$table] ?? $table,
            'columns' => [],
            'rows' => [],
            'error' => null,
        ];

        if ($table === '' || !preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            $empty['error'] = 'Geçersiz log tablosu.';
            return $empty;
        }

        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::log($serverKey);
            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN) ?: [];
            if (!in_array($table, $tables, true)) {
                $empty['error'] = 'Tablo bulunamadı: ' . $table;
                return $empty;
            }

            $cols = [];
            foreach ($pdo->query("DESCRIBE `{$table}`") as $col) {
                $cols[] = (string) ($col['Field'] ?? '');
            }
            $cols = array_values(array_filter($cols, static fn(string $c): bool => $c !== ''));
            if ($cols === []) {
                $empty['error'] = 'Tablo kolonları okunamadı.';
                return $empty;
            }

            $orderCol = self::pickOrderColumn($cols);
            $orderSql = $orderCol !== null ? "ORDER BY `{$orderCol}` DESC" : '';
            $stmt = $pdo->query("SELECT * FROM `{$table}` {$orderSql} LIMIT {$limit}");
            $raw = $stmt ? ($stmt->fetchAll() ?: []) : [];
            $rows = [];
            foreach ($raw as $row) {
                $mapped = [];
                foreach ($cols as $c) {
                    $val = $row[$c] ?? null;
                    if (is_resource($val)) {
                        $val = '[blob]';
                    } elseif (is_array($val) || is_object($val)) {
                        $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                    } elseif ($val === null) {
                        $val = '';
                    } else {
                        $val = (string) $val;
                    }
                    if (function_exists('mb_strlen') && mb_strlen($val) > 200) {
                        $val = mb_substr($val, 0, 200) . '…';
                    } elseif (strlen($val) > 200) {
                        $val = substr($val, 0, 200) . '…';
                    }
                    $mapped[$c] = $val;
                }
                $rows[] = $mapped;
            }

            return [
                'table' => $table,
                'label' => self::TABLE_LABELS[$table] ?? $table,
                'columns' => $cols,
                'rows' => $rows,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            $empty['error'] = 'Log okunamadı.';
            return $empty;
        }
    }

    /** @param list<string> $cols */
    private static function pickOrderColumn(array $cols): ?string
    {
        $preferred = ['time', 'date', 'timestamp', 'created_at', 'ctime', 'id', 'log_id'];
        $lowerMap = [];
        foreach ($cols as $c) {
            $lowerMap[strtolower($c)] = $c;
        }
        foreach ($preferred as $want) {
            if (isset($lowerMap[$want])) {
                return $lowerMap[$want];
            }
        }
        return null;
    }
}
