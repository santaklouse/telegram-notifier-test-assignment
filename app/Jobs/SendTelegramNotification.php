<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendTelegramNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $chatId;
    protected string $message;

    /**
     * Create a new job instance.
     */
    public function __construct(string $chatId, string $message)
    {
        $this->chatId = $chatId;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramService $telegramService): void
    {
        try {
            $result = $telegramService->sendMessage($this->chatId, $this->message);

            if (!$result) {
                Log::error("Failed to send Telegram notification to chat ID: {$this->chatId}");
            }
        } catch (\Exception $e) {
            Log::error("Error in SendTelegramNotification job: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
