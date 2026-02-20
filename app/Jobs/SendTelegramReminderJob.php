<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Models\Order;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    // pengaturan backoff eksponensial (detik)
    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    private int $orderId;
    private array $payload;

    public function __construct(int $orderId, array $payload)
    {
        $this->orderId = $orderId;
        $this->payload = $payload;
    }

    public function handle(TelegramService $telegram)
    {
        $order = Order::with('items.product')->find($this->orderId);

        // buat entri log antrian (attempts = 0)
        $log = NotificationLog::create([
            'channel' => 'telegram',
            'order_id' => $this->orderId,
            'payload' => $this->payload,
            'status' => 'processing',
            'attempts' => 0,
        ]);

        // bangun dan escape pesan
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

            if ($sent && $order) {
                $order->update(['is_notified' => true]);
            }

            if (! $sent) {
                // lempar exception agar antrian melakukan retry/backoff
                throw new \Exception('Telegram API returned false');
            }
        } catch (\Throwable $ex) {
            Log::warning('SendTelegramReminderJob failed', ['order_id' => $this->orderId, 'error' => $ex->getMessage()]);

            $log->update([
                'error_message' => substr($ex->getMessage(), 0, 1000),
                'attempts' => $log->attempts + 1,
                'status' => 'failed',
            ]);

            // lempar ulang agar Laravel menjalankan retry/backoff
            throw $ex;
        }
    }

    public function failed(\Throwable $exception)
    {
        // kegagalan akhir: pastikan log ada dan tandai sebagai gagal
        NotificationLog::create([
            'channel' => 'telegram',
            'order_id' => $this->orderId,
            'payload' => $this->payload,
            'response' => null,
            'attempts' => $this->attempts() ?? 0,
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
