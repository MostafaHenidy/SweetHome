<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Services\BulkQRService;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class QRCodeController extends Controller
{
    /**
     * Handle QR code scanning - short redirect route.
     */
    public function scan(Request $request, string $token)
    {
        $qrService = app(QRCodeService::class);
        $tokenRecord = $qrService->validateToken($token);

        if (! $tokenRecord) {
            return view('qr.error', [
                'error' => 'Invalid or expired QR code',
                'message' => 'This QR code is no longer valid or has expired.',
            ]);
        }

        // If user is not logged in, store token in session and redirect to login
        if (! Auth::check()) {
            Session::put('pending_qr_token', $token);

            return redirect()->route('login')->with('qr_notice', 'Please login to redeem your coupon.');
        }

        // Check if user has already redeemed this coupon
        $alreadyRedeemed = $tokenRecord->coupon->qrTokens()
            ->where('user_id', Auth::id())
            ->whereNotNull('redeemed_at')
            ->exists();

        if ($alreadyRedeemed) {
            return view('qr.error', [
                'error' => 'Already Redeemed',
                'message' => 'You have already redeemed this coupon.',
            ]);
        }

        // Redeem the token
        $success = $qrService->redeemToken($tokenRecord, Auth::id());

        if ($success) {
            return view('qr.success', [
                'coupon' => $tokenRecord->coupon,
                'points' => (int) $tokenRecord->coupon->value,
                'newBalance' => Auth::user()->points_balance,
            ]);
        }

        return view('qr.error', [
            'error' => 'Redemption Failed',
            'message' => 'Unable to redeem coupon. Please try again.',
        ]);
    }

    /**
     * Handle post-login QR token redemption.
     */
    public function handlePostLoginRedemption()
    {
        $token = Session::get('pending_qr_token');

        if (! $token) {
            return redirect()->route('home');
        }

        // Clear the pending token
        Session::forget('pending_qr_token');

        // Redirect to the scan endpoint which will handle the redemption
        return redirect()->route('qr.scan', ['token' => $token]);
    }

    /**
     * API endpoint for QR code scanning (for mobile apps).
     */
    public function apiScan(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $qrService = app(QRCodeService::class);
        $tokenRecord = $qrService->validateToken($request->token);

        if (! $tokenRecord) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid or expired QR code',
            ], 400);
        }

        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Authentication required',
                'redirect' => route('login'),
            ], 401);
        }

        // Check if user has already redeemed this coupon
        $alreadyRedeemed = $tokenRecord->coupon->qrTokens()
            ->where('user_id', Auth::id())
            ->whereNotNull('redeemed_at')
            ->exists();

        if ($alreadyRedeemed) {
            return response()->json([
                'success' => false,
                'error' => 'You have already redeemed this coupon',
            ], 400);
        }

        // Redeem the token
        $success = $qrService->redeemToken($tokenRecord, Auth::id());

        if ($success) {
            return response()->json([
                'success' => true,
                'coupon' => [
                    'code' => $tokenRecord->coupon->code,
                    'points' => (int) $tokenRecord->coupon->value,
                ],
                'newBalance' => Auth::user()->points_balance,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Unable to redeem coupon',
        ], 500);
    }

    /**
     * Handle bulk QR code export for admin.
     */
    public function bulkExport(Request $request)
    {
        $request->validate([
            'coupon_ids' => 'required|array',
            'coupon_ids.*' => 'integer|exists:coupons,id',
            'format' => 'required|in:html,text',
            'filename' => 'required|string',
        ]);

        $couponIds = $request->coupon_ids;
        $format = $request->format;
        $filename = $request->filename;

        // Get coupons and validate they are fixed_points type
        $coupons = Coupon::whereIn('id', $couponIds)->get();
        $fixedPointsCoupons = $coupons->filter(fn ($c) => $c->type === 'fixed_points');

        if ($fixedPointsCoupons->isEmpty()) {
            return redirect()->back()->withErrors([
                'coupon_ids' => 'Nessun coupon di tipo "Punti fissi" selezionato.',
            ]);
        }

        $bulkQrService = app(BulkQRService::class);
        $finalIds = $fixedPointsCoupons->pluck('id')->toArray();

        if ($format === 'html') {
            $content = $bulkQrService->generateHTMLFile($finalIds);
            $extension = 'html';
            $mimeType = 'text/html';
        } else {
            $content = $bulkQrService->generateBulkQRCodesPDF($finalIds);
            $extension = 'txt';
            $mimeType = 'text/plain';
        }

        $fullFilename = "{$filename}.{$extension}";

        return response($content, 200, [
            'Content-Type' => $mimeType.'; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$fullFilename.'"',
            'Content-Length' => strlen($content),
        ]);
    }
}
