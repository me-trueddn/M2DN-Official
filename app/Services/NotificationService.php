<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\ServerManager;

/** Panel içi bildirimler (zil ikonu). */
final class NotificationService
{
    public static function push(int $recipientAccountId, string $type, string $title, string $body = '', string $link = ''): void
    {
        if ($recipientAccountId <= 0) {
            return;
        }
        try {
            Database::web()->prepare(
                'INSERT INTO notifications (recipient_account_id, type, title, body, link, is_read, created_at)
                 VALUES (?,?,?,?,?,0,NOW())'
            )->execute([
                $recipientAccountId,
                mb_substr(trim($type), 0, 40),
                mb_substr(trim($title), 0, 200),
                mb_substr(trim($body), 0, 1000),
                mb_substr(trim($link), 0, 500),
            ]);
        } catch (\Throwable) {
            // ignore
        }
    }

    /** WebPermission >= 1 tüm hesaplara. */
    public static function pushStaff(string $type, string $title, string $body = '', string $link = ''): void
    {
        foreach (self::staffAccountIds() as $id) {
            self::push($id, $type, $title, $body, $link);
        }
    }

    /** @return list<int> */
    public static function staffAccountIds(): array
    {
        try {
            $serverKey = ServerManager::current()['key'] ?? null;
            $pdo = Database::account($serverKey);
            $rows = $pdo->query(
                'SELECT id FROM account WHERE WebPermission IS NOT NULL AND WebPermission >= 1'
            )->fetchAll() ?: [];
            $ids = [];
            foreach ($rows as $r) {
                $id = (int) ($r['id'] ?? 0);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
            return $ids;
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return list<array> */
    public static function forAccount(int $accountId, int $limit = 30): array
    {
        if ($accountId <= 0) {
            return [];
        }
        $limit = max(1, min(50, $limit));
        try {
            $stmt = Database::web()->prepare(
                "SELECT id, type, title, body, link, is_read, created_at
                 FROM notifications WHERE recipient_account_id = ?
                 ORDER BY id DESC LIMIT {$limit}"
            );
            $stmt->execute([$accountId]);
            $out = [];
            foreach ($stmt->fetchAll() ?: [] as $r) {
                $ts = strtotime((string) ($r['created_at'] ?? ''));
                $out[] = [
                    'id' => (int) $r['id'],
                    'type' => (string) $r['type'],
                    'title' => (string) $r['title'],
                    'body' => (string) $r['body'],
                    'link' => (string) $r['link'],
                    'is_read' => (int) ($r['is_read'] ?? 0) === 1,
                    'created_at' => (string) ($r['created_at'] ?? ''),
                    'created_label' => $ts ? date('d.m.Y H:i', $ts) : '—',
                ];
            }
            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    public static function unreadCount(int $accountId): int
    {
        if ($accountId <= 0) {
            return 0;
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT COUNT(*) FROM notifications WHERE recipient_account_id = ? AND is_read = 0'
            );
            $stmt->execute([$accountId]);
            return (int) $stmt->fetchColumn();
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function markRead(int $accountId, ?int $notificationId = null): void
    {
        if ($accountId <= 0) {
            return;
        }
        try {
            if ($notificationId && $notificationId > 0) {
                Database::web()->prepare(
                    'UPDATE notifications SET is_read = 1 WHERE id = ? AND recipient_account_id = ?'
                )->execute([$notificationId, $accountId]);
                return;
            }
            Database::web()->prepare(
                'UPDATE notifications SET is_read = 1 WHERE recipient_account_id = ? AND is_read = 0'
            )->execute([$accountId]);
        } catch (\Throwable) {
            // ignore
        }
    }
}
