<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

// Resource untuk formatting data OverheadSetting
// Resource untuk format data setting overhead
class OverheadSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    // Mengubah data model menjadi array untuk response API
    public function toArray($request): array
    {
        return [
            'key' => $this->key,
            'label' => self::humanLabel($this->key),
            'value' => (float) $this->value,
            'unit' => $this->unit,
        ];
    }

    // Mengubah key setting menjadi label yang mudah dibaca
    public static function humanLabel(string $key): string
    {
        return match ($key) {
            'gas_price_per_tube' => 'Harga Gas per Tabung',
            'gas_capacity_minutes' => 'Kapasitas Gas (menit)',
            'electricity_rate_kwh' => 'Tarif Listrik per kWh',
            'safety_margin_percent' => 'Safety Margin (%)',
            'labor_rate_per_hour' => 'Tarif Tenaga Kerja per Jam',
            'depreciation_per_batch' => 'Biaya Penyusutan per Batch',
            // mixer_power_kw dipakai sebagai daya mixer
            'mixer_power_kw' => 'Daya Mixer (kW)',
            'baking_minutes_per_batch' => 'Durasi Panggang per Batch (menit)',
            'mixer_minutes_per_batch' => 'Durasi Mixer per Batch (menit)',
            default => str_replace('_', ' ', ucfirst($key)),
        };
    }
}
