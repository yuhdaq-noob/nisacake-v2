<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk mengirim notifikasi Telegram saat stok material menipis
 */
class SendLowStockNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    // pengaturan backoff eksponensial (detik)
    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    private int $materialId;
    private array $payload;

    public function __construct(int $materialId, array $payload)
    {
        $this->materialId = $materialId;
        $this->payload = $payload;
    }

    public function handle(TelegramService $telegram)
    {
        // buat entri log antrian (attempts = 0)
        $log = NotificationLog::create([
            'channel' => 'telegram',
            'order_id' => null, // Low stock notification tidak terkait order
            'payload' => array_merge($this->payload, ['material_id' => $this->materialId]),
            'status' => 'processing',
            'attempts' => 0,
        ]);

        // ambil pesan dari payload
        $message = $this->payload['message'] ?? '';

        try {
            $sent = $telegram->sendMessage($message);

            // perbarui log
            $log->update([
                'response' => ['ok' => $sent],
                'attempts' => $log->attempts + 1,
                'status' => $sent ? 'sent' : 'failed',
                'sent_at' => $sent ? now() : null,
            ]);

            if (! $sent) {
                // lempar exception agar antrian melakukan retry/backoff
                throw new \Exception('Telegram API returned false');
            }
        } catch (\Throwable $ex) {
            Log::warning('SendLowStockNotificationJob failed', [
                'material_id' => $this->materialId,
                'error' => $ex->getMessage(),
            ]);

            $log->update([
                'error_message' => substr($ex->getMessage(), 0, 1000),
                'attempts' => $log->attempts + 1,
                'status' => 'failed',
            ]);

            // lempar ulang agar Laravel menjalankan retry/backoff
            throw $ex;
        }
    }
}
