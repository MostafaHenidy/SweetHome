<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PointsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'points_balance' => $user->points_balance,
            'points_value_in_euros' => $user->points_value_in_euros,
        ]);
    }

    public function transactions(Request $request)
    {
        return response()->json(
            $request->user()
                ->pointsTransactions()
                ->latest()
                ->paginate($request->integer('per_page', 15))
        );
    }
}
