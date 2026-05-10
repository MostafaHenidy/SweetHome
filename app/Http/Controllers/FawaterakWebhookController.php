<?php

namespace App\Http\Controllers;

use App\Services\FawaterakService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FawaterakWebhookController extends Controller
{
    public function __construct(
        protected FawaterakService $fawaterakService
    ) {}

    public function handle(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('Fawaterak webhook received', ['payload' => $payload]);

            $success = $this->fawaterakService->handleWebhook($payload);

            if ($success) {
                return response()->json(['status' => 'success'], 200);
            } else {
                return response()->json(['status' => 'ignored'], 200);
            }
        } catch (\Exception $e) {
            Log::error('Fawaterak webhook error', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}