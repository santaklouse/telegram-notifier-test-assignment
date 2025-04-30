<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TaskService
{
    private string $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = 'https://jsonplaceholder.typicode.com';
    }

    /**
     * Get tasks from external API
     *
     * @return array Array of tasks
     */
    public function getTasks(): array
    {
        try {
            $response = Http::get("{$this->apiBaseUrl}/todos");

            if (!$response->successful()) {
                Log::error('External API error: ' . $response->body());
                return [];
            }

            $allTasks = $response->json();

            // Filter tasks where completed = false and userId <= 5
            $filteredTasks = array_filter($allTasks, function ($task) {
                return $task['completed'] === false && $task['userId'] <= 5;
            });

            return array_values($filteredTasks);
        } catch (\Exception $e) {
            Log::error('Error fetching tasks: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Format tasks for Telegram message
     *
     * @param array $tasks List of tasks
     * @return string Formatted message
     */
    public function formatTasksForTelegram(array $tasks): string
    {
        if (empty($tasks)) {
            return "No pending tasks found.";
        }

        // Group tasks by userId
        $tasksByUser = [];
        $total = 0;
        foreach ($tasks as $task) {
            $userId = $task['userId'];
            if (!isset($tasksByUser[$userId])) {
                $tasksByUser[$userId] = [];
            }
            $tasksByUser[$userId][] = $task;
            $total++;
        }

        $message = "<b>📋 Pending Tasks: $total</b>\n\n";

        // Format tasks by user
        foreach ($tasksByUser as $userId => $userTasks) {
            $message .= "<b>User $userId tasks:</b> (<a href='{$this->apiBaseUrl}/todos?userId=$userId'>Link to source</a>)\n";

            foreach ($userTasks as $index => $task) {
                $message .= "  • Task #{$task['id']}: {$task['title']}\n";
            }
            $message .= "\n";
        }

        return $message;
    }
}
