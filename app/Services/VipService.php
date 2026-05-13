<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Setting;
use App\Models\VipAssignment;
use App\Models\PointsTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VipService
{
    /**
     * Check if user qualifies for automatic VIP status
     */
    public function checkAutomaticQualification(User $user): bool
    {
        $threshold = Setting::get('vip_minimum_order_threshold', 1000);
        $timePeriod = Setting::get('vip_time_period_days', 365);
        $autoQualifyEnabled = Setting::get('vip_auto_qualify_enabled', true);

        if (!$autoQualifyEnabled) {
            return false;
        }

        $lifetimeSpend = $user->calculateLifetimeSpend($timePeriod);

        return $lifetimeSpend >= $threshold;
    }

    /**
     * Assign VIP status to user
     */
    public function assignVip(User $user, string $type = 'manual', ?User $assignedBy = null, string $reason = null): bool
    {
        return DB::transaction(function () use ($user, $type, $assignedBy, $reason) {
            // Check if already VIP
            if ($user->isVip()) {
                return true;
            }

            $user->is_vip = true;
            $user->vip_type = $type;
            $user->vip_assigned_at = now();
            $user->vip_assigned_by = $assignedBy?->id;

            $user->save();

            // Log the assignment
            VipAssignment::create([
                'user_id' => $user->id,
                'assigned_by' => $assignedBy?->id,
                'action' => 'assigned',
                'assigned_at' => now(),
                'reason' => $reason ?? ($type === 'automatic' ? 'Qualified automatically' : 'Assigned by admin'),
            ]);

            Log::info("VIP status assigned to user {$user->id} ({$type})", [
                'assigned_by' => $assignedBy?->id,
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Remove VIP status from user
     */
    public function removeVip(User $user, ?User $removedBy = null, string $reason = null): bool
    {
        return DB::transaction(function () use ($user, $removedBy, $reason) {
            if (!$user->isVip()) {
                return true;
            }

            $user->is_vip = false;
            $user->vip_type = null;
            $user->vip_assigned_at = null;
            $user->vip_assigned_by = null;

            $user->save();

            // Log the removal
            VipAssignment::create([
                'user_id' => $user->id,
                'assigned_by' => $removedBy?->id,
                'action' => 'removed',
                'assigned_at' => now(),
                'reason' => $reason ?? 'Removed by admin',
            ]);

            Log::info("VIP status removed from user {$user->id}", [
                'removed_by' => $removedBy?->id,
                'reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Add points to user
     */
    public function addPoints(User $user, float $points, string $type = 'earn', string $description = null, ?Order $order = null): PointsTransaction
    {
        return DB::transaction(function () use ($user, $points, $type, $description, $order) {
            $balanceBefore = $user->points_balance ?? 0;
            $balanceAfter = $balanceBefore + $points;

            // Map custom types to valid enum values
            // Map custom types to valid enum values
            if ($type === 'redeem') {
                $mappedType = 'spent';
            } elseif ($type === 'admin_adjustment') {
                $mappedType = 'admin_adjustment';
            } else {
                $mappedType = 'earned';
            }

            $transaction = PointsTransaction::create([
                'user_id' => $user->id,
                'order_id' => $order?->id,
                'points' => $points,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'type' => $mappedType,
                'description' => $description,
            ]);

            $user->points_balance = $balanceAfter;
            $user->save();

            Log::info("Points added to user {$user->id}", [
                'points' => $points,
                'type' => $type,
                'balance_after' => $balanceAfter,
            ]);

            return $transaction;
        });
    }

    /**
     * Redeem points from user
     */
    public function redeemPoints(User $user, float $points, string $description = null, ?Order $order = null): ?PointsTransaction
    {
        if (($user->points_balance ?? 0) < $points) {
            throw new \Exception('Insufficient points balance');
        }

        return DB::transaction(function () use ($user, $points, $description, $order) {
            $balanceBefore = $user->points_balance ?? 0;
            $balanceAfter = $balanceBefore - $points;

            $transaction = PointsTransaction::create([
                'user_id' => $user->id,
                'order_id' => $order?->id,
                'points' => -$points,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'type' => 'redeem',
                'description' => $description,
            ]);

            $user->points_balance = $balanceAfter;
            $user->save();

            Log::info("Points redeemed from user {$user->id}", [
                'points' => $points,
                'balance_after' => $balanceAfter,
            ]);

            return $transaction;
        });
    }

    /**
     * Calculate points earned for order
     */
    public function calculateOrderPoints(Order $order): array
    {
        if (!$order->user) {
            return [
                'earned' => 0,
                'base_rate' => 0,
                'vip_multiplier' => 1,
            ];
        }

        $baseRate = Setting::get('points_per_euro_rate', 1.00); // Default: 1 point per €1
        $vipMultiplier = $order->user->isVip() ? 1.5 : 1; // VIP customers get 50% bonus

        $pointsEarned = $order->total * $baseRate * $vipMultiplier;

        return [
            'earned' => $pointsEarned,
            'base_rate' => $baseRate,
            'vip_multiplier' => $vipMultiplier,
        ];
    }

    /**
     * Process order points (to be called after order completion)
     */
    public function processOrderPoints(Order $order): void
    {
        if (!$order->user || $order->payment_status !== 'paid') {
            return;
        }

        $pointsCalculation = $this->calculateOrderPoints($order);

        if ($pointsCalculation['earned'] > 0) {
            $this->addPoints(
                $order->user,
                $pointsCalculation['earned'],
                'earn',
                "Points earned from order {$order->order_number}",
                $order
            );

            $order->points_earned = $pointsCalculation['earned'];
            $order->save();
        }
    }

    /**
     * Bulk send points to VIP customers
     */
    public function sendPointsToVips(float $points, string $description, ?User $admin = null): int
    {
        $vipUsers = User::where('is_vip', true)->get();
        $successCount = 0;

        foreach ($vipUsers as $user) {
            try {
                $this->addPoints($user, $points, 'admin_adjustment', $description);
                $successCount++;
            } catch (\Exception $e) {
                Log::error("Failed to add points to user {$user->id}: {$e->getMessage()}");
            }
        }

        return $successCount;
    }

    /**
     * Check and update automatic VIP qualifications (to be run via scheduled task)
     */
    public function updateAutomaticVipQualifications(): int
    {
        $updatedCount = 0;
        $nonVipUsers = User::where('is_vip', false)->get();

        foreach ($nonVipUsers as $user) {
            if ($this->checkAutomaticQualification($user)) {
                $this->assignVip($user, 'automatic', null, 'Auto-qualified based on order history');
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Get VIP statistics
     */
    public function getVipStatistics(): array
    {
        $totalUsers = User::count();
        $vipUsers = User::where('is_vip', true)->count();
        $vipManual = User::where('is_vip', true)->where('vip_type', 'manual')->count();
        $vipAutomatic = User::where('is_vip', true)->where('vip_type', 'automatic')->count();

        return [
            'total_users' => $totalUsers,
            'vip_users' => $vipUsers,
            'vip_manual' => $vipManual,
            'vip_automatic' => $vipAutomatic,
            'vip_percentage' => $totalUsers > 0 ? round(($vipUsers / $totalUsers) * 100, 2) : 0,
        ];
    }

    /**
     * Get users nearing VIP status
     */
    public function getNearVipUsers(int $thresholdPercentage = 80): \Illuminate\Database\Eloquent\Collection
    {
        $vipThreshold = Setting::get('vip_minimum_order_threshold', 1000);
        $timePeriod = Setting::get('vip_time_period_days', 365);
        $minimumSpend = ($vipThreshold * ($thresholdPercentage / 100));

        return User::where('is_vip', false)
            ->whereHas('orders', function ($query) use ($timePeriod, $minimumSpend) {
                $query->where('created_at', '>=', now()->subDays($timePeriod))
                      ->where('payment_status', 'paid')
                      ->groupBy('user_id')
                      ->havingRaw('SUM(total) >= ?', [$minimumSpend]);
            })
            ->withCount(['orders' => function ($query) use ($timePeriod) {
                $query->where('created_at', '>=', now()->subDays($timePeriod))
                      ->where('payment_status', 'paid');
            }])
            ->get();
    }
}