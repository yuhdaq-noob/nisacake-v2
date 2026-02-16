<?php

namespace Database\Seeders;

use App\Models\OverheadSetting;
use Illuminate\Database\Seeder;

class OverheadSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Default, reasonably realistic overhead settings.
        $defaults = [
            [
                'key' => 'gas_price_per_tube',
                'name' => 'Harga Gas per Tabung',
                'value' => 22000, // Rp 22.000 per tabung
                'unit' => 'Rp/tabung',
            ],
            [
                'key' => 'gas_capacity_minutes',
                'name' => 'Kapasitas Gas (menit)',
                'value' => 620, // 10 jam pemakaian efektif
                'unit' => 'menit',
            ],
            [
                'key' => 'electricity_rate_kwh',
                'name' => 'Tarif Listrik per kWh',
                'value' => 605, // Rp 605 per kWh
                'unit' => 'Rp/kWh',
            ],
            [
                // Catatan: dipakai sebagai daya mixer (bukan oven listrik)
                'key' => 'mixer_power_kw',
                'name' => 'Daya Mixer (kW)',
                'value' => 0.16, // Mixer 160 W
                'unit' => 'kW',
            ],
            [
                'key' => 'labor_rate_per_hour',
                'name' => 'Tarif Tenaga Kerja per Jam',
                'value' => 10000, // Rp 10.000 per jam
                'unit' => 'Rp/jam',
            ],
            [
                'key' => 'depreciation_per_batch',
                'name' => 'Biaya Penyusutan per Batch',
                'value' => 800, // Rp 800 per batch
                'unit' => 'Rp/batch',
            ],
            [
                'key' => 'baking_minutes_per_batch',
                'name' => 'Durasi Panggang per Batch (menit)',
                'value' => 50, // 50 menit per batch/menu di oven gas
                'unit' => 'menit/batch',
            ],
            [
                'key' => 'mixer_minutes_per_batch',
                'name' => 'Durasi Mixer per Batch (menit)',
                'value' => 12, // 10 menit pemakaian mixer per batch
                'unit' => 'menit/batch',
            ],
            [
                'key' => 'safety_margin_percent',
                'name' => 'Safety Margin (%)',
                'value' => 5, // 5% cadangan
                'unit' => '%',
            ],
        ];

        foreach ($defaults as $data) {
            OverheadSetting::updateOrCreate(
                ['key' => $data['key']],
                $data
            );
        }
    }
}
