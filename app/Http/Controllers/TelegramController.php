<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function test(TelegramService $telegram): JsonResponse
    {
        $message = 'Test Telegram — ini pesan percobaan dari Nisacake.';
        $ok = $telegram->sendMessage($message);

        return response()->json([
            'ok' => $ok,
            'chat_id' => $telegram->getChatId(),
            'token_masked' => $telegram->getTokenMasked(),
            'message' => $ok ? 'Message sent' : 'Failed to send',
        ], $ok ? 200 : 500);
    }

    public function health(): JsonResponse
    {
        $last = NotificationLog::where('channel', 'telegram')
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'last' => $last ? $last->toArray() : null,
        ]);
    }
}
