<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PointsConfiguration;
use App\Models\PointsTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsService
{
    public function calculatePointsForOrder(Order $order): int
    {
        $configuration = PointsConfiguration::active();
        $eligibleTotal = max(0, (float) $order->total - (float) $order->shipping_cost);
        $pointsValue = $eligibleTotal * ((float) $configuration->points_per_order_percentage / 100) * (float) $configuration->points_per_euro;

        return (int) floor($pointsValue);
    }

    public function awardPointsForOrder(Order $order): void
    {
        if (! $order->user_id) {
            return;
        }

        if ($order->pointsTransactions()->where('type', 'earned')->exists()) {
            return;
        }

        $points = $this->calculatePointsForOrder($order);

        if ($points <= 0) {
            return;
        }

        DB::transaction(function () use ($order, $points): void {
            PointsTransaction::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'points' => $points,
                'type' => 'earned',
                'description' => "Punti guadagnati con ordine {$order->order_number}",
            ]);

            $order->user()->increment('points_balance', $points);
        });
    }

    public function adjustPoints(User $user, int $points, string $description = 'Adeguamento manuale punti'): PointsTransaction
    {
        return DB::transaction(function () use ($user, $points, $description): PointsTransaction {
            $effectivePoints = $points < 0 ? max($points, -1 * (int) $user->points_balance) : $points;

            $transaction = PointsTransaction::create([
                'user_id' => $user->id,
                'points' => $effectivePoints,
                'type' => 'admin_adjustment',
                'description' => $description,
            ]);

            if ($effectivePoints >= 0) {
                $user->increment('points_balance', $effectivePoints);
            } else {
                $user->decrement('points_balance', abs($effectivePoints));
            }

            return $transaction;
        });
    }

    public function spendPoints(User $user, int $points, ?Order $order = null, string $description = 'Punti utilizzati'): PointsTransaction
    {
        if ($points <= 0 || $user->points_balance < $points) {
            throw new \InvalidArgumentException('Saldo punti insufficiente.');
        }

        return DB::transaction(function () use ($user, $points, $order, $description): PointsTransaction {
            $transaction = PointsTransaction::create([
                'user_id' => $user->id,
                'order_id' => $order?->id,
                'points' => -$points,
                'type' => 'spent',
                'description' => $description,
            ]);

            $user->decrement('points_balance', $points);

            return $transaction;
        });
    }

    public function convertPointsToEuros(int $points): float
    {
        $pointsPerEuro = max((float) PointsConfiguration::active()->points_per_euro, 0.01);

        return round($points / $pointsPerEuro, 2);
    }

    public function convertEurosToPoints(float $euros): int
    {
        return (int) ceil($euros * (float) PointsConfiguration::active()->points_per_euro);
    }
}
