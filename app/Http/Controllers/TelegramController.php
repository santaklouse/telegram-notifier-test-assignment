<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected TelegramService $telegramService;

    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * Handle incoming webhook from Telegram
     *
     * @OA\Post(
     *     path="/api/telegram/webhook",
     *     summary="Handle Telegram webhook",
     *     description="Receives and processes updates from Telegram bot",
     *     operationId="telegramWebhook",
     *     tags={"Telegram"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Telegram update object",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function webhook(Request $request): Response
    {
        try {
            $update = $request->all();
            $updateData = $this->telegramService->processUpdate($update);

            if (empty($updateData) || empty($updateData['chat_id'])) {
                return response()->noContent();
            }

            $chatId = $updateData['chat_id'];
            $text = $updateData['text'] ?? '';
            $name = $updateData['name'] ?? '';

            if (str_starts_with($text, '/start')) {
                $this->handleStartCommand($chatId, $name);
            } elseif (str_starts_with($text, '/stop')) {
                $this->handleStopCommand($chatId);
            }

            return response()->noContent();
        } catch (\Exception $e) {
            Log::error('Error processing webhook: ' . $e->getMessage());
            return response()->noContent();
        }
    }

    /**
     * Handle /start command
     */
    protected function handleStartCommand(string $chatId, string $name): void
    {
        // Try to find existing user or create a new one
        $user = User::updateOrCreate(
            ['telegram_id' => $chatId],
            [
                'name' => $name,
                'email' => "$chatId@telegram.user", // Dummy email
                'subscribed' => true
            ]
        );

        $message = "👋 Привет, {$name}!\n\n";
        $message .= "Вы успешно подписались на уведомления о задачах.\n";
        $message .= "Чтобы отписаться, отправьте команду /stop.";

        $this->telegramService->sendMessage($chatId, $message);
    }

    /**
     * Handle /stop command
     */
    protected function handleStopCommand(string $chatId): void
    {
        $user = User::where('telegram_id', $chatId)->first();

        if ($user) {
            $user->update(['subscribed' => false]);

            $message = "Вы успешно отписались от уведомлений.\n";
            $message .= "Чтобы подписаться снова, отправьте команду /start.";

            $this->telegramService->sendMessage($chatId, $message);
        }
    }
}
