<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * DNWeb hesap onayları (topluluk kuralları, gizlilik / KVKK).
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

    public static function recordPrivacyAccepted(int $accountId, ?int $revision = null): bool
    {
        if ($accountId <= 0) {
            return false;
        }
        $revision = $revision ?? LegalContentService::privacyRevision();
        try {
            Database::web()->prepare(
                'INSERT INTO account_consents
                  (account_id, privacy_accepted, privacy_accepted_at, privacy_revision, created_at, updated_at)
                 VALUES (?, 1, NOW(), ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE
                   privacy_accepted = 1,
                   privacy_accepted_at = NOW(),
                   privacy_revision = VALUES(privacy_revision),
                   updated_at = NOW()'
            )->execute([$accountId, max(0, $revision)]);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /** Güncel kural revizyonu kabul edilmemişse true. */
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

    /** Güncel gizlilik / KVKK revizyonu kabul edilmemişse true. */
    public static function needsPrivacyAcceptance(int $accountId): bool
    {
        if ($accountId <= 0) {
            return false;
        }
        $current = LegalContentService::privacyRevision();
        try {
            $stmt = Database::web()->prepare(
                'SELECT privacy_accepted, privacy_revision FROM account_consents WHERE account_id = ? LIMIT 1'
            );
            $stmt->execute([$accountId]);
            $row = $stmt->fetch();
            if (!$row || empty($row['privacy_accepted'])) {
                return true;
            }
            return (int) ($row['privacy_revision'] ?? 0) < $current;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array{accepted:bool, accepted_at:?string, accepted_label:string, revision:int, needs_reaccept:bool}
     */
    public static function rulesStatus(int $accountId): array
    {
        return self::statusFor(
            $accountId,
            'rules_accepted',
            'rules_accepted_at',
            'rules_revision',
            CommunityRulesService::currentRevision()
        );
    }

    /**
     * @return array{accepted:bool, accepted_at:?string, accepted_label:string, revision:int, needs_reaccept:bool}
     */
    public static function privacyStatus(int $accountId): array
    {
        return self::statusFor(
            $accountId,
            'privacy_accepted',
            'privacy_accepted_at',
            'privacy_revision',
            LegalContentService::privacyRevision()
        );
    }

    /**
     * @return array{accepted:bool, accepted_at:?string, accepted_label:string, revision:int, needs_reaccept:bool}
     */
    private static function statusFor(
        int $accountId,
        string $acceptedCol,
        string $atCol,
        string $revCol,
        int $current
    ): array {
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
        try {
            $stmt = Database::web()->prepare(
                "SELECT {$acceptedCol}, {$atCol}, {$revCol}
                 FROM account_consents WHERE account_id = ? LIMIT 1"
            );
            $stmt->execute([$accountId]);
            $row = $stmt->fetch();
            if (!$row || empty($row[$acceptedCol])) {
                return $empty;
            }
            $revision = (int) ($row[$revCol] ?? 0);
            $needs = $revision < $current;
            $at = (string) ($row[$atCol] ?? '');
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
