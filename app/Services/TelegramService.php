<?php

namespace App\Services;

use App\Services\Contracts\TelegramServiceContract;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Telegram Service
 *
 * SOLID Principles Applied:
 * 1. Single Responsibility: Only handles Telegram API communication
 * 2. Open/Closed: Easy to extend for other message types
 * 3. Dependency Injection: Token and chat_id from config
 * 4. Liskov Substitution: Implements TelegramServiceContract interface
 * 5. Interface Segregation: Minimal interface with only essential methods
 * Dependency Inversion: Classes depend on TelegramServiceContract abstraction
 *
 * Best Practices:
 * - Strong typing with return types and parameter types
 * - Proper error handling and logging
 * - Configuration-driven (no hardcoded values)
 * - Immutable configuration values
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
     * Validate and get token from config
     *
     * @param string $token The token value from config
     * @return string The validated token
     * @throws \InvalidArgumentException If token is invalid
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
     * Validate and get chat ID from config
     *
     * @param string $chatId The chat ID value from config
     * @return string The validated chat ID
     * @throws \InvalidArgumentException If chat ID is invalid
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
     * Send text message to Telegram
     *
     * @param string $message Message text (supports HTML formatting)
     * @return bool True if sent successfully, false otherwise
     */
    public function sendMessage(string $message): bool
    {
        if (empty($message)) {
            Log::warning('Telegram: Attempted to send empty message');
            return false;
        }

        // Escape message for HTML parse_mode to avoid Telegram rejecting it
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
     * Handle Telegram API response
     *
     * @param Response $response The HTTP response from Telegram API
     * @return bool True if response was successful, false otherwise
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
     * Build Telegram API URL
     *
     * @return string The complete Telegram API endpoint URL
     */
    private function buildUrl(): string
    {
        return self::TELEGRAM_API_BASE . $this->token . self::SEND_MESSAGE_ENDPOINT;
    }

    /**
     * Escape text for Telegram HTML parse mode
     */
    public function escapeForHtml(string $text): string
    {
        // Convert special HTML chars to entities. Telegram supports a small subset of HTML;
        // escaping avoids `Bad Request: can't parse message` when content contains <, >, & etc.
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Get configured chat ID
     */
    public function getChatId(): string
    {
        return $this->chatId;
    }

    /**
     * Get configured token (masked for security)
     */
    public function getTokenMasked(): string
    {
        return substr($this->token, 0, 10) . '***';
    }
}
