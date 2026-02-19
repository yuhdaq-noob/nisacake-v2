<?php

namespace App\Services\Contracts;

/**
 * Telegram Service Contract
 *
 * Interface for Telegram service implementation.
 * Supports Dependency Inversion Principle (DIP) from SOLID principles.
 */
interface TelegramServiceContract
{
    /**
     * Send text message to Telegram
     *
     * @param string $message Message text (supports HTML formatting)
     * @return bool True if sent successfully, false otherwise
     */
    public function sendMessage(string $message): bool;

    /**
     * Get configured chat ID
     *
     * @return string The chat ID
     */
    public function getChatId(): string;

    /**
     * Get configured token (masked for security)
     *
     * @return string The masked token
     */
    public function getTokenMasked(): string;
}
