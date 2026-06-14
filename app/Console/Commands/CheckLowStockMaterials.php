<?php

namespace App\Console\Commands;

use App\Services\LowStockNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckLowStockMaterials extends Command
{
    protected $signature = 'materials:check-low-stock {--dry-run : Simulasi tanpa mengirim notifikasi}';

    protected $description = 'Periksa dan kirim notifikasi untuk bahan baku dengan stok menipis';

    private LowStockNotificationService $notificationService;

    public function __construct(LowStockNotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Jalankan command
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - Notifikasi tidak akan dikirim');
        }

        try {
            $this->info('🔎 Memeriksa stok bahan baku...');

            if ($dryRun) {
                // Hanya check, tidak dispatch job
                return $this->checkWithoutNotifying();
            }

            $result = $this->notificationService->checkAndNotifyLowStock();

            return $this->displayResult($result);

        } catch (Throwable $e) {
            Log::critical('CheckLowStockMaterials: Fatal error occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('❌ Fatal error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Check stok tanpa mengirim notifikasi (dry-run mode)
     */
    private function checkWithoutNotifying(): int
    {
        // Import Material langsung di method
        $materials = \App\Models\Material::query()
            ->whereRaw('current_stock < min_stock_level')
            ->where('min_stock_level', '>', 0)
            ->orderBy('current_stock', 'asc')
            ->get();

        if ($materials->isEmpty()) {
            $this->info('✅ Tidak ada bahan baku yang stok menipis');
            return self::SUCCESS;
        }

        $this->line("\n⚠️  <fg=yellow>Bahan baku dengan stok menipis:</>");
        $this->line('═══════════════════════════════════════════════════════');

        foreach ($materials as $material) {
            $percentage = $material->min_stock_level > 0
                ? intval(($material->current_stock / $material->min_stock_level) * 100)
                : 0;

            $this->line(sprintf(
                "📦 %s\n   Stok: %d %s | Min: %d %s | %d%%\n",
                $material->name,
                $material->current_stock,
                $material->unit,
                $material->min_stock_level,
                $material->unit,
                $percentage
            ));
        }

        $this->line('═══════════════════════════════════════════════════════');
        $this->info("✓ Total: {$materials->count()} bahan baku");
        $this->line('(Notifikasi tidak akan dikirim karena mode DRY RUN)');

        return self::SUCCESS;
    }

    /**
     * Tampilkan hasil notifikasi
     */
    private function displayResult(array $result): int
    {
        if ($result['count'] === 0) {
            $this->info('✅ Semua bahan baku stok aman');
            return self::SUCCESS;
        }

        $this->line("\n📤 <fg=green>Notifikasi telah dikirim untuk:</>");
        $this->line('═══════════════════════════════════════════════════════');

        foreach ($result['materials'] as $material) {
            $this->line("✓ {$material}");
        }

        $this->line('═══════════════════════════════════════════════════════');
        $this->info("✅ Total: {$result['count']} notifikasi dikirim");

        return self::SUCCESS;
    }
}
