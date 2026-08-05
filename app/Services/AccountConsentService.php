<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * DNWeb hesap onayları (topluluk kuralları vb.).
 */
final class AccountConsentService
{
    public static function recordRulesAccepted(int $accountId, ?int $revision = null): bool
    {
        if ($accountId <= 0) {
            return false;
        }
        $revision = $revision ?? CommunityRulesService::currentRevision();
        try {
            Database::web()->prepare(
                'INSERT INTO account_consents
                  (account_id, rules_accepted, rules_accepted_at, rules_revision, created_at, updated_at)
                 VALUES (?, 1, NOW(), ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                   rules_accepted = 1,
                   rules_accepted_at = NOW(),
                   rules_revision = VALUES(rules_revision),
                   updated_at = NOW()'
            )->execute([$accountId, max(0, $revision)]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Güncel kural revizyonu kabul edilmemişse true.
     */
    public static function needsRulesAcceptance(int $accountId): bool
    {
        if ($accountId <= 0) {
            return false;
        }
        $current = CommunityRulesService::currentRevision();
        try {
            $stmt = Database::web()->prepare(
                'SELECT rules_accepted, rules_revision FROM account_consents WHERE account_id = ? LIMIT 1'
            );
            $stmt->execute([$accountId]);
            $row = $stmt->fetch();
            if (!$row || empty($row['rules_accepted'])) {
                return true;
            }
            return (int) ($row['rules_revision'] ?? 0) < $current;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array{accepted:bool, accepted_at:?string, accepted_label:string, revision:int, needs_reaccept:bool}
     */
    public static function rulesStatus(int $accountId): array
    {
        $empty = [
            'accepted' => false,
            'accepted_at' => null,
            'accepted_label' => 'Hayır',
            'revision' => 0,
            'needs_reaccept' => true,
        ];
        if ($accountId <= 0) {
            return $empty;
        }
        $current = CommunityRulesService::currentRevision();
        try {
            $stmt = Database::web()->prepare(
                'SELECT rules_accepted, rules_accepted_at, rules_revision
                 FROM account_consents WHERE account_id = ? LIMIT 1'
            );
            $stmt->execute([$accountId]);
            $row = $stmt->fetch();
            if (!$row || empty($row['rules_accepted'])) {
                return $empty;
            }
            $revision = (int) ($row['rules_revision'] ?? 0);
            $needs = $revision < $current;
            $at = (string) ($row['rules_accepted_at'] ?? '');
            $ts = $at !== '' && $at !== '0000-00-00 00:00:00' ? strtotime($at) : false;
            $label = $ts ? ('Evet · ' . date('d.m.Y H:i', $ts)) : 'Evet';
            if ($needs) {
                $label .= ' · Yeniden onay gerekli';
            }
            return [
                'accepted' => true,
                'accepted_at' => $ts ? date('Y-m-d H:i:s', $ts) : $at,
                'accepted_label' => $label,
                'revision' => $revision,
                'needs_reaccept' => $needs,
            ];
        } catch (\Throwable) {
            return $empty;
        }
    }
}
