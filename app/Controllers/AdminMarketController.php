<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Security;
use App\Core\Session;
use App\Services\AuthService;
use App\Services\MarketCategoryService;
use App\Services\MarketCouponService;
use App\Services\MarketItemService;
use App\Services\AdminLogService;
use App\Services\PermissionService;

final class AdminMarketController
{
    private function gate(): array
    {
        $user = AuthService::requireAdmin();
        if (!PermissionService::userHasFlag($user, PermissionService::FLAG_MENU_NESNE_MARKET)
            && !PermissionService::userHasFlag($user, PermissionService::FLAG_SITE_SETTINGS)
        ) {
            Session::flash('panel_errors', ['Nesne Market yetkin yok.']);
            redirect('/admin');
        }
        Security::requireCsrf('login');
        return $user;
    }

    private function ok(string $msg, string $section = 'nesne-market-kategoriler'): void
    {
        Session::flash('panel_success', $msg);
        Session::flash('panel_section', $section);
        redirect('/admin?section=' . $section);
    }

    private function fail(array $errors, string $section = 'nesne-market-kategoriler'): void
    {
        Session::flash('panel_errors', $errors);
        Session::flash('panel_section', $section);
        redirect('/admin?section=' . $section);
    }

    public function saveCategory(): void
    {
        $this->gate();
        $result = MarketCategoryService::save([
            'id' => (int) ($_POST['id'] ?? 0),
            'name' => (string) ($_POST['name'] ?? ''),
            'slug' => (string) ($_POST['slug'] ?? ''),
            'icon' => (string) ($_POST['icon'] ?? ''),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => !empty($_POST['is_active']),
        ]);
        if (empty($result['ok'])) {
            $this->fail($result['errors'] ?? ['Kayıt başarısız.']);
        }
        $this->ok('Kategori kaydedildi.');
    }

    public function deleteCategory(): void
    {
        $this->gate();
        $id = (int) ($_POST['id'] ?? 0);
        if (!MarketCategoryService::delete($id)) {
            $this->fail(['Kategori silinemedi.']);
        }
        $this->ok('Kategori silindi.');
    }

    public function toggleCategory(): void
    {
        $this->gate();
        $id = (int) ($_POST['id'] ?? 0);
        $active = ((string) ($_POST['is_active'] ?? '1')) === '1';
        if (!MarketCategoryService::toggle($id, $active)) {
            $this->fail(['Durum güncellenemedi.']);
        }
        $this->ok('Kategori durumu güncellendi.');
    }

    public function saveItem(): void
    {
        $this->gate();
        $section = 'nesne-market-urunler';
        $upload = isset($_FILES['image_file']) && is_array($_FILES['image_file'])
            ? $_FILES['image_file']
            : null;
        $result = MarketItemService::save([
            'id' => (int) ($_POST['id'] ?? 0),
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'name' => (string) ($_POST['name'] ?? ''),
            'description' => (string) ($_POST['description'] ?? ''),
            'price' => (int) ($_POST['price'] ?? 0),
            'discount_active' => !empty($_POST['discount_active']),
            'discount_percent' => (int) ($_POST['discount_percent'] ?? 0),
            'image_url' => (string) ($_POST['image_url'] ?? ''),
            'duration_type' => (string) ($_POST['duration_type'] ?? 'permanent'),
            'item_code' => (string) ($_POST['item_code'] ?? ''),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
            'is_active' => !empty($_POST['is_active']),
        ], $upload);
        if (empty($result['ok'])) {
            $this->fail($result['errors'] ?? ['Kayıt başarısız.'], $section);
        }
        $this->ok('Ürün kaydedildi.', $section);
    }

    public function deleteItem(): void
    {
        $this->gate();
        $section = 'nesne-market-urunler';
        $id = (int) ($_POST['id'] ?? 0);
        if (!MarketItemService::delete($id)) {
            $this->fail(['Ürün silinemedi.'], $section);
        }
        $this->ok('Ürün silindi.', $section);
    }

