<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $apiBaseUrl;
    private string $botToken;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->apiBaseUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Send a message to a Telegram user
     *
     * @param string $chatId Telegram user's chat ID
     * @param string $text Message text
     * @return array|null Response from Telegram API
     */
    public function sendMessage(string $chatId, string $text): ?array
    {
        try {
            $response = Http::post("{$this->apiBaseUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API error: ' . $response->body());
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Error sending Telegram message: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get updates from Telegram webhook
     *
     * @param array $update The update from Telegram
     * @return array Processed update data
     */
    public function processUpdate(array $update): array
    {
        if (!isset($update['message'])) {
            return [];
        }

        $message = $update['message'];
        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '';
        $firstName = $message['chat']['first_name'] ?? '';
        $lastName = $message['chat']['last_name'] ?? '';
        $username = $message['chat']['username'] ?? '';

        $name = trim("$firstName $lastName");
        if (empty($name) && !empty($username)) {
            $name = $username;
        } elseif (empty($name)) {
            $name = "User$chatId";
        }

        return [
            'chat_id' => $chatId,
            'text' => $text,
            'name' => $name,
        ];
    }
}
