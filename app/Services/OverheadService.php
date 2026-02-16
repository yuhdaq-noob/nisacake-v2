<?php

namespace App\Services;

use App\Models\OverheadSetting;

class OverheadService
{
    /**
     * Hitung overhead per unit (1 unit = 1 batch menu).
     *
    * Rumus (disesuaikan dengan oven gas + mixer listrik):
    *  - Biaya gas per batch   = (gas_price_per_tube / gas_capacity_minutes) × baking_minutes_per_batch
    *  - Biaya listrik per batch (mixer) = (mixer_power_kw × electricity_rate_kwh / 60) × mixer_minutes_per_batch
     *  - Biaya tenaga kerja per batch    = (labor_rate_per_hour / 60) × baking_minutes_per_batch
     *  - Biaya dasar per batch           = gas + listrik + tenaga kerja + depreciation_per_batch
     *  - Overhead per unit               = biaya dasar per batch × (1 + safety_margin_percent/100)
     */
    public static function calculateOverheadPerUnit(): float
    {
        $settings = OverheadSetting::query()->pluck('value', 'key');

        $gasPricePerTube      = (float) ($settings['gas_price_per_tube'] ?? 0);
        $gasCapacityMinutes   = (float) ($settings['gas_capacity_minutes'] ?? 0);
        $electricityRateKwh   = (float) ($settings['electricity_rate_kwh'] ?? 0);
        $laborRatePerHour      = (float) ($settings['labor_rate_per_hour'] ?? 0);
        $depreciationPerBatch  = (float) ($settings['depreciation_per_batch'] ?? 0);
        $safetyMarginPercent   = (float) ($settings['safety_margin_percent'] ?? 0);
        $mixerPowerKw          = (float) ($settings['mixer_power_kw'] ?? 0);
        $bakingMinutesPerBatch = (float) ($settings['baking_minutes_per_batch'] ?? 0);
        $mixerMinutesPerBatch  = (float) ($settings['mixer_minutes_per_batch'] ?? 0);

        // Hitung biaya overhead dasar per batch
        if ($bakingMinutesPerBatch <= 0) {
            // Jika durasi panggang belum di-set, gunakan hanya penyusutan sebagai basis
            $basePerBatch = $depreciationPerBatch;
        } else {
            // Biaya gas per menit (oven tangkring gas)
            $gasCostPerMinute = $gasCapacityMinutes > 0
                ? $gasPricePerTube / $gasCapacityMinutes
                : 0.0;

            // Biaya listrik per menit (hanya mixer)
            $electricityCostPerMinute = $mixerPowerKw > 0
                ? ($mixerPowerKw * $electricityRateKwh) / 60.0
                : 0.0;

            // Biaya tenaga kerja per menit
            $laborCostPerMinute = $laborRatePerHour > 0
                ? $laborRatePerHour / 60.0
                : 0.0;

            // Hitung biaya per batch terpisah per komponen
            $gasCostPerBatch = $gasCostPerMinute * $bakingMinutesPerBatch;
            $electricityCostPerBatch = $electricityCostPerMinute * $mixerMinutesPerBatch;
            $laborCostPerBatch = $laborCostPerMinute * $bakingMinutesPerBatch;

            $basePerBatch = $gasCostPerBatch + $electricityCostPerBatch + $laborCostPerBatch + $depreciationPerBatch;
        }

        // Satu unit = satu batch menu
        $overheadPerUnitBase = $basePerBatch;

        // Tambahkan safety margin
        $overheadPerUnit = $overheadPerUnitBase * (1 + $safetyMarginPercent / 100.0);

        return $overheadPerUnit;
    }
}
