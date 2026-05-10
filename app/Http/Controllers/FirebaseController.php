<?php

namespace App\Http\Controllers;

use App\Models\FirebaseToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FirebaseController extends Controller
{
    /**
     * Register a device token for the authenticated user.
     */
    public function registerToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
            'device_type' => 'nullable|string|in:ios,android,web',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        try {
            // Remove any existing token with the same device token
            FirebaseToken::where('device_token', $request->device_token)->delete();

            // Create new token record
            $token = FirebaseToken::create([
                'user_id' => Auth::id(),
                'device_token' => $request->device_token,
                'device_type' => $request->device_type,
                'device_name' => $request->device_name,
                'last_used_at' => now(),
            ]);

            Log::info('Firebase token registered', [
                'user_id' => Auth::id(),
                'device_token' => substr($request->device_token, 0, 20).'...',
                'device_type' => $request->device_type,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device token registered successfully',
                'token_id' => $token->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to register Firebase token: '.$e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register device token',
            ], 500);
        }
    }

    /**
     * Remove a device token.
     */
    public function unregisterToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        try {
            $deleted = FirebaseToken::where('user_id', Auth::id())
                ->where('device_token', $request->device_token)
                ->delete();

            Log::info('Firebase token unregistered', [
                'user_id' => Auth::id(),
                'device_token' => substr($request->device_token, 0, 20).'...',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device token removed successfully',
                'deleted' => $deleted > 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to unregister Firebase token: '.$e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove device token',
            ], 500);
        }
    }

    /**
     * Get user's registered device tokens.
     */
    public function getTokens()
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        try {
            $tokens = FirebaseToken::where('user_id', Auth::id())
                ->orderBy('last_used_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'tokens' => $tokens,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get Firebase tokens: '.$e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve device tokens',
            ], 500);
        }
    }
}
