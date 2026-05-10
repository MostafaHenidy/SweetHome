<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponQRToken;
use App\Models\User;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Illuminate\Support\Str;

class QRCodeService
{
    /**
     * Generate a new QR token for a coupon.
     */
    public function generateToken(Coupon $coupon, ?int $userId = null): CouponQRToken
    {
        // Validate coupon type
        if ($coupon->type !== 'fixed_points') {
            throw new \InvalidArgumentException('QR codes are only available for fixed_points coupons.');
        }

        // Check if coupon is still valid
        if ($coupon->status !== 'active') {
            throw new \InvalidArgumentException('Coupon is not active or has expired.');
        }

        // Generate secure token
        $token = Str::random(32);

        // Create QR token record
        return CouponQRToken::create([
            'coupon_id' => $coupon->id,
            'token' => $token,
            'user_id' => $userId,
            'expires_at' => now()->addDays(7), // 7-day expiry
        ]);
    }

    /**
     * Generate QR code image data for a coupon.
     */
    public function generateQRCode(Coupon $coupon, ?int $userId = null): string
    {
        // Get or create a valid token
        $tokenRecord = $this->getOrCreateValidToken($coupon, $userId);

        // Prepare QR code data
        $qrData = [
            'type' => 'coupon_redemption',
            'coupon_code' => $coupon->code,
            'token' => $tokenRecord->token,
            'created_at' => $tokenRecord->created_at->toISOString(),
            'expires_at' => $tokenRecord->expires_at?->toISOString(),
        ];

        // Create redirect URL (using short route for mobile)
        $redirectUrl = url("/r/{$tokenRecord->token}");

        // Generate QR code using BaconQrCode v3
        $rendererStyle = new RendererStyle(400, 0);
        $renderer = new ImageRenderer($rendererStyle, new SvgImageBackEnd);

        // Encode the QR code content
        $qrCode = Encoder::encode(
            $redirectUrl,
            ErrorCorrectionLevel::M()
        );

        // Render to SVG
        $svg = $renderer->render($qrCode);

        return $svg;
    }

    /**
     * Get or create a valid token for a coupon.
     */
    public function getOrCreateValidToken(Coupon $coupon, ?int $userId = null): CouponQRToken
    {
        // Try to find a valid token first
        $validToken = $coupon->qrTokens()
            ->valid()
            ->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->first();

        if ($validToken) {
            return $validToken;
        }

        // Create a new token
        return $this->generateToken($coupon, $userId);
    }

    /**
     * Validate a token and return the coupon if valid.
     */
    public function validateToken(string $token): ?CouponQRToken
    {
        $tokenRecord = CouponQRToken::with('coupon')
            ->where('token', $token)
            ->first();

        if (! $tokenRecord || ! $tokenRecord->isValid()) {
            return null;
        }

        return $tokenRecord;
    }

    /**
     * Redeem a token and add points to user account.
     */
    public function redeemToken(CouponQRToken $tokenRecord, int $userId): bool
    {
        // Validate token is still valid
        if (! $tokenRecord->isValid()) {
            return false;
        }

        // Check if user has already redeemed this coupon
        $alreadyRedeemed = $tokenRecord->coupon->qrTokens()
            ->where('user_id', $userId)
            ->whereNotNull('redeemed_at')
            ->exists();

        if ($alreadyRedeemed) {
            return false;
        }

        // Mark token as redeemed
        $tokenRecord->update([
            'user_id' => $userId,
            'redeemed_at' => now(),
        ]);

        // Add points to user account
        $pointsService = app(PointsService::class);
        $points = (int) $tokenRecord->coupon->value;

        $pointsService->adjustPoints(
            User::find($userId),
            $points,
            "Coupon QR redemption: {$tokenRecord->coupon->code}"
        );

        // Generate new token for next redemption
        $this->generateToken($tokenRecord->coupon);

        return true;
    }

    /**
     * Get QR code as base64 encoded image.
     */
    public function getQRCodeBase64(Coupon $coupon, ?int $userId = null): string
    {
        $qrCode = $this->generateQRCode($coupon, $userId);

        return 'data:image/svg+xml;base64,'.base64_encode($qrCode);
    }

    /**
     * Generate QR codes for bulk distribution.
     */
    public function bulkGenerateQRCodes(array $couponIds): array
    {
        $qrCodes = [];

        foreach ($couponIds as $couponId) {
            $coupon = Coupon::find($couponId);

            if ($coupon && $coupon->type === 'fixed_points') {
                $qrCodes[$couponId] = [
                    'coupon' => $coupon,
                    'qr_code' => $this->getQRCodeBase64($coupon),
                    'token_count' => $coupon->qrTokens()->valid()->count(),
                ];
            }
        }

        return $qrCodes;
    }
}