    public function toggleItem(): void
    {
        $this->gate();
        $section = 'nesne-market-urunler';
        $id = (int) ($_POST['id'] ?? 0);
        $active = ((string) ($_POST['is_active'] ?? '1')) === '1';
        if (!MarketItemService::toggle($id, $active)) {
            $this->fail(['Durum güncellenemedi.'], $section);
        }
        $this->ok('Ürün durumu güncellendi.', $section);
    }

    public function saveCouponCategory(): void
    {
        $user = $this->gate();
        $section = 'nesne-market-kuponlar';
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $cash = (int) ($_POST['cash_amount'] ?? 0);
        $result = MarketCouponService::saveCategory([
            'id' => $id,
            'name' => $name,
            'cash_amount' => $cash,
            'is_active' => !empty($_POST['is_active']),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        if (empty($result['ok'])) {
            $this->fail($result['errors'] ?? ['Kayıt başarısız.'], $section);
        }
        AdminLogService::write(
            $user,
            $id > 0 ? 'Market kupon kategorisi güncellendi' : 'Market kupon kategorisi eklendi',
            $name . ' · Count: ' . number_format($cash, 0, ',', '.') . ' Elmas'
            . ($id > 0 ? ' · #' . $id : ' · #' . (int) ($result['id'] ?? 0))
        );
        $this->ok('Kupon kategorisi kaydedildi.', $section);
    }

    public function deleteCouponCategory(): void
    {
        $user = $this->gate();
        $section = 'nesne-market-kuponlar';
        $id = (int) ($_POST['id'] ?? 0);
        $cat = MarketCouponService::findCategory($id);
        $result = MarketCouponService::deleteCategory($id);
        if (empty($result['ok'])) {
            $this->fail($result['errors'] ?? ['Silinemedi.'], $section);
        }
        AdminLogService::write(
            $user,
            'Market kupon kategorisi silindi',
            ($cat['name'] ?? 'Kategori') . ' · #' . $id
        );
        $this->ok('Kupon kategorisi silindi.', $section);
    }

    public function generateCoupons(): void
    {
        $user = $this->gate();
        $section = 'nesne-market-kuponlar';
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $cat = MarketCouponService::findCategory($categoryId);
        $result = MarketCouponService::generate(
            $categoryId,
            $quantity,
            [
                'account_id' => (int) ($user['account_id'] ?? 0),
                'login' => (string) ($user['login'] ?? ''),
            ]
        );
        if (empty($result['ok'])) {
            $this->fail($result['errors'] ?? ['Oluşturulamadı.'], $section);
        }
        $created = (int) ($result['created'] ?? 0);
        AdminLogService::write(
            $user,
            'Market kuponu oluşturuldu',
            $created . ' adet · '
            . (string) ($cat['name'] ?? ('Kategori #' . $categoryId))
            . ' · Count: ' . number_format((int) ($cat['cash_amount'] ?? 0), 0, ',', '.') . ' Elmas'
        );
        Session::flash('coupon_generated_codes', $result['codes'] ?? []);
        $this->ok($created . ' kupon oluşturuldu. Kodları şimdi kopyala — tekrar gösterilmez.', $section);
    }

    public function deleteCoupons(): void
    {
        $user = $this->gate();
        $section = 'nesne-market-kuponlar';
        $ids = $_POST['ids'] ?? [];
        if (!is_array($ids)) {
            $ids = [];
        }
        $result = MarketCouponService::deleteMany($ids);
        if (empty($result['ok'])) {
            $this->fail($result['errors'] ?? ['Silinemedi.'], $section);
        }
        $deleted = (int) ($result['deleted'] ?? 0);
        AdminLogService::write(
            $user,
            'Market kuponu silindi',
            $deleted . ' adet · id: ' . implode(', ', array_map('intval', array_slice($ids, 0, 40)))
            . (count($ids) > 40 ? '…' : '')
        );
        $this->ok($deleted . ' kupon silindi.', $section);
    }
}
