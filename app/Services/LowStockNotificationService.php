<?php

namespace App\Services;

use App\Jobs\SendTelegramReminderJob;
use App\Models\Material;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk menangani notifikasi stok menipis via Telegram
 *
 * Fitur:
 * - Deteksi bahan baku dengan stok di bawah min_stock_level
 * - Kirim notifikasi Telegram ke admin
 * - Mencatat setiap notifikasi yang dikirim
 */
class LowStockNotificationService
{
    /**
     * Check semua material dengan stok menipis dan kirim notifikasi
     *
     * @return array Result dengan info material yang dinotifikasi
     */
    public function checkAndNotifyLowStock(): array
    {
        $lowStockMaterials = $this->getLowStockMaterials();

        if ($lowStockMaterials->isEmpty()) {
            Log::channel('business')->info('Low stock check: No materials below min level');
            return ['count' => 0, 'materials' => []];
        }

        $notified = [];

        foreach ($lowStockMaterials as $material) {
            try {
                $this->notifyMaterial($material);
                $notified[] = $material->name;
            } catch (\Throwable $e) {
                Log::error('LowStockNotificationService: Failed to notify', [
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::channel('business')->info('Low stock notifications sent', [
            'count' => count($notified),
            'materials' => $notified,
        ]);

        return [
            'count' => count($notified),
            'materials' => $notified,
        ];
    }

    /**
     * Ambil semua material dengan stok di bawah min_stock_level
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getLowStockMaterials()
    {
        return Material::query()
            ->whereRaw('current_stock < min_stock_level')
            ->where('min_stock_level', '>', 0) // hanya yang punya min_stock_level
            ->orderBy('current_stock', 'asc')
            ->get();
    }

    /**
     * Kirim notifikasi untuk satu material
     *
     * @param Material $material
     * @return void
     */
    private function notifyMaterial(Material $material): void
    {
        $message = $this->buildLowStockMessage($material);

        // Dispatch job ke queue — tidak perlu order_id, gunakan null/0
        SendTelegramReminderJob::dispatch(
            orderId: 0, // gunakan 0 untuk notifikasi stok (bukan order-specific)
            payload: [
                'message' => $message,
                'type' => 'low_stock',
                'material_id' => $material->id,
                'material_name' => $material->name,
            ]
        )->onQueue('default');

        Log::channel('business')->info('Low stock notification dispatched', [
            'material_id' => $material->id,
            'material_name' => $material->name,
            'current_stock' => $material->current_stock,
            'min_stock_level' => $material->min_stock_level,
        ]);
    }

    /**
     * Bangun pesan notifikasi stok menipis
     *
     * @param Material $material
     * @return string
     */
    private function buildLowStockMessage(Material $material): string
    {
        $percentage = $material->min_stock_level > 0
            ? intval(($material->current_stock / $material->min_stock_level) * 100)
            : 0;

        return <<<MESSAGE
        ⚠️ <b>PERINGATAN STOK MENIPIS</b>

        📦 <b>Bahan Baku:</b> {$material->name}
        📉 <b>Stok Saat Ini:</b> {$material->current_stock} {$material->unit}
        📍 <b>Stok Minimum:</b> {$material->min_stock_level} {$material->unit}
        💯 <b>Persentase:</b> {$percentage}% dari minimum

        ⏰ <i>Silakan segera lakukan restock bahan baku ini!</i>
        MESSAGE;
    }
}
