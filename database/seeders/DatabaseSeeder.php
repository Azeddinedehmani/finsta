<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * This improved version eliminates data redundancy and ensures proper seeding order.
     */
    public function run(): void
    {
        // Phase 1: Foundation Data (Essential system configuration)
        $this->command->info('Phase 1: Initializing foundation data...');
        $this->call([
            SystemSettingsSeeder::class,
        ]);

        // Phase 2: Core Business Data (Users, Categories, Suppliers)
        $this->command->info('Phase 2: Creating core business data...');
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            SupplierSeeder::class,
        ]);

        // Phase 3: Product Data (Depends on categories and suppliers)
        $this->command->info('Phase 3: Setting up product catalog...');
        $this->call([
            ProductSeeder::class,
        ]);

        // Phase 4: Client Data
        $this->command->info('Phase 4: Creating client database...');
        $this->call([
            ClientSeeder::class,
        ]);

        // Phase 5: Transactional Data (Depends on products and clients)
        $this->command->info('Phase 5: Generating transactional data...');
        $this->call([
            PrescriptionSeeder::class,
            PurchaseSeeder::class,
            SaleSeeder::class,
        ]);

        // Phase 6: System Features (Notifications, etc.)
        $this->command->info('Phase 6: Setting up system features...');
        $this->call([
            NotificationSeeder::class,
        ]);

        // Note: BasicDataSeeder has been integrated into the above seeders
        // to eliminate data redundancy and ensure consistency.

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Users: ' . \App\Models\User::count());
        $this->command->info('   - Categories: ' . \App\Models\Category::count());
        $this->command->info('   - Suppliers: ' . \App\Models\Supplier::count());
        $this->command->info('   - Products: ' . \App\Models\Product::count());
        $this->command->info('   - Clients: ' . \App\Models\Client::count());
        $this->command->info('   - Sales: ' . \App\Models\Sale::count());
        $this->command->info('   - Prescriptions: ' . \App\Models\Prescription::count());
        $this->command->info('   - Purchases: ' . \App\Models\Purchase::count());
        $this->command->info('   - Notifications: ' . \App\Models\Notification::count());
    }
}