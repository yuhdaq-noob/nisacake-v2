<?php

namespace App\Services;

class MaterialService
{
	/**
	 * Hitung base unit dan harga per base unit berdasarkan unit kecil.
	 */
	public static function convertUnitPricing(string $unit, float $pricePerUnit): array
	{
		$normalizedUnit = strtolower(trim($unit));

		if (in_array($normalizedUnit, ['gram', 'g'], true)) {
			return [
				'base_unit' => 'kg',
				'price_per_base_unit' => $pricePerUnit * 1000,
			];
		}

		if ($normalizedUnit === 'ml') {
			return [
				'base_unit' => 'liter',
				'price_per_base_unit' => $pricePerUnit * 1000,
			];
		}

		if ($normalizedUnit === 'pcs') {
			return [
				'base_unit' => 'Pack',
				'price_per_base_unit' => $pricePerUnit * 100,
			];
		}

		return [
			'base_unit' => $unit,
			'price_per_base_unit' => $pricePerUnit,
		];
	}
}
