<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Database;
use PDO;

/**
 * Nesne Market satın alma → account.cash düşümü + player.item SAFEBOX.
 */
final class MarketPurchaseService
{
    /**
     * @return array{ok:bool, errors:list<string>, cash?:int, cash_before?:int, cash_after?:int, item?:array}
     */
    public static function purchase(int $accountId, string $accountLogin, int $marketItemId, string $ip = ''): array
    {
        if ($accountId <= 0) {
            return ['ok' => false, 'errors' => ['Oturum geçersiz.']];
        }

        $product = MarketItemService::find($marketItemId);
        if ($product === null || empty($product['is_active'])) {
            return ['ok' => false, 'errors' => ['Ürün bulunamadı veya satışta değil.']];
        }

        $itemCode = MarketItemService::normalizeItemCode((string) ($product['item_code'] ?? ''));
        $vnum = (int) $itemCode;
        if ($itemCode === '' || $vnum <= 0) {
            return ['ok' => false, 'errors' => ['Ürün item kodu geçersiz.']];
        }

        $price = (int) ($product['sale_price'] ?? 0);
        if ($price < 0) {
            return ['ok' => false, 'errors' => ['Geçersiz fiyat.']];
        }

        $itemName = (string) ($product['name'] ?? '');
        $pageSize = max(1, (int) Config::get('nesne_market.safebox_page_size', 45));
        $defaultPages = max(1, (int) Config::get('nesne_market.safebox_default_pages', 1));

        $acc = Database::account();
        $player = Database::player();
        $web = Database::web();

        // Ön kontrol: DB'den güncel cash (hile / eski UI bakiyesine karşı)
        try {
            $pre = $acc->prepare('SELECT cash FROM account WHERE id = ? LIMIT 1');
            $pre->execute([$accountId]);
            $preCash = $pre->fetchColumn();
            if ($preCash === false) {
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }
            if ((int) $preCash < $price) {
                return ['ok' => false, 'errors' => [
                    'Yetersiz Elmas. Bakiyen: ' . number_format((int) $preCash, 0, ',', '.') . '.',
                ]];
            }
        } catch (\Throwable) {
            return ['ok' => false, 'errors' => ['Bakiye okunamadı.']];
        }

        $pos = -1;
        $playerItemId = 0;

        // 1) Depoya item yaz (slot kilidi)
        try {
            $player->beginTransaction();
            $capacity = self::ensureSafeboxCapacity($player, $accountId, $defaultPages, $pageSize);
            $pos = self::findFreeSafeboxPos($player, $accountId, $capacity);
            if ($pos < 0) {
                $player->rollBack();
                return ['ok' => false, 'errors' => ['Depoda boş yer yok. Depocuya gidip yer açtıktan sonra tekrar dene.']];
            }

            $ins = $player->prepare(
                'INSERT INTO item
                 (owner_id, window, pos, count, vnum,
                  socket0, socket1, socket2, socket3, socket4, socket5,
                  attrtype0, attrvalue0, attrtype1, attrvalue1, attrtype2, attrvalue2,
                  attrtype3, attrvalue3, attrtype4, attrvalue4, attrtype5, attrvalue5,
                  attrtype6, attrvalue6)
                 VALUES
                 (?, \'SAFEBOX\', ?, 1, ?,
                  0, 0, 0, 0, 0, 0,
                  0, 0, 0, 0, 0, 0,
                  0, 0, 0, 0, 0, 0,
                  0, 0)'
            );
            $ins->execute([$accountId, $pos, $vnum]);
            $playerItemId = (int) $player->lastInsertId();
            if ($playerItemId <= 0) {
                $player->rollBack();
                return ['ok' => false, 'errors' => ['Eşya depoya yazılamadı.']];
            }
            $player->commit();
        } catch (\Throwable) {
            if ($player->inTransaction()) {
                $player->rollBack();
            }
            return ['ok' => false, 'errors' => ['Depoya yazma başarısız.']];
        }

        // 2) Cash düş — her zaman FOR UPDATE + optimistic check
        $cashBefore = 0;
        $cashAfter = 0;
        try {
            $acc->beginTransaction();
            $cashStmt = $acc->prepare('SELECT cash FROM account WHERE id = ? LIMIT 1 FOR UPDATE');
            $cashStmt->execute([$accountId]);
            $cashRow = $cashStmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($cashRow)) {
                $acc->rollBack();
                self::deletePlayerItem($player, $playerItemId);
                return ['ok' => false, 'errors' => ['Hesap bulunamadı.']];
            }
            $cashBefore = (int) ($cashRow['cash'] ?? 0);
            if ($cashBefore < $price) {
                $acc->rollBack();
                self::deletePlayerItem($player, $playerItemId);
                return ['ok' => false, 'errors' => [
                    'Yetersiz Elmas. Bakiyen: ' . number_format($cashBefore, 0, ',', '.') . '.',
                ]];
            }
            $cashAfter = $cashBefore - $price;
            $upd = $acc->prepare('UPDATE account SET cash = ? WHERE id = ? AND cash = ?');
            $upd->execute([$cashAfter, $accountId, $cashBefore]);
            if ($upd->rowCount() !== 1) {
                $acc->rollBack();
                self::deletePlayerItem($player, $playerItemId);
                return ['ok' => false, 'errors' => ['Bakiye güncellenemedi (eşzamanlı işlem). Tekrar dene.']];
            }
            $acc->commit();
        } catch (\Throwable) {
            if ($acc->inTransaction()) {
                $acc->rollBack();
            }
            self::deletePlayerItem($player, $playerItemId);
            return ['ok' => false, 'errors' => ['Ödeme işlemi başarısız.']];
        }

