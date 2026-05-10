# QR Code Coupon System

## Overview

The QR Code Coupon System allows customers to redeem coupons by scanning QR codes with their mobile devices. When scanned, points are automatically added to the customer's account.

## Features

- **Mobile-Friendly**: Scan QR codes with any smartphone camera
- **Secure Tokens**: Each QR code contains a secure, unique token
- **Multi-Use**: Same QR code can be scanned by multiple customers (up to max_uses)
- **Automatic Point Addition**: Points are added directly to user accounts
- **Prevention of Double Redemption**: Each user can only redeem a coupon once
- **Guest User Flow**: Supports login flow for unauthenticated users

## System Requirements

- **Coupon Type**: Only `fixed_points` type coupons support QR code redemption
- **Authentication**: Users must have an account to redeem coupons
- **QR Code Generation**: Available in Filament admin panel for fixed_points coupons

## User Flow

### For Customers
1. **Scan QR Code**: Use phone camera to scan the QR code
2. **Authentication**: If not logged in, you'll be redirected to login
3. **Redemption**: After login, points are automatically added to your account
4. **Confirmation**: See confirmation screen with new point balance

### For Administrators
1. **Create Coupon**: Create a `fixed_points` coupon in admin panel
2. **Generate QR Code**: Click "Genera QR Code" button in coupon list for individual coupons
3. **Bulk Export**: Select multiple coupons and use "Esporta QR Code" for batch processing
4. **Download/Print**: Download QR code image or HTML file for distribution
5. **Distribute**: Share QR codes via flyers, posters, emails, etc.

## Technical Implementation

### Database Structure
- **coupon_qr_tokens**: Stores QR code tokens and redemption status
  - `coupon_id`: Reference to the coupon
  - `token`: Secure 32-character token
  - `user_id`: User who redeemed the token (nullable)
  - `redeemed_at`: When the token was redeemed
  - `expires_at`: Token expiry date (7 days default)

### QR Code Data
QR codes contain a URL like: `https://yoursite.com/r/{token}`

When scanned, this redirects to the redemption page.

### Security Features
- **Cryptographically Secure Tokens**: 32-character random tokens
- **Token Expiration**: 7-day default expiration
- **Rate Limiting**: 10 requests per minute per user
- **Single Redemption**: Each user can only redeem a coupon once
- **Token Validation**: Validates coupon status, expiration, and usage limits

## API Endpoints

### Web Routes
- `GET /r/{token}` - Mobile redemption page
- `GET /coupon/redeemed` - Post-login redemption handler

### API Routes (for mobile apps)
- `POST /api/coupon/scan` - Scan and redeem QR code
  - Requires authentication
  - Rate limited to 10 requests/minute

## Usage Examples

### Creating a QR Code Coupon
1. In Filament admin, go to Coupons
2. Click "Create Coupon"
3. Select type: "Punti fissi" (fixed_points)
4. Set value to number of points (e.g., 100)
5. Set max uses if desired
6. Save coupon
7. Click "Genera QR Code" button
8. Download QR code image

### Testing the System
```php
// Create test coupon
php artisan tinker --execute="
\$coupon = App\Models\Coupon::create([
    'code' => 'TEST-100',
    'type' => 'fixed_points',
    'value' => 100,
    'is_active' => true,
]);

// Generate QR code
\$qrService = app(App\Services\QRCodeService::class);
\$qrCode = \$qrService->getQRCodeBase64(\$coupon);
echo 'QR Code generated: ' . strlen(\$qrCode) . ' characters';
"
```

## Error Handling

The system handles these error scenarios:
- **Expired Token**: "This coupon has expired"
- **Invalid Token**: "Invalid QR code"
- **Coupon Exhausted**: "This coupon has no more redemptions available"
- **Already Redeemed**: "You have already redeemed this coupon"
- **Wrong Coupon Type**: "QR codes only available for point-based coupons"
- **Guest Users**: Redirects to login page

## Files Created/Modified

### New Files
- `app/Models/CouponQRToken.php` - QR token model
- `app/Services/QRCodeService.php` - QR code generation service
- `app/Http/Controllers/QRCodeController.php` - Redemption controller
- `resources/views/qr/success.blade.php` - Success view
- `resources/views/qr/error.blade.php` - Error view
- `resources/views/filament/components/qr-code-modal.blade.php` - Admin QR modal
- `database/migrations/2026_05_06_000010_create_coupon_qr_tokens_table.php` - Migration

### Modified Files
- `app/Models/Coupon.php` - Added QR token relationship
- `app/Filament/Resources/CouponResource.php` - Added QR code generation UI
- `routes/web.php` - Added redemption routes
- `app/Services/CouponService.php` - No changes needed (reuses existing logic)

## Maintenance

### Regular Tasks
- Monitor token expiration rates
- Clean up expired tokens (optional)
- Review redemption analytics

### Troubleshooting
- **QR Code Not Working**: Check coupon is active and type is `fixed_points`
- **Points Not Added**: Verify user account and token status
- **Duplicate Redemption**: Check user has not already redeemed coupon

## Bulk QR Export Feature
The system includes a bulk QR code export feature in the Filament admin panel:
- Select multiple `fixed_points` coupons from the coupon list
- Use the "Esporta QR Code" bulk action
- Choose HTML format (recommended) for images or text format for simple data
- Download and print for physical distribution

See `docs/BULK_QR_EXPORT_GUIDE.md` for detailed instructions.

## Future Enhancements
- QR code analytics and tracking
- Custom expiry periods per coupon
- QR code templates with branding
- Mobile app integration improvements