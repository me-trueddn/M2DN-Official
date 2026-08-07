<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

final class TicketService
{
    public const STATUS_NEW = 'new';
    public const STATUS_WAIT_PLAYER = 'waiting_player';
    public const STATUS_WAIT_STAFF = 'waiting_staff';
    public const STATUS_CLOSED = 'closed';

    /** @return list<array> */
    public static function categories(bool $onlyActive = true): array
    {
        try {
            $sql = 'SELECT id, name, description, is_active, sort_order FROM ticket_categories';
            if ($onlyActive) {
                $sql .= ' WHERE is_active = 1';
            }
            $sql .= ' ORDER BY sort_order ASC, id ASC';
            return Database::web()->query($sql)->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return list<array> */
    public static function statuses(bool $onlyActive = true): array
    {
        try {
            $sql = 'SELECT id, code, label, is_system, is_active, sort_order FROM ticket_statuses';
            if ($onlyActive) {
                $sql .= ' WHERE is_active = 1';
            }
            $sql .= ' ORDER BY sort_order ASC, id ASC';
            return Database::web()->query($sql)->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return list<array> */
    public static function allowedFileTypes(bool $onlyActive = true): array
    {
        try {
            $sql = 'SELECT id, extension, mime_type, is_active FROM ticket_file_types';
            if ($onlyActive) {
                $sql .= ' WHERE is_active = 1';
            }
            $sql .= ' ORDER BY extension ASC';
            return Database::web()->query($sql)->fetchAll() ?: [];
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveCategory(?int $id, string $name, string $description): array
    {
        $name = trim($name);
        $description = trim($description);
        if ($name === '') {
            return ['ok' => false, 'errors' => ['Kategori adı zorunlu.']];
        }
        try {
            $web = Database::web();
            if ($id && $id > 0) {
                $web->prepare('UPDATE ticket_categories SET name=?, description=?, updated_at=NOW() WHERE id=?')
                    ->execute([$name, $description, $id]);
            } else {
                $sort = (int) $web->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM ticket_categories')->fetchColumn();
                $web->prepare(
                    'INSERT INTO ticket_categories (name, description, sort_order, is_active, created_at, updated_at)
                     VALUES (?,?,?,1,NOW(),NOW())'
                )->execute([$name, $description, $sort]);
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kategori kaydedilemedi.']];
        }
    }

    public static function deleteCategory(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('DELETE FROM ticket_categories WHERE id=?')->execute([$id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveStatus(?int $id, string $code, string $label): array
    {
        $code = strtolower(trim($code));
        $label = trim($label);
        if ($code === '' || $label === '') {
            return ['ok' => false, 'errors' => ['Kod ve etiket zorunlu.']];
        }
        if (!preg_match('/^[a-z0-9_]{2,40}$/', $code)) {
            return ['ok' => false, 'errors' => ['Kod sadece küçük harf, rakam ve _ olabilir.']];
        }
        try {
            $web = Database::web();
            if ($id && $id > 0) {
                $stmt = $web->prepare('SELECT is_system FROM ticket_statuses WHERE id=? LIMIT 1');
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if (!$row) {
                    return ['ok' => false, 'errors' => ['Durum bulunamadı.']];
                }
                if ((int) ($row['is_system'] ?? 0) === 1) {
                    $web->prepare('UPDATE ticket_statuses SET label=?, updated_at=NOW() WHERE id=?')
                        ->execute([$label, $id]);
                } else {
                    $web->prepare('UPDATE ticket_statuses SET code=?, label=?, updated_at=NOW() WHERE id=?')
                        ->execute([$code, $label, $id]);
                }
            } else {
                $sort = (int) $web->query('SELECT COALESCE(MAX(sort_order),0)+1 FROM ticket_statuses')->fetchColumn();
                $web->prepare(
                    'INSERT INTO ticket_statuses (code, label, is_system, is_active, sort_order, created_at, updated_at)
                     VALUES (?,?,0,1,?,NOW(),NOW())'
                )->execute([$code, $label, $sort]);
            }
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Durum kaydedilemedi.']];
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function saveFileType(string $extension, string $mime): array
    {
        $extension = strtolower(ltrim(trim($extension), '.'));
        $mime = trim($mime);
        if ($extension === '' || $mime === '') {
            return ['ok' => false, 'errors' => ['Uzantı ve MIME zorunlu.']];
        }
        try {
            Database::web()->prepare(
                'INSERT INTO ticket_file_types (extension, mime_type, is_active, created_at)
                 VALUES (?,?,1,NOW())
                 ON DUPLICATE KEY UPDATE mime_type = VALUES(mime_type), is_active = 1'
            )->execute([$extension, $mime]);
            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Dosya türü kaydedilemedi.']];
        }
    }

    public static function deleteFileType(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('DELETE FROM ticket_file_types WHERE id=?')->execute([$id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function toggleFileType(int $id, bool $active): bool
    {
        if ($id <= 0) {
            return false;
        }
        try {
            Database::web()->prepare('UPDATE ticket_file_types SET is_active=? WHERE id=?')
                ->execute([$active ? 1 : 0, $id]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array{ok:bool, errors:list<string>, ticket_id?:int, code?:string} */
    public static function createTicket(
        int $accountId,
        string $login,
        int $categoryId,
        string $subject,
        string $body,
        ?array $uploadFile = null
    ): array {
        $subject = trim($subject);
        $body = trim($body);
        $errors = [];
        if ($categoryId <= 0) {
            $errors[] = 'Kategori seç.';
        }
        if ($subject === '' || mb_strlen($subject) > 200) {
            $errors[] = 'Konu zorunlu (max 200).';
        }
        if ($body === '') {
            $errors[] = 'Açıklama zorunlu.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $statusId = self::statusIdByCode(self::STATUS_NEW);
        if ($statusId === null) {
            return ['ok' => false, 'errors' => ['Ticket durumu yapılandırılmamış.']];
        }

        try {
            $web = Database::web();
            $web->beginTransaction();
            $code = self::generateUniqueCode();
            $serverKey = (string) (ServerManager::current()['key'] ?? 'main');
            $web->prepare(
                'INSERT INTO tickets
                  (public_code, account_id, account_login, server_key, category_id, status_id, subject, body, created_at, updated_at)
                 VALUES (?,?,?,?,?,?,?,?,NOW(),NOW())'
            )->execute([$code, $accountId, $login, $serverKey, $categoryId, $statusId, $subject, $body]);
            $ticketId = (int) $web->lastInsertId();

            $web->prepare(
                'INSERT INTO ticket_messages (ticket_id, account_id, account_login, is_staff, body, created_at)
                 VALUES (?,?,?,0,?,NOW())'
            )->execute([$ticketId, $accountId, $login, $body]);
            $messageId = (int) $web->lastInsertId();

            if ($uploadFile !== null && !empty($uploadFile['tmp_name'])) {
                $att = self::storeAttachment($ticketId, $messageId, $uploadFile);
                if (!$att['ok']) {
                    $web->rollBack();
                    return ['ok' => false, 'errors' => $att['errors']];
                }
            }

            $web->commit();

            $linkAdmin = '/admin?section=destekler&ticket=' . $ticketId;
            NotificationService::pushStaff(
                'ticket_created',
                'Yeni ticket: ' . $code,
                $subject . ' · ' . $login,
                $linkAdmin
            );
            foreach (NotificationService::staffAccountIds() as $staffId) {
                $contact = self::accountContact($staffId);
                if ($contact && $contact['email'] !== '') {
                    try {
                        MailService::sendTemplate('ticket_created', $contact['email'], $contact['login'], [
                            'login' => $login,
                            'code' => $code,
                            'subject' => $subject,
                            'link' => $linkAdmin,
                            'email' => $contact['email'],
                        ]);
                    } catch (\Throwable) {
                        // ticket oluştu; mail hatası yutulur
                    }
                }
            }

            return ['ok' => true, 'errors' => [], 'ticket_id' => $ticketId, 'code' => $code];
        } catch (\Throwable) {
            try {
                if (isset($web) && $web->inTransaction()) {
                    $web->rollBack();
                }
            } catch (\Throwable) {
            }
            return ['ok' => false, 'errors' => ['Ticket oluşturulamadı.']];
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function reply(
        int $ticketId,
        int $accountId,
        string $login,
        string $body,
        bool $isStaff,
        ?array $uploadFile = null
    ): array {
        $body = trim($body);
        if ($ticketId <= 0 || $body === '') {
            return ['ok' => false, 'errors' => ['Mesaj zorunlu.']];
        }

        $ticket = self::getTicket($ticketId);
        if ($ticket === null) {
            return ['ok' => false, 'errors' => ['Ticket bulunamadı.']];
        }
        if (($ticket['status_code'] ?? '') === self::STATUS_CLOSED) {
            return ['ok' => false, 'errors' => ['Kapalı ticket’a yanıt yazılamaz.']];
        }
        if (!$isStaff && (int) $ticket['account_id'] !== $accountId) {
            return ['ok' => false, 'errors' => ['Bu ticket sana ait değil.']];
        }
        if (!$isStaff && ($ticket['status_code'] ?? '') !== self::STATUS_WAIT_PLAYER) {
            return ['ok' => false, 'errors' => [
                'Bu ticket henüz yanıtlanmadı veya zaten dönüş bekleniyor. Mevcut içeriği değiştiremezsin; sadece yetkili cevapladıktan sonra yeni yanıt yazabilirsin.',
            ]];
        }

        try {
            $web = Database::web();
            $web->prepare(
                'INSERT INTO ticket_messages (ticket_id, account_id, account_login, is_staff, body, created_at)
                 VALUES (?,?,?,?,?,NOW())'
            )->execute([$ticketId, $accountId, $login, $isStaff ? 1 : 0, $body]);
            $messageId = (int) $web->lastInsertId();

            if ($uploadFile !== null) {
                $att = self::storeAttachment($ticketId, $messageId, $uploadFile);
                if (!$att['ok']) {
                    return ['ok' => false, 'errors' => $att['errors']];
                }
            }

            $next = $isStaff ? self::STATUS_WAIT_PLAYER : self::STATUS_WAIT_STAFF;
            $statusId = self::statusIdByCode($next);
            if ($statusId !== null) {
                $web->prepare('UPDATE tickets SET status_id=?, updated_at=NOW() WHERE id=?')
                    ->execute([$statusId, $ticketId]);
            }

            $code = (string) ($ticket['public_code'] ?? '');
            $subject = (string) ($ticket['subject'] ?? '');
            $ownerId = (int) ($ticket['account_id'] ?? 0);
            $ownerLogin = (string) ($ticket['account_login'] ?? '');

            if ($isStaff && $ownerId > 0) {
                $link = '/panel?ticket=' . $ticketId . '&section=destek';
                NotificationService::push(
                    $ownerId,
                    'ticket_replied',
                    'Ticket yanıtlandı: ' . $code,
                    $subject,
                    $link
                );
                $contact = self::accountContact($ownerId);
                if ($contact && $contact['email'] !== '') {
                    try {
                        MailService::sendTemplate('ticket_replied', $contact['email'], $ownerLogin, [
                            'login' => $ownerLogin,
                            'code' => $code,
                            'subject' => $subject,
                            'link' => $link,
                            'email' => $contact['email'],
                        ]);
                    } catch (\Throwable) {
                        // ignore
                    }
                }
            } elseif (!$isStaff) {
                $linkAdmin = '/admin?section=destekler&ticket=' . $ticketId;
                NotificationService::pushStaff(
                    'ticket_replied',
                    'Ticket yanıtı: ' . $code,
                    $ownerLogin . ' · ' . $subject,
                    $linkAdmin
                );
                foreach (NotificationService::staffAccountIds() as $staffId) {
                    $contact = self::accountContact($staffId);
                    if ($contact && $contact['email'] !== '') {
                        try {
                            MailService::sendTemplate('ticket_replied', $contact['email'], $contact['login'], [
                                'login' => $ownerLogin,
                                'code' => $code,
                                'subject' => $subject,
                                'link' => $linkAdmin,
                                'email' => $contact['email'],
                            ]);
                        } catch (\Throwable) {
                            // ignore
                        }
                    }
                }
            }

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Yanıt kaydedilemedi.']];
        }
    }

    /** @return array{ok:bool, errors:list<string>} */
    public static function closeTicket(int $ticketId): array
    {
        $statusId = self::statusIdByCode(self::STATUS_CLOSED);
        if ($statusId === null) {
            return ['ok' => false, 'errors' => ['Kapalı durumu yok.']];
        }
        $ticket = self::getTicket($ticketId);
        try {
            Database::web()->prepare(
                'UPDATE tickets SET status_id=?, closed_at=NOW(), updated_at=NOW() WHERE id=?'
            )->execute([$statusId, $ticketId]);

            if ($ticket) {
                $ownerId = (int) ($ticket['account_id'] ?? 0);
                $code = (string) ($ticket['public_code'] ?? '');
                $subject = (string) ($ticket['subject'] ?? '');
                $ownerLogin = (string) ($ticket['account_login'] ?? '');
                if ($ownerId > 0) {
                    $link = '/panel?ticket=' . $ticketId . '&section=destek';
                    NotificationService::push(
                        $ownerId,
                        'ticket_closed',
                        'Ticket kapatıldı: ' . $code,
                        $subject,
                        $link
                    );
                    $contact = self::accountContact($ownerId);
                    if ($contact && $contact['email'] !== '') {
                        try {
                            MailService::sendTemplate('ticket_closed', $contact['email'], $ownerLogin, [
                                'login' => $ownerLogin,
                                'code' => $code,
                                'subject' => $subject,
                                'link' => $link,
                                'email' => $contact['email'],
                            ]);
                        } catch (\Throwable) {
                            // ignore
                        }
                    }
                }
            }

            return ['ok' => true, 'errors' => []];
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Kapatılamadı.']];
        }
    }

    /** @return array{login:string, email:string}|null */
    private static function accountContact(int $accountId): ?array
    {
        if ($accountId <= 0) {
            return null;
        }
        try {
            $stmt = Database::account()->prepare('SELECT login, email FROM account WHERE id = ? LIMIT 1');
            $stmt->execute([$accountId]);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            return [
                'login' => (string) ($row['login'] ?? ''),
                'email' => trim((string) ($row['email'] ?? '')),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return list<array> */
    public static function forAccount(int $accountId, int $limit = 50): array
    {
        if ($accountId <= 0) {
            return [];
        }
        $limit = max(1, min(100, $limit));
        try {
            $stmt = Database::web()->prepare(
                "SELECT t.id, t.public_code, t.subject, t.created_at, t.updated_at,
                        c.name AS category_name, s.code AS status_code, s.label AS status_label
                 FROM tickets t
                 LEFT JOIN ticket_categories c ON c.id = t.category_id
                 LEFT JOIN ticket_statuses s ON s.id = t.status_id
                 WHERE t.account_id = ?
                 ORDER BY t.id DESC
                 LIMIT {$limit}"
            );
            $stmt->execute([$accountId]);
            return self::mapTicketRows($stmt->fetchAll() ?: []);
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return list<array> */
    public static function listAll(int $limit = 100, string $search = ''): array
    {
        $limit = max(1, min(300, $limit));
        $search = trim($search);
        try {
            $sql = "SELECT t.id, t.public_code, t.account_id, t.account_login, t.subject, t.created_at, t.updated_at,
                           c.name AS category_name, s.code AS status_code, s.label AS status_label
                    FROM tickets t
                    LEFT JOIN ticket_categories c ON c.id = t.category_id
                    LEFT JOIN ticket_statuses s ON s.id = t.status_id";
            $params = [];
            if ($search !== '') {
                $like = '%' . $search . '%';
                $codeExact = strtoupper(str_replace(' ', '', $search));
                $sql .= ' WHERE (t.public_code LIKE ? OR t.account_login LIKE ? OR REPLACE(t.public_code, \'-\', \'\') LIKE ?)';
                $params[] = $like;
                $params[] = $like;
                $params[] = '%' . str_replace('-', '', $codeExact) . '%';
            }
            $sql .= " ORDER BY t.id DESC LIMIT {$limit}";
            if ($params === []) {
                $rows = Database::web()->query($sql)->fetchAll() ?: [];
            } else {
                $stmt = Database::web()->prepare($sql);
                $stmt->execute($params);
                $rows = $stmt->fetchAll() ?: [];
            }
            return self::mapTicketRows($rows);
        } catch (\Throwable) {
            return [];
        }
    }

    public static function getTicket(int $id, ?int $ownerAccountId = null): ?array
    {
        if ($id <= 0) {
            return null;
        }
        try {
            $sql = 'SELECT t.*, c.name AS category_name, c.description AS category_description,
                           s.code AS status_code, s.label AS status_label
                    FROM tickets t
                    LEFT JOIN ticket_categories c ON c.id = t.category_id
                    LEFT JOIN ticket_statuses s ON s.id = t.status_id
                    WHERE t.id = ?';
            $params = [$id];
            if ($ownerAccountId !== null) {
                $sql .= ' AND t.account_id = ?';
                $params[] = $ownerAccountId;
            }
            $sql .= ' LIMIT 1';
            $stmt = Database::web()->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();
            if (!$row) {
                return null;
            }
            $ticket = [
                'id' => (int) $row['id'],
                'public_code' => (string) $row['public_code'],
                'account_id' => (int) $row['account_id'],
                'account_login' => (string) $row['account_login'],
                'category_id' => (int) $row['category_id'],
                'category_name' => (string) ($row['category_name'] ?? ''),
                'status_id' => (int) $row['status_id'],
                'status_code' => (string) ($row['status_code'] ?? ''),
                'status_label' => (string) ($row['status_label'] ?? ''),
                'subject' => (string) $row['subject'],
                'body' => (string) $row['body'],
                'created_at' => (string) $row['created_at'],
                'updated_at' => (string) $row['updated_at'],
                'messages' => self::messages((int) $row['id']),
            ];
            return $ticket;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function openCountForAccount(int $accountId): int
    {
        if ($accountId <= 0) {
            return 0;
        }
        try {
            $stmt = Database::web()->prepare(
                "SELECT COUNT(*) FROM tickets t
                 INNER JOIN ticket_statuses s ON s.id = t.status_id
                 WHERE t.account_id = ? AND s.code <> ?"
            );
            $stmt->execute([$accountId, self::STATUS_CLOSED]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function openCountAll(): int
    {
        try {
            $stmt = Database::web()->prepare(
                "SELECT COUNT(*) FROM tickets t
                 INNER JOIN ticket_statuses s ON s.id = t.status_id
                 WHERE s.code <> ?"
            );
            $stmt->execute([self::STATUS_CLOSED]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }

    /** @return list<array> */
    public static function messages(int $ticketId): array
    {
        try {
            $stmt = Database::web()->prepare(
                'SELECT m.id, m.account_id, m.account_login, m.is_staff, m.body, m.created_at,
                        a.id AS attachment_id, a.original_name, a.stored_path
                 FROM ticket_messages m
                 LEFT JOIN ticket_attachments a ON a.message_id = m.id
                 WHERE m.ticket_id = ?
                 ORDER BY m.id ASC'
            );
            $stmt->execute([$ticketId]);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $row) {
                $ts = strtotime((string) ($row['created_at'] ?? ''));
                $out[] = [
                    'id' => (int) $row['id'],
                    'account_id' => (int) $row['account_id'],
                    'account_login' => (string) $row['account_login'],
                    'is_staff' => (int) ($row['is_staff'] ?? 0) === 1,
                    'body' => (string) $row['body'],
                    'created_at' => (string) $row['created_at'],
                    'created_label' => $ts ? date('d.m.Y H:i', $ts) : '—',
                    'attachment' => !empty($row['attachment_id']) ? [
                        'id' => (int) $row['attachment_id'],
                        'name' => (string) $row['original_name'],
                        'path' => (string) $row['stored_path'],
                    ] : null,
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function statusIdByCode(string $code): ?int
    {
        try {
            $stmt = Database::web()->prepare('SELECT id FROM ticket_statuses WHERE code = ? LIMIT 1');
            $stmt->execute([$code]);
            $id = $stmt->fetchColumn();
            return $id !== false ? (int) $id : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function generateUniqueCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $web = Database::web();
        for ($i = 0; $i < 40; $i++) {
            $parts = [];
            for ($p = 0; $p < 3; $p++) {
                $seg = '';
                for ($c = 0; $c < 3; $c++) {
                    $seg .= $chars[random_int(0, strlen($chars) - 1)];
                }
                $parts[] = $seg;
            }
            $code = implode('-', $parts);
            $check = $web->prepare('SELECT COUNT(*) FROM tickets WHERE public_code = ?');
            $check->execute([$code]);
            if ((int) $check->fetchColumn() === 0) {
                return $code;
            }
        }
        throw new \RuntimeException('Ticket kodu üretilemedi.');
    }

    /**
     * @param array $file $_FILES item
     * @return array{ok:bool, errors:list<string>}
     */
    private static function storeAttachment(int $ticketId, int $messageId, array $file): array
    {
        $tmp = (string) ($file['tmp_name'] ?? '');
        $name = (string) ($file['name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        if ($tmp === '' || !is_uploaded_file($tmp) || $size <= 0) {
            return ['ok' => true, 'errors' => []]; // optional
        }
        if ($size > 5 * 1024 * 1024) {
            return ['ok' => false, 'errors' => ['Dosya en fazla 5MB olabilir.']];
        }

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = self::allowedFileTypes(true);
        $mimeMap = [];
        foreach ($allowed as $row) {
            $mimeMap[strtolower((string) $row['extension'])] = strtolower((string) $row['mime_type']);
        }
        if ($ext === '' || !isset($mimeMap[$ext])) {
            return ['ok' => false, 'errors' => ['Bu dosya türüne izin yok.']];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = strtolower((string) $finfo->file($tmp));
        $expected = $mimeMap[$ext];
        if ($detected !== $expected) {
            // allow common aliases
            $aliases = [
                'image/jpg' => 'image/jpeg',
                'image/pjpeg' => 'image/jpeg',
            ];
            $detectedNorm = $aliases[$detected] ?? $detected;
            $expectedNorm = $aliases[$expected] ?? $expected;
            if ($detectedNorm !== $expectedNorm) {
                return ['ok' => false, 'errors' => [
                    'Dosya bütünlüğü hatası: uzantı .' . $ext . ' ama içerik ' . $detected . ' olarak algılandı.',
                ]];
            }
        }

        $dir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'tickets';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['ok' => false, 'errors' => ['Yükleme klasörü oluşturulamadı.']];
        }
        $stored = 't_' . $ticketId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dest = $dir . DIRECTORY_SEPARATOR . $stored;
        if (!move_uploaded_file($tmp, $dest)) {
            return ['ok' => false, 'errors' => ['Dosya kaydedilemedi.']];
        }

        Database::web()->prepare(
            'INSERT INTO ticket_attachments
              (ticket_id, message_id, original_name, stored_path, mime_detected, size_bytes, created_at)
             VALUES (?,?,?,?,?,?,NOW())'
        )->execute([
            $ticketId,
            $messageId,
            mb_substr($name, 0, 200),
            '/uploads/tickets/' . $stored,
            $detected,
            $size,
        ]);

        return ['ok' => true, 'errors' => []];
    }

    /** @param list<array> $rows */
    private static function mapTicketRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            $ts = strtotime((string) ($row['created_at'] ?? ''));
            $out[] = [
                'id' => (int) $row['id'],
                'public_code' => (string) ($row['public_code'] ?? ''),
                'account_id' => (int) ($row['account_id'] ?? 0),
                'account_login' => (string) ($row['account_login'] ?? ''),
                'subject' => (string) ($row['subject'] ?? ''),
                'category_name' => (string) ($row['category_name'] ?? ''),
                'status_code' => (string) ($row['status_code'] ?? ''),
                'status_label' => (string) ($row['status_label'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'created_label' => $ts ? date('d.m.Y H:i', $ts) : '—',
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ];
        }
        return $out;
    }
}
