<?php

namespace Tests\Unit;

use App\Models\OverheadSetting;
use App\Services\OverheadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OverheadServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test calculation overhead with default settings.
     */
    public function test_calculate_overhead_with_default_settings(): void
    {
        $this->seed(\Database\Seeders\OverheadSettingSeeder::class);
        $overhead = OverheadService::calculateOverheadPerUnit();

        $this->assertEquals(11473.23, round($overhead, 2));
    }

    /**
     * Test calculation overhead with zero baking minutes.
     */
    public function test_calculate_overhead_with_zero_baking_minutes(): void
    {
        OverheadSetting::updateOrCreate(['key' => 'gas_price_per_tube'], ['name' => 'Harga Gas per Tabung', 'value' => 22000, 'unit' => 'Rp/tabung']);
        OverheadSetting::updateOrCreate(['key' => 'gas_capacity_minutes'], ['name' => 'Kapasitas Gas (menit)', 'value' => 620, 'unit' => 'menit']);
        OverheadSetting::updateOrCreate(['key' => 'baking_minutes_per_batch'], ['name' => 'Durasi Panggang per Batch', 'value' => 0, 'unit' => 'menit/batch']);
        OverheadSetting::updateOrCreate(['key' => 'depreciation_per_batch'], ['name' => 'Biaya Penyusutan per Batch', 'value' => 800, 'unit' => 'Rp/batch']);
        OverheadSetting::updateOrCreate(['key' => 'safety_margin_percent'], ['name' => 'Safety Margin (%)', 'value' => 5, 'unit' => '%']);

        $overhead = OverheadService::calculateOverheadPerUnit();

        $this->assertEquals(840.0, round($overhead, 2));
    }

    /**
     * Test calculation overhead with zero gas price.
     */
    public function test_calculate_overhead_with_zero_gas_price(): void
    {
        OverheadSetting::updateOrCreate(['key' => 'gas_price_per_tube'], ['name' => 'Harga Gas per Tabung', 'value' => 0, 'unit' => 'Rp/tabung']);
        OverheadSetting::updateOrCreate(['key' => 'gas_capacity_minutes'], ['name' => 'Kapasitas Gas (menit)', 'value' => 620, 'unit' => 'menit']);
        OverheadSetting::updateOrCreate(['key' => 'baking_minutes_per_batch'], ['name' => 'Durasi Panggang per Batch', 'value' => 50, 'unit' => 'menit/batch']);
        OverheadSetting::updateOrCreate(['key' => 'electricity_rate_kwh'], ['name' => 'Tarif Listrik per kWh', 'value' => 605, 'unit' => 'Rp/kWh']);
        OverheadSetting::updateOrCreate(['key' => 'mixer_power_kw'], ['name' => 'Daya Mixer (kW)', 'value' => 0.16, 'unit' => 'kW']);
        OverheadSetting::updateOrCreate(['key' => 'labor_rate_per_hour'], ['name' => 'Tarif Tenaga Kerja per Jam', 'value' => 10000, 'unit' => 'Rp/jam']);
        OverheadSetting::updateOrCreate(['key' => 'depreciation_per_batch'], ['name' => 'Biaya Penyusutan per Batch', 'value' => 800, 'unit' => 'Rp/batch']);
        OverheadSetting::updateOrCreate(['key' => 'mixer_minutes_per_batch'], ['name' => 'Durasi Mixer per Batch', 'value' => 12, 'unit' => 'menit/batch']);
        OverheadSetting::updateOrCreate(['key' => 'safety_margin_percent'], ['name' => 'Safety Margin (%)', 'value' => 5, 'unit' => '%']);

        $overhead = OverheadService::calculateOverheadPerUnit();

        $this->assertEquals(9610.33, round($overhead, 2));
    }

    /**
     * Test calculation overhead with zero safety margin.
     */
    public function test_calculate_overhead_with_zero_safety_margin(): void
    {
        $this->seed(\Database\Seeders\OverheadSettingSeeder::class);
        OverheadSetting::updateOrCreate(['key' => 'safety_margin_percent'], ['name' => 'Safety Margin (%)', 'value' => 0, 'unit' => '%']);

        $overhead = OverheadService::calculateOverheadPerUnit();

        $this->assertEquals(10926.89, round($overhead, 2));
    }

    /**
     * Test calculation overhead with zero labor rate.
     */
    public function test_calculate_overhead_with_zero_labor_rate(): void
    {
        OverheadSetting::updateOrCreate(['key' => 'gas_price_per_tube'], ['name' => 'Harga Gas per Tabung', 'value' => 22000, 'unit' => 'Rp/tabung']);
        OverheadSetting::updateOrCreate(['key' => 'gas_capacity_minutes'], ['name' => 'Kapasitas Gas (menit)', 'value' => 620, 'unit' => 'menit']);
        OverheadSetting::updateOrCreate(['key' => 'baking_minutes_per_batch'], ['name' => 'Durasi Panggang per Batch', 'value' => 50, 'unit' => 'menit/batch']);
        OverheadSetting::updateOrCreate(['key' => 'electricity_rate_kwh'], ['name' => 'Tarif Listrik per kWh', 'value' => 605, 'unit' => 'Rp/kWh']);
        OverheadSetting::updateOrCreate(['key' => 'mixer_power_kw'], ['name' => 'Daya Mixer (kW)', 'value' => 0.16, 'unit' => 'kW']);
        OverheadSetting::updateOrCreate(['key' => 'labor_rate_per_hour'], ['name' => 'Tarif Tenaga Kerja per Jam', 'value' => 0, 'unit' => 'Rp/jam']);
        OverheadSetting::updateOrCreate(['key' => 'depreciation_per_batch'], ['name' => 'Biaya Penyusutan per Batch', 'value' => 800, 'unit' => 'Rp/batch']);
        OverheadSetting::updateOrCreate(['key' => 'mixer_minutes_per_batch'], ['name' => 'Durasi Mixer per Batch', 'value' => 12, 'unit' => 'menit/batch']);
        OverheadSetting::updateOrCreate(['key' => 'safety_margin_percent'], ['name' => 'Safety Margin (%)', 'value' => 5, 'unit' => '%']);

        $overhead = OverheadService::calculateOverheadPerUnit();

        $this->assertEquals(2723.23, round($overhead, 2));
    }

    /**
     * Test calculation overhead with zero mixer power.
     */
    public function test_calculate_overhead_with_zero_mixer_power(): void
    {
        OverheadSetting::updateOrCreate(['key' => 'gas_price_per_tube'], ['name' => 'Harga Gas per Tabung', 'value' => 22000, 'unit' => 'Rp/tabung']);
        OverheadSetting::updateOrCreate(['key' => 'gas_capacity_minutes'], ['name' => 'Kapasitas Gas (menit)', 'value' => 620, 'unit' => 'menit']);
        OverheadSetting::updateOrCreate(['key' => 'baking_minutes_per_batch'], ['name' => 'Durasi Panggang per Batch', 'value' => 50, 'unit' => 'menit/batch']);
        OverheadSetting::updateOrCreate(['key' => 'electricity_rate_kwh'], ['name' => 'Tarif Listrik per kWh', 'value' => 605, 'unit' => 'Rp/kWh']);
        OverheadSetting::updateOrCreate(['key' => 'mixer_power_kw'], ['name' => 'Daya Mixer (kW)', 'value' => 0, 'unit' => 'kW']);
        OverheadSetting::updateOrCreate(['key' => 'labor_rate_per_hour'], ['name' => 'Tarif Tenaga Kerja per Jam', 'value' => 10000, 'unit' => 'Rp/jam']);
        OverheadSetting::updateOrCreate(['key' => 'depreciation_per_batch'], ['name' => 'Biaya Penyusutan per Batch', 'value' => 800, 'unit' => 'Rp/batch']);
        OverheadSetting::updateOrCreate(['key' => 'mixer_minutes_per_batch'], ['name' => 'Durasi Mixer per Batch', 'value' => 12, 'unit' => 'menit/batch']);
        OverheadSetting::updateOrCreate(['key' => 'safety_margin_percent'], ['name' => 'Safety Margin (%)', 'value' => 5, 'unit' => '%']);

        $overhead = OverheadService::calculateOverheadPerUnit();

        $this->assertEquals(11452.90, round($overhead, 2));
    }

    /**
     * Test calculation overhead with high safety margin.
     */
    public function test_calculate_overhead_with_high_safety_margin(): void
    {
        $this->seed(\Database\Seeders\OverheadSettingSeeder::class);
        OverheadSetting::updateOrCreate(['key' => 'safety_margin_percent'], ['name' => 'Safety Margin (%)', 'value' => 20, 'unit' => '%']);

        $overhead = OverheadService::calculateOverheadPerUnit();

        $this->assertEquals(13112.26, round($overhead, 2));
    }

    /**
     * Test overhead calculation returns positive value.
     */
    public function test_overhead_is_positive(): void
    {
        $this->seed(\Database\Seeders\OverheadSettingSeeder::class);
        $overhead = OverheadService::calculateOverheadPerUnit();

        $this->assertGreaterThan(0, $overhead);
    }

    /**
     * Test overhead calculation is greater than depreciation.
     */
    public function test_overhead_greater_than_depreciation(): void
    {
        $this->seed(\Database\Seeders\OverheadSettingSeeder::class);
        $overhead = OverheadService::calculateOverheadPerUnit();

        $this->assertGreaterThan(800, $overhead);
    }
}
