<?php

namespace App\Console\Commands;

use App\Services\VipService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckVipStatus extends Command
{
    protected $signature = 'vip:check-qualifications
                            {--dry-run : Show what would happen without making changes}';

    protected $description = 'Check and update automatic VIP qualifications based on order history';

    public function handle(): int
    {
        $vipService = app(VipService::class);
        $dryRun = $this->option('dry-run');

        $this->info('Checking automatic VIP qualifications...');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        // Get non-VIP users who would qualify
        $nonVipUsers = \App\Models\User::where('is_vip', false)->get();
        $usersToUpgrade = collect([]);

        foreach ($nonVipUsers as $user) {
            if ($vipService->checkAutomaticQualification($user)) {
                $usersToUpgrade->push($user);
            }
        }

        $this->info("Found {$usersToUpgrade->count()} users who qualify for VIP status");

        if ($dryRun) {
            foreach ($usersToUpgrade as $user) {
                $lifetimeSpend = $user->calculateLifetimeSpend(
                    setting('vip_time_period_days', 365)
                );
                $this->line("- {$user->name} ({$user->email}) - €{$lifetimeSpend}");
            }
            $this->info('Dry run completed - no changes made');
            return Command::SUCCESS;
        }

        // Update VIP qualifications
        $updatedCount = $vipService->updateAutomaticVipQualifications();

        $this->info("Successfully upgraded {$updatedCount} users to VIP status");

        if ($updatedCount > 0) {
            Log::info("Auto-VIP command: {$updatedCount} users upgraded to VIP status");
        }

        return Command::SUCCESS;
    }
}