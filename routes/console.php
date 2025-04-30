<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('telegram:set-webhook-endpoint', function () {
    $botToken = config('services.telegram.bot_token');
    $webhookUrl = route('telegram.webhook');

    if (empty($botToken)) {
        $this->error('Bot token is not set in the configuration.');
        return;
    }

    $response = Http::get("https://api.telegram.org/bot{$botToken}/setWebhook", [
        'url' => $webhookUrl,
    ]);

    if ($response->successful()) {
        $this->info('Webhook endpoint set successfully.');
    } else {
        $this->error('Failed to set webhook endpoint: ' . $response->body());
    }
})->purpose('Set the webhook endpoint for the Telegram bot. Make sure to replace <your_bot_token> with your actual bot token.');
