<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'see-dashboard','read-all','update-all','manage-modules','read-activity',
            'read-reports','read-labels','read-pos','settings','read-settings','show-cost',
            'create-sales','read-sales','update-sales','delete-sales','email-sales',
            'create-purchases','read-purchases','update-purchases','delete-purchases','email-purchases',
            'create-quotations','read-quotations','update-quotations','delete-quotations','email-quotations',
            'create-payments','read-payments','update-payments','delete-payments','email-payments',
            'create-products','read-products','update-products','delete-products','import-products','export-products',
            'create-categories','read-categories','update-categories','delete-categories','import-categories','export-categories',
            'create-brands','read-brands','update-brands','delete-brands','import-brands','export-brands',
            'create-customers','read-customers','update-customers','delete-customers','import-customers','export-customers',
            'create-customer-groups','read-customer-groups','update-customer-groups','delete-customer-groups',
            'create-suppliers','read-suppliers','update-suppliers','delete-suppliers','import-suppliers','export-suppliers',
            'create-stores','read-stores','update-stores','delete-stores',
            'create-users','read-users','update-users','delete-users',
            'create-roles','read-roles','update-roles','delete-roles',
            'create-adjustments','read-adjustments','update-adjustments','delete-adjustments',
            'create-transfers','read-transfers','update-transfers','delete-transfers',
            'create-expenses','read-expenses','update-expenses','delete-expenses',
            'create-incomes','read-incomes','update-incomes','delete-incomes',
            'create-return-orders','read-return-orders','update-return-orders','delete-return-orders',
            'create-deliveries','read-deliveries','update-deliveries','delete-deliveries',
            'create-taxes','read-taxes','update-taxes','delete-taxes',
            'create-units','read-units','update-units','delete-units',
            'create-stock-counts','read-stock-counts','update-stock-counts','delete-stock-counts',
            'create-price-groups','read-price-groups','update-price-groups','delete-price-groups',
            'create-promotions','read-promotions','update-promotions','delete-promotions',
            'create-gift-cards','read-gift-cards','update-gift-cards','delete-gift-cards',
            'create-repair-orders','read-repair-orders','update-repair-orders','delete-repair-orders',
            'create-technicians','read-technicians','update-technicians','delete-technicians',
            'create-service-types','read-service-types','update-service-types','delete-service-types',
            'create-custom-fields','read-custom-fields','update-custom-fields','delete-custom-fields',
            'create-accounts','read-accounts','update-accounts','delete-accounts',
            'create-account-transactions','read-account-transactions','update-account-transactions','delete-account-transactions',
            'create-account-transfers','read-account-transfers','update-account-transfers','delete-account-transfers',
            'create-account-types','read-account-types','update-account-types','delete-account-types',
            'create-employees','read-employees','update-employees','delete-employees',
            'create-attendances','read-attendances','update-attendances','delete-attendances',
            'create-payrolls','read-payrolls','update-payrolls','delete-payrolls',
            'create-leaves','read-leaves','update-leaves','delete-leaves',
            'create-claims','read-claims','update-claims','delete-claims',
            'create-assets','read-assets','update-assets','delete-assets',
            'create-asset-categories','read-asset-categories','update-asset-categories','delete-asset-categories',
            'create-asset-maintenances','read-asset-maintenances','update-asset-maintenances','delete-asset-maintenances',
            'create-asset-allocations','read-asset-allocations','update-asset-allocations','delete-asset-allocations',
            'create-halls','read-halls','update-halls','delete-halls',
            'create-tables','read-tables','update-tables','delete-tables',
            'create-calendar','read-calendar','update-calendar','delete-calendar',
            'read-orders','delete-orders','delete-attachments',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->syncPermissions(Permission::all());

        $this->command->info('Created ' . Permission::count() . ' permissions and assigned to admin role.');
    }
}
