<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderReminders extends Command
{
    protected $signature = 'orders:send-reminders {--dry-run : Simulate sending without updating database}';
    protected $description = 'Kirim notifikasi Telegram untuk pesanan yang dijadwalkan besok dan belum dinotifikasi';

    private TelegramService $telegram;
    private int $successCount = 0;
    private int $failureCount = 0;

    public function __construct(TelegramService $telegram)
    {
        parent::__construct();
        $this->telegram = $telegram;
    }

    /**
     * Jalankan perintah konsol
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No database changes will be made');
        }

        try {
            return $this->sendReminders($dryRun);
        } catch (Throwable $e) {
            Log::critical('SendOrderReminders: Fatal error occurred', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error('❌ Fatal error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Kirim pengingat untuk pesanan yang terjadwal
     */
    private function sendReminders(bool $dryRun = false): int
    {
        $tomorrow = Carbon::tomorrow()->startOfDay();

        // Query pesanan yang dijadwalkan besok dan belum dinotifikasi
        $orders = Order::query()
            ->whereDate('scheduled_at', $tomorrow)
            ->where('status', '!=', 'completed')
            ->where('is_notified', false)
            ->with(['items.product'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('ℹ️  Tidak ada pesanan untuk diingatkan besok.');
            return Command::SUCCESS;
        }

        $this->info("📨 Memproses " . $orders->count() . " pesanan...\n");

        foreach ($orders as $order) {
            $this->processOrder($order, $dryRun);
        }

        return $this->displaySummary();
    }

    /**
     * Proses satu per satu pesanan
     */
    private function processOrder(Order $order, bool $dryRun = false): void
    {
        try {
            $message = $this->buildMessage($order);

            $this->line("📤 Mengirim pesanan #{$order->id} ({$order->customer_name})...");

            // Kirim job untuk mengirim pesan (mengizinkan retry/backoff dan non-blocking)
            if ($dryRun) {
                $this->line('   [DRY RUN] Dispatching job (no DB changes)');
                // simulasi dengan tidak mendispatch
            } else {
                // masukkan job ke antrian — worker akan menandai order sebagai dinotifikasi saat sukses
                \App\Jobs\SendTelegramReminderJob::dispatch($order->id, ['message' => $message])->onQueue('default');
                $this->line('   ⏱ Dispatched job to queue');
            }

            $this->successCount++;
            $this->line('   <fg=green>✓ Dispatched</fd>');

        } catch (Throwable $e) {
            $this->failureCount++;
            $this->line("   <fg=red>✗ Gagal: " . $e->getMessage() . "</fg>");

            Log::warning('SendOrderReminders: Failed to send reminder', [
                'order_id' => $order->id,
                'customer' => $order->customer_name,
                'reason' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Bangun pesan notifikasi
     */
    private function buildMessage(Order $order): string
    {
        $scheduledDate = Carbon::parse($order->scheduled_at)
            ->translatedFormat('l, d F Y H:i');

        $itemsList = $order->items
            ->map(fn ($item) => "• " . ($item->product?->name ?? 'Produk tidak diketahui') . " ({$item->quantity} pcs)")
            ->join("\n");

        return <<<MESSAGE
        ⚠️ <b>PENGINGAT PRODUKSI (H-1)</b>

        🆔 <b>Order ID:</b> #{$order->id}
        👤 <b>Pelanggan:</b> {$order->customer_name}
        📅 <b>Jadwal:</b> {$scheduledDate}
        📋 <b>Detail Pesanan:</b>
        {$itemsList}

        <i>Mohon siapkan bahan baku sekarang!</i>
        MESSAGE;
    }

    /**
     * Tampilkan ringkasan hasil perintah
     */
    private function displaySummary(): int
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info("✅ Berhasil: {$this->successCount}");
        $this->info("❌ Gagal: {$this->failureCount}");
        $this->info('═══════════════════════════════════════');

        return ($this->failureCount === 0) ? Command::SUCCESS : Command::FAILURE;
    }
}