<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Initialize default system settings
        SystemSetting::initializeDefaults();
        
        $this->command->info('SystemSettingsSeeder: Default system settings initialized successfully.');
    }
}