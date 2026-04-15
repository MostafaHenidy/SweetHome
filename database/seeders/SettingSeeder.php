<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'SweetHome Milano', 'group' => 'general'],
            ['key' => 'site_email', 'value' => 'info@sweethome.it', 'group' => 'general'],
            ['key' => 'site_phone', 'value' => '328 899 5628', 'group' => 'general'],
            ['key' => 'site_address', 'value' => 'Via Carlo Espinasse 104/a, 20156 Milano MI', 'group' => 'general'],
            ['key' => 'vat_number', 'value' => '', 'group' => 'general'],
            ['key' => 'tax_code', 'value' => '', 'group' => 'general'],
            ['key' => 'shipping_cost', 'value' => 9.90, 'group' => 'shipping'],
            ['key' => 'free_shipping_threshold', 'value' => 150.00, 'group' => 'shipping'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'group' => $setting['group']]
            );
        }
    }
}
