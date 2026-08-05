<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * DNWeb hesap onayları (topluluk kuralları vb.).
 */
final class AccountConsentService
{
    public static function recordRulesAccepted(int $accountId): bool
    {
        if ($accountId <= 0) {
            return false;
        }
        try {
            Database::web()->prepare(
                'INSERT INTO account_consents (account_id, rules_accepted, rules_accepted_at, created_at, updated_at)
                 VALUES (?, 1, NOW(), NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                   rules_accepted = 1,
                   rules_accepted_at = NOW(),
                   updated_at = NOW()'
            )->execute([$accountId]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array{accepted:bool, accepted_at:?string, accepted_label:string}
     */
    public static function rulesStatus(int $accountId): array
    {
        $empty = ['accepted' => false, 'accepted_at' => null, 'accepted_label' => 'Hayır'];
        if ($accountId <= 0) {
            return $empty;
        }
        try {
            $stmt = Database::web()->prepare(
                'SELECT rules_accepted, rules_accepted_at FROM account_consents WHERE account_id = ? LIMIT 1'
            );
            $stmt->execute([$accountId]);
            $row = $stmt->fetch();
            if (!$row || empty($row['rules_accepted'])) {
                return $empty;
            }
            $at = (string) ($row['rules_accepted_at'] ?? '');
            $ts = $at !== '' && $at !== '0000-00-00 00:00:00' ? strtotime($at) : false;
            return [
                'accepted' => true,
                'accepted_at' => $ts ? date('Y-m-d H:i:s', $ts) : $at,
                'accepted_label' => $ts ? ('Evet · ' . date('d.m.Y H:i', $ts)) : 'Evet',
            ];
        } catch (\Throwable) {
            return $empty;
        }
    }
}
