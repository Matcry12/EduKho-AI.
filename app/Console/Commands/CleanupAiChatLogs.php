<?php

namespace App\Console\Commands;

use App\Models\AiChatLog;
use Illuminate\Console\Command;

class CleanupAiChatLogs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ai:cleanup-logs {--days=90 : Delete AI chat logs older than this many days}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old AI chat logs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = AiChatLog::query()
            ->where('created_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$deleted} AI chat log(s) older than {$days} day(s).");

        return Command::SUCCESS;
    }
}
