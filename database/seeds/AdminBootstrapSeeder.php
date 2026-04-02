<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminBootstrapSeeder extends Seeder
{
    /**
     * Seed non-sensitive admin panel skeleton data.
     *
     * @return void
     */
    public function run()
    {
        $this->seedMenu();
        $this->seedPermissions();
        $this->seedRoles();
    }

    private function seedMenu(): void
    {
        foreach ($this->menuRecords() as $record) {
            $this->upsertById('admin_menu', $record);
        }
    }

    private function seedPermissions(): void
    {
        foreach ($this->permissionRecords() as $record) {
            $this->upsertById('admin_permissions', $record);
        }
    }

    private function seedRoles(): void
    {
        foreach ($this->roleRecords() as $record) {
            $this->upsertById('admin_roles', $record);
        }
    }

    /**
     * @param string $table
     * @param array<string, mixed> $record
     */
    private function upsertById(string $table, array $record): void
    {
        if (DB::table($table)->where('id', $record['id'])->exists()) {
            DB::table($table)->where('id', $record['id'])->update($record);
            return;
        }

        DB::table($table)->insert($record);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function menuRecords(): array
    {
        return [
            ['id' => 1, 'parent_id' => 0, 'order' => 1, 'title' => 'Index', 'icon' => 'feather icon-bar-chart-2', 'uri' => '/', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 2, 'parent_id' => 0, 'order' => 2, 'title' => 'Admin', 'icon' => 'feather icon-settings', 'uri' => '', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 3, 'parent_id' => 2, 'order' => 3, 'title' => 'Users', 'icon' => '', 'uri' => 'auth/users', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 4, 'parent_id' => 2, 'order' => 4, 'title' => 'Roles', 'icon' => '', 'uri' => 'auth/roles', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 5, 'parent_id' => 2, 'order' => 5, 'title' => 'Permission', 'icon' => '', 'uri' => 'auth/permissions', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 6, 'parent_id' => 2, 'order' => 6, 'title' => 'Menu', 'icon' => '', 'uri' => 'auth/menu', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 7, 'parent_id' => 2, 'order' => 7, 'title' => 'Extensions', 'icon' => '', 'uri' => 'auth/extensions', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 11, 'parent_id' => 0, 'order' => 9, 'title' => 'Goods_Manage', 'icon' => 'fa-shopping-bag', 'uri' => null, 'extension' => '', 'show' => 1, 'created_at' => '2021-05-16 11:39:55', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 12, 'parent_id' => 11, 'order' => 11, 'title' => 'Goods', 'icon' => 'fa-shopping-bag', 'uri' => '/goods', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-16 11:44:35', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 13, 'parent_id' => 11, 'order' => 10, 'title' => 'Goods_Group', 'icon' => 'fa-star-half-o', 'uri' => '/goods-group', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-16 17:08:43', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 14, 'parent_id' => 0, 'order' => 12, 'title' => 'Carmis_Manage', 'icon' => 'fa-credit-card-alt', 'uri' => null, 'extension' => '', 'show' => 1, 'created_at' => '2021-05-17 21:29:50', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 15, 'parent_id' => 14, 'order' => 13, 'title' => 'Carmis', 'icon' => 'fa-credit-card', 'uri' => '/carmis', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-17 21:37:59', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 16, 'parent_id' => 14, 'order' => 14, 'title' => 'Import_Carmis', 'icon' => 'fa-plus-circle', 'uri' => '/import-carmis', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-18 14:46:35', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 17, 'parent_id' => 18, 'order' => 16, 'title' => 'Coupon', 'icon' => 'fa-dollar', 'uri' => '/coupon', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-18 17:29:53', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 18, 'parent_id' => 0, 'order' => 15, 'title' => 'Coupon_Manage', 'icon' => 'fa-diamond', 'uri' => null, 'extension' => '', 'show' => 1, 'created_at' => '2021-05-18 17:32:03', 'updated_at' => '2021-05-18 17:32:03'],
            ['id' => 19, 'parent_id' => 0, 'order' => 17, 'title' => 'Configuration', 'icon' => 'fa-wrench', 'uri' => null, 'extension' => '', 'show' => 1, 'created_at' => '2021-05-20 20:06:47', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 20, 'parent_id' => 19, 'order' => 18, 'title' => 'Email_Template_Configuration', 'icon' => 'fa-envelope', 'uri' => '/emailtpl', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-20 20:17:07', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 21, 'parent_id' => 19, 'order' => 19, 'title' => 'Pay_Configuration', 'icon' => 'fa-cc-visa', 'uri' => '/pay', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-20 20:41:24', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 22, 'parent_id' => 0, 'order' => 8, 'title' => 'Order_Manage', 'icon' => 'fa-table', 'uri' => null, 'extension' => '', 'show' => 1, 'created_at' => '2021-05-23 20:43:43', 'updated_at' => '2021-05-23 20:44:20'],
            ['id' => 23, 'parent_id' => 22, 'order' => 20, 'title' => 'Order', 'icon' => 'fa-heart', 'uri' => '/order', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-23 20:46:13', 'updated_at' => '2021-05-23 20:47:16'],
            ['id' => 24, 'parent_id' => 19, 'order' => 21, 'title' => 'System_Setting', 'icon' => 'fa-cogs', 'uri' => '/system-setting', 'extension' => '', 'show' => 1, 'created_at' => '2021-05-26 10:26:34', 'updated_at' => '2021-05-26 10:26:34'],
            ['id' => 25, 'parent_id' => 19, 'order' => 22, 'title' => 'Email_Test', 'icon' => 'fa-envelope', 'uri' => '/email-test', 'extension' => '', 'show' => 1, 'created_at' => '2022-07-26 12:09:34', 'updated_at' => '2022-07-26 12:17:21'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function permissionRecords(): array
    {
        return [
            ['id' => 1, 'name' => 'Auth management', 'slug' => 'auth-management', 'http_method' => '', 'http_path' => '', 'order' => 1, 'parent_id' => 0, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 2, 'name' => 'Users', 'slug' => 'users', 'http_method' => '', 'http_path' => '/auth/users*', 'order' => 2, 'parent_id' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 3, 'name' => 'Roles', 'slug' => 'roles', 'http_method' => '', 'http_path' => '/auth/roles*', 'order' => 3, 'parent_id' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 4, 'name' => 'Permissions', 'slug' => 'permissions', 'http_method' => '', 'http_path' => '/auth/permissions*', 'order' => 4, 'parent_id' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 5, 'name' => 'Menu', 'slug' => 'menu', 'http_method' => '', 'http_path' => '/auth/menu*', 'order' => 5, 'parent_id' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
            ['id' => 6, 'name' => 'Extension', 'slug' => 'extension', 'http_method' => '', 'http_path' => '/auth/extensions*', 'order' => 6, 'parent_id' => 1, 'created_at' => '2021-05-16 02:06:08', 'updated_at' => null],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function roleRecords(): array
    {
        return [
            ['id' => 1, 'name' => 'Administrator', 'slug' => 'administrator', 'created_at' => '2021-05-16 02:06:08', 'updated_at' => '2021-05-16 02:06:08'],
        ];
    }
}
