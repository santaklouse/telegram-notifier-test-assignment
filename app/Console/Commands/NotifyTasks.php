<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramNotification;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch tasks from API and send notifications to subscribed users';

    /**
     * Execute the console command.
     */
    public function handle(TaskService $taskService): int
    {
        $this->info('Fetching tasks from external API...');

        try {
            // Get tasks from external API
            $tasks = $taskService->getTasks();

            if (empty($tasks)) {
                $this->warn('No pending tasks found.');
                return Command::SUCCESS;
            }

            $this->info('Found ' . count($tasks) . ' pending tasks.');

            // Format tasks for Telegram message
            $formattedMessage = $taskService->formatTasksForTelegram($tasks);

            // Get all subscribed users
            $users = User::subscribed()->whereNotNull('telegram_id')->get();

            if ($users->isEmpty()) {
                $this->warn('No subscribed users found.');
                return Command::SUCCESS;
            }

            $this->info('Sending notifications to ' . $users->count() . ' users...');

            // Dispatch jobs for each user
            foreach ($users as $user) {
                SendTelegramNotification::dispatch($user->telegram_id, $formattedMessage);
                $this->line("Queued notification for user: {$user->name}");
            }

            $this->info('All notifications have been queued successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('NotifyTasks command error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
