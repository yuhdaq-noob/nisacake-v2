<?php

namespace App\Services;

use App\Services\Contracts\TelegramServiceContract;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Layanan Telegram — menangani pengiriman pesan ke API Telegram.
 * Konfigurasi token dan chat_id di `services.telegram`.
 */
class TelegramService implements TelegramServiceContract
{
    private const TELEGRAM_API_BASE = 'https://api.telegram.org/bot';
    private const SEND_MESSAGE_ENDPOINT = '/sendMessage';
    private const PARSE_MODE = 'HTML';
    private const REQUEST_TIMEOUT = 10;
    private const TOKEN_MASK_LENGTH = 10;

    private string $token;
    private string $chatId;

    public function __construct()
    {
        $this->token = $this->validateToken(config('services.telegram.token', ''));
        $this->chatId = $this->validateChatId(config('services.telegram.chat_id', ''));
    }

     /**
      * Validasi dan ambil token dari konfigurasi
      *
          * @param string $token Token dari konfigurasi
          * @return string Token yang sudah tervalidasi
          * @throws \InvalidArgumentException Jika token kosong atau tidak valid
      */
    private function validateToken(string $token): string
    {
        if (empty($token)) {
            throw new \InvalidArgumentException(
                'Telegram token is not configured. Check .env file.'
            );
        }

        return $token;
    }

     /**
      * Validasi dan ambil chat ID dari konfigurasi
      *
          * @param string $chatId Chat ID dari konfigurasi
          * @return string Chat ID yang sudah tervalidasi
          * @throws \InvalidArgumentException Jika chat ID kosong atau tidak valid
      */
    private function validateChatId(string $chatId): string
    {
        if (empty($chatId)) {
            throw new \InvalidArgumentException(
                'Telegram chat_id is not configured. Check .env file.'
            );
        }

        return $chatId;
    }

    /**
        * Kirim pesan teks ke Telegram (mendukung format HTML)
        *
        * @param string $message Isi pesan
        * @return bool True jika terkirim, false jika gagal
     */
    public function sendMessage(string $message): bool
    {
        if (empty($message)) {
            Log::warning('Telegram: Attempted to send empty message');
            return false;
        }

        // Escape pesan untuk mode HTML agar Telegram tidak menolak
        $message = $this->escapeForHtml($message);

        try {
            $url = $this->buildUrl();
            $payload = [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => self::PARSE_MODE,
            ];

            /** @var Response $response */
            $response = Http::timeout(self::REQUEST_TIMEOUT)
                ->post($url, $payload);

            return $this->handleResponse($response);

        } catch (\Throwable $e) {
            Log::error('Telegram: Exception occurred', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return false;
        }
    }

    /**
        * Tangani respons dari API Telegram
        *
        * @param Response $response Respons HTTP dari Telegram
        * @return bool True jika respons sukses, false jika gagal
     */
    private function handleResponse(Response $response): bool
    {
        if ($response->successful()) {
            Log::info('Telegram: Message sent successfully');
            return true;
        }

        /** @var array<string, mixed>|null $errorData */
        $errorData = $response->json();
        Log::warning('Telegram: API returned non-200 status', [
            'status' => $response->status(),
            'response' => $errorData ?? [],
        ]);

        return false;
    }

    /**
     * Bangun URL endpoint API Telegram
     *
     * @return string URL lengkap untuk mengirim permintaan
     */
    private function buildUrl(): string
    {
        return self::TELEGRAM_API_BASE . $this->token . self::SEND_MESSAGE_ENDPOINT;
    }

    /**
     * Escape teks untuk mode HTML Telegram
     */
    public function escapeForHtml(string $text): string
    {
        // Ubah karakter HTML khusus menjadi entitas. Telegram hanya mendukung subset HTML;
        // escaping mencegah error "can't parse message" jika teks mengandung <, >, & dll.
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Dapatkan chat ID yang dikonfigurasi
     */
    public function getChatId(): string
    {
        return $this->chatId;
    }

    /**
     * Dapatkan token (termask untuk keamanan)
     */
    public function getTokenMasked(): string
    {
        return substr($this->token, 0, 10) . '***';
    }
}
