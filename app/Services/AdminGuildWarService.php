<?php

declare(strict_types=1);

namespace App\Services;

/**
 * @deprecated Use GuildWarService — geriye uyumluluk köprüsü.
 */
final class AdminGuildWarService
{
    /** @return list<array> */
    public static function listActive(?string $serverKey = null): array
    {
        return GuildWarService::listActive($serverKey);
    }

    public static function typeLabel(?int $type): string
    {
        return GuildWarService::typeLabel($type);
    }
}
