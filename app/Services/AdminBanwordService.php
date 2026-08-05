<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

/**
 * Yasaklı kelimeler — player.banword (word VARBINARY(24) PK)
 */
final class AdminBanwordService
{
    public const MAX_BYTES = 24;
    public const PER_PAGE_OPTIONS = [10, 20, 30, 50, 100];

    /** @var list<string> */
    public const DEFAULT_WORDS = [
        'salak',
        'manyak',
        'aptal',
        'gerizekali',
        'mal',
        'budala',
        'dangalak',
        'serefsiz',
        'kahpe',
        'orospu',
        'amk',
        'aq',
        'siktir',
        'göt',
        'amcik',
    ];

    /**
     * @return array{
     *   words: list<array{word:string}>,
     *   total: int,
     *   page: int,
     *   pages: int,
     *   per_page: int,
     *   q: string,
     *   per_page_options: list<int>
     * }
     */
    public static function list(string $q = '', int $page = 1, int $perPage = 20, ?string $serverKey = null): array
    {
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        $q = trim($q);
        $page = max(1, $page);
        $perPage = self::normalizePerPage($perPage);
        $empty = [
            'words' => [],
            'total' => 0,
            'page' => 1,
            'pages' => 1,
            'per_page' => $perPage,
            'q' => $q,
            'per_page_options' => self::PER_PAGE_OPTIONS,
        ];

        try {
            $pdo = Database::player($serverKey);
            self::ensureDefaults($pdo);
        } catch (\Throwable) {
            return $empty;
        }

        $where = ['1=1'];
        $params = [];
        if ($q !== '') {
            $where[] = 'CONVERT(word USING utf8mb4) LIKE ?';
            $params[] = '%' . $q . '%';
        }
        $whereSql = implode(' AND ', $where);

        try {
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM banword WHERE {$whereSql}");
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = max(1, (int) ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;

            $stmt = $pdo->prepare(
                "SELECT CONVERT(word USING utf8mb4) AS word
                 FROM banword
                 WHERE {$whereSql}
                 ORDER BY CONVERT(word USING utf8mb4) ASC
                 LIMIT {$perPage} OFFSET {$offset}"
            );
            $stmt->execute($params);
            $words = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $w = trim((string) ($row['word'] ?? ''));
                if ($w !== '') {
                    $words[] = ['word' => $w];
                }
            }

            return [
                'words' => $words,
                'total' => $total,
                'page' => $page,
                'pages' => $pages,
                'per_page' => $perPage,
                'q' => $q,
                'per_page_options' => self::PER_PAGE_OPTIONS,
            ];
        } catch (\Throwable) {
            return $empty;
        }
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function add(string $word, ?string $serverKey = null): array
    {
        $word = self::normalizeWord($word);
        $err = self::validateWord($word);
        if ($err !== null) {
            return ['ok' => false, 'errors' => [$err]];
        }

        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $chk = $pdo->prepare('SELECT 1 FROM banword WHERE word = ? LIMIT 1');
            $chk->execute([$word]);
            if ($chk->fetch()) {
                return ['ok' => false, 'errors' => ['Bu kelime zaten listede.']];
            }
            $pdo->prepare('INSERT INTO banword (word) VALUES (?)')->execute([$word]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kelime eklenemedi.']];
        }
    }

    /**
     * @return array{ok:bool, errors:list<string>}
     */
    public static function delete(string $word, ?string $serverKey = null): array
    {
        $word = self::normalizeWord($word);
        if ($word === '') {
            return ['ok' => false, 'errors' => ['Geçersiz kelime.']];
        }
        $serverKey = $serverKey ?: (ServerManager::current()['key'] ?? null);
        try {
            $pdo = Database::player($serverKey);
            $stmt = $pdo->prepare('DELETE FROM banword WHERE word = ? LIMIT 1');
            $stmt->execute([$word]);
            if ($stmt->rowCount() < 1) {
                return ['ok' => false, 'errors' => ['Kelime bulunamadı.']];
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kelime silinemedi.']];
        }
    }

    public static function normalizePerPage(int $perPage): int
    {
        if (in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            return $perPage;
        }
        return 20;
    }

    /** @param \PDO $pdo */
    private static function ensureDefaults(\PDO $pdo): void
    {
        try {
            $count = (int) $pdo->query('SELECT COUNT(*) FROM banword')->fetchColumn();
            if ($count > 0) {
                return;
            }
            $ins = $pdo->prepare('INSERT IGNORE INTO banword (word) VALUES (?)');
            foreach (self::DEFAULT_WORDS as $w) {
                $norm = self::normalizeWord($w);
                if (self::validateWord($norm) === null) {
                    $ins->execute([$norm]);
                }
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    private static function normalizeWord(string $word): string
    {
        $word = trim($word);
        $word = preg_replace('/\s+/u', '', $word) ?? $word;
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($word, 'UTF-8');
        }
        return strtolower($word);
    }

    private static function validateWord(string $word): ?string
    {
        if ($word === '') {
            return 'Kelime boş olamaz.';
        }
        if (strlen($word) > self::MAX_BYTES) {
            return 'Kelime en fazla ' . self::MAX_BYTES . ' bayt olabilir (Türkçe karakterler daha fazla yer kaplar).';
        }
        if (!preg_match('/^[\p{L}\p{N}_\-]+$/u', $word)) {
            return 'Sadece harf, rakam, _ ve - kullanılabilir.';
        }
        return null;
    }
}
