<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@workhub.test')->firstOrFail();
        SystemSetting::updateOrCreate(['key' => 'avatar_allowed_domains'], ['value' => 'images.unsplash.com,ui-avatars.com', 'updated_by' => $admin->id]);
        SystemSetting::updateOrCreate(['key' => 'max_upload_size_mb'], ['value' => '20', 'updated_by' => $admin->id]);
    }
}
