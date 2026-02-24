<?php

namespace Tests\Unit;

use App\Services\MaterialService;
use PHPUnit\Framework\TestCase;

class MaterialServiceTest extends TestCase
{
    /**
     * Test konversi dari gram ke kilogram
     */
    public function test_convert_gram_to_kilogram(): void
    {
        $result = MaterialService::convertUnitPricing('gram', 12.0);

        $this->assertEquals('kg', $result['base_unit']);
        $this->assertEquals(12000.0, $result['price_per_base_unit']);
    }

    /**
     * Test konversi dari gram (huruf besar) ke kilogram
     */
    public function test_convert_gram_uppercase_to_kilogram(): void
    {
        $result = MaterialService::convertUnitPricing('Gram', 12.0);

        $this->assertEquals('kg', $result['base_unit']);
        $this->assertEquals(12000.0, $result['price_per_base_unit']);
    }

    /**
     * Test konversi dari 'g' ke kilogram
     */
    public function test_convert_g_abbreviation_to_kilogram(): void
    {
        $result = MaterialService::convertUnitPricing('g', 12.0);

        $this->assertEquals('kg', $result['base_unit']);
        $this->assertEquals(12000.0, $result['price_per_base_unit']);
    }

    /**
     * Test konversi dari ml ke liter
     */
    public function test_convert_ml_to_liter(): void
    {
        $result = MaterialService::convertUnitPricing('ml', 25.0);

        $this->assertEquals('liter', $result['base_unit']);
        $this->assertEquals(25000.0, $result['price_per_base_unit']);
    }

    /**
     * Test konversi dari pcs ke Pack
     */
    public function test_convert_pcs_to_pack(): void
    {
        $result = MaterialService::convertUnitPricing('pcs', 1800.0);

        $this->assertEquals('Pack', $result['base_unit']);
        $this->assertEquals(180000.0, $result['price_per_base_unit']);
    }

    /**
     * Test unit yang tidak perlu dikonversi
     */
    public function test_unit_without_conversion(): void
    {
        $result = MaterialService::convertUnitPricing('butir', 1800.0);

        $this->assertEquals('butir', $result['base_unit']);
        $this->assertEquals(1800.0, $result['price_per_base_unit']);
    }

    /**
     * Test dengan harga 0
     */
    public function test_convert_with_zero_price(): void
    {
        $result = MaterialService::convertUnitPricing('gram', 0.0);

        $this->assertEquals('kg', $result['base_unit']);
        $this->assertEquals(0.0, $result['price_per_base_unit']);
    }

    /**
     * Test dengan harga desimal
     */
    public function test_convert_with_decimal_price(): void
    {
        $result = MaterialService::convertUnitPricing('gram', 12.50);

        $this->assertEquals('kg', $result['base_unit']);
        $this->assertEquals(12500.0, $result['price_per_base_unit']);
    }

    /**
     * Test dengan whitespace di sekitar unit
     */
    public function test_convert_with_whitespace(): void
    {
        $result = MaterialService::convertUnitPricing('  gram  ', 12.0);

        $this->assertEquals('kg', $result['base_unit']);
        $this->assertEquals(12000.0, $result['price_per_base_unit']);
    }

    /**
     * Test case insensitive untuk ml
     */
    public function test_convert_ml_case_insensitive(): void
    {
        $result = MaterialService::convertUnitPricing('ML', 25.0);

        $this->assertEquals('liter', $result['base_unit']);
        $this->assertEquals(25000.0, $result['price_per_base_unit']);
    }

    /**
     * Test case insensitive untuk pcs
     */
    public function test_convert_pcs_case_insensitive(): void
    {
        $result = MaterialService::convertUnitPricing('PCS', 1800.0);

        $this->assertEquals('Pack', $result['base_unit']);
        $this->assertEquals(180000.0, $result['price_per_base_unit']);
    }
}