        // 3) Satış logu
        try {
            $web->prepare(
                'INSERT INTO market_sales_logs
                 (account_id, account_login, market_item_id, item_code, item_name,
                  price, cash_before, cash_after, safebox_pos, player_item_id, ip, created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW())'
            )->execute([
                $accountId,
                mb_substr($accountLogin, 0, 30),
                (int) $product['id'],
                $itemCode,
                mb_substr($itemName, 0, 160),
                $price,
                $cashBefore,
                $cashAfter,
                $pos,
                $playerItemId,
                mb_substr($ip, 0, 45),
            ]);
        } catch (\Throwable) {
            // Alım tamam; log opsiyonel
        }

        $verify = $acc->prepare('SELECT cash FROM account WHERE id = ? LIMIT 1');
        $verify->execute([$accountId]);
        $verifiedCash = (int) ($verify->fetchColumn() ?: $cashAfter);

        return [
            'ok' => true,
            'errors' => [],
            'cash' => $verifiedCash,
            'cash_before' => $cashBefore,
            'cash_after' => $verifiedCash,
            'item' => [
                'id' => (int) $product['id'],
                'code' => $itemCode,
                'name' => $itemName,
                'price' => $price,
                'pos' => $pos,
            ],
        ];
    }

    /**
     * @return array{logs:list<array>, total:int, page:int, pages:int, per_page:int, q:string}
     */
    public static function salesLogs(string $q = '', int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(5, min(100, $perPage));
        $q = trim($q);
        try {
            $web = Database::web();
            $where = '';
            $params = [];
            if ($q !== '') {
                $where = ' WHERE account_login LIKE ? OR item_name LIKE ? OR item_code LIKE ?';
                $like = '%' . $q . '%';
                $params = [$like, $like, $like];
            }
            $countStmt = $web->prepare('SELECT COUNT(*) FROM market_sales_logs' . $where);
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            $pages = max(1, (int) ceil($total / $perPage));
            if ($page > $pages) {
                $page = $pages;
            }
            $offset = ($page - 1) * $perPage;
            $sql = 'SELECT id, account_id, account_login, market_item_id, item_code, item_name,
                           price, cash_before, cash_after, safebox_pos, player_item_id, ip, created_at
                    FROM market_sales_logs' . $where . '
                    ORDER BY id DESC
                    LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;
            $stmt = $web->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            $logs = [];
            foreach ($rows as $row) {
                $logs[] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'account_id' => (int) ($row['account_id'] ?? 0),
                    'account_login' => (string) ($row['account_login'] ?? ''),
                    'market_item_id' => (int) ($row['market_item_id'] ?? 0),
                    'item_code' => (string) ($row['item_code'] ?? ''),
                    'item_name' => (string) ($row['item_name'] ?? ''),
                    'price' => (int) ($row['price'] ?? 0),
                    'cash_before' => (int) ($row['cash_before'] ?? 0),
                    'cash_after' => (int) ($row['cash_after'] ?? 0),
                    'safebox_pos' => (int) ($row['safebox_pos'] ?? -1),
                    'player_item_id' => (int) ($row['player_item_id'] ?? 0),
                    'ip' => (string) ($row['ip'] ?? ''),
                    'created_at' => (string) ($row['created_at'] ?? ''),
                ];
            }
            return [
                'logs' => $logs,
                'total' => $total,
                'page' => $page,
                'pages' => $pages,
                'per_page' => $perPage,
                'q' => $q,
            ];
        } catch (\Throwable) {
            return ['logs' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'per_page' => $perPage, 'q' => $q];
        }
    }

    private static function ensureSafeboxCapacity(PDO $player, int $accountId, int $defaultPages, int $pageSize): int
    {
        $stmt = $player->prepare('SELECT size FROM safebox WHERE account_id = ? LIMIT 1 FOR UPDATE');
        $stmt->execute([$accountId]);
        $size = $stmt->fetchColumn();
        if ($size === false) {
            $player->prepare('INSERT INTO safebox (account_id, size, password, gold) VALUES (?, ?, \'\', 0)')
                ->execute([$accountId, $defaultPages]);
            return $defaultPages * $pageSize;
        }
        $pages = (int) $size;
        if ($pages <= 0) {
            $pages = $defaultPages;
            $player->prepare('UPDATE safebox SET size = ? WHERE account_id = ?')->execute([$pages, $accountId]);
        }
        return $pages * $pageSize;
    }

    private static function findFreeSafeboxPos(PDO $player, int $accountId, int $capacity): int
    {
        if ($capacity <= 0) {
            return -1;
        }
        $stmt = $player->prepare(
            'SELECT pos FROM item WHERE owner_id = ? AND window = \'SAFEBOX\' ORDER BY pos ASC'
        );
        $stmt->execute([$accountId]);
        $used = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) ?: [] as $p) {
            $used[(int) $p] = true;
        }
        for ($i = 0; $i < $capacity; $i++) {
            if (!isset($used[$i])) {
                return $i;
            }
        }
        return -1;
    }

    private static function deletePlayerItem(PDO $player, int $itemId): void
    {
        if ($itemId <= 0) {
            return;
        }
        try {
            $player->prepare('DELETE FROM item WHERE id = ? LIMIT 1')->execute([$itemId]);
        } catch (\Throwable) {
            // ignore
        }
    }
}
