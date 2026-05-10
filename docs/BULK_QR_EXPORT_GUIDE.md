# Bulk QR Code Export Guide

## Overview

The SweetHome admin panel now supports bulk QR code generation and export for multiple coupons. This feature allows administrators to select multiple `fixed_points` coupons and export their QR codes in various formats for printing and distribution.

## How to Use

### Step 1: Select Coupons
1. Navigate to **Admin Panel → Coupons**
2. Use the checkboxes to select one or more coupons
3. Only coupons of type "Punti fissi" (fixed points) can have QR codes

### Step 2: Export QR Codes
1. Click the **"Esporta QR Code"** button in the bulk actions dropdown
2. Choose your export format:
   - **HTML (Recommended)**: Generates a styled HTML file with embedded QR code images
     - Can be printed to PDF from any browser
     - Preserves QR code images for reliable scanning
     - Professional appearance with branding
   - **Text**: Generates a simple text file with coupon details
     - Lightweight format
     - Easy to read
     - Good for quick reference

3. Enter a filename (defaults to `qr-coupons-YYYY-MM-DD`)
4. Click "Confirm" to download the file

### Step 3: Print to PDF (if using HTML format)
1. Open the downloaded HTML file in your browser
2. Press `Ctrl+P` (Windows) or `Cmd+P` (Mac)
3. Select "Save as PDF" as the printer
4. Adjust layout settings as needed
5. Save the PDF

## Export Formats

### HTML Format (Recommended)
- **File Extension**: `.html`
- **Includes**: 
  - QR code images embedded as base64
  - Coupon details (code, points, usage limits)
  - Professional styling with SweetHome branding
  - Usage instructions
  - Grid layout for easy printing
- **Best for**: Printing professional flyers, posters, or handouts

### Text Format
- **File Extension**: `.txt`
- **Includes**:
  - Coupon codes and point values
  - Usage statistics
  - Creation dates
  - URL template for QR codes
- **Best for**: Quick reference, importing into other systems

## Requirements

- **Coupon Type**: Only `fixed_points` type coupons can be exported
- **Selection**: At least one coupon must be selected
- **Admin Access**: Requires admin panel access

## Example Workflow

1. **Create Campaign Coupons**:
   - Create multiple fixed_points coupons in admin
   - Example: `ESTATE-2024-01` (50 points), `ESTATE-2024-02` (100 points), etc.

2. **Select and Export**:
   - Select all campaign coupons
   - Click "Esporta QR Code"
   - Choose HTML format
   - Download the file

3. **Print and Distribute**:
   - Open HTML file in browser
   - Print to PDF or directly to paper
   - Distribute flyers in store, at events, or via mail

## Technical Details

### QR Code Data Structure
Each QR code contains a URL like:
```
https://yoursite.com/r/{secure_token}
```

When scanned:
1. User is redirected to redemption page
2. If not logged in, redirected to login
3. After login, points are automatically added
4. Token is invalidated to prevent reuse

### Security Features
- **Unique Tokens**: Each coupon generates secure, unique tokens
- **Single Redemption**: Each user can only redeem a coupon once
- **Rate Limiting**: Prevents abuse of redemption system
- **Validation**: Expired or used coupons are rejected

## Troubleshooting

### "Solo i coupon di tipo Punti fissi" Error
- This means you selected coupons that are not `fixed_points` type
- Only fixed_points coupons can have QR codes
- Deselect non-fixed_points coupons and try again

### QR Code Not Scanning
- Ensure printed QR code is clear and not pixelated
- Check that lighting is adequate when scanning
- Verify the coupon is still active and not expired

### File Not Downloading
- Check browser popup blocker
- Ensure you have permission to download files
- Try a different browser

## Tips for Distribution

- **Print Quality**: Use high-quality paper and printer for best results
- **Size**: QR codes should be at least 2x2 cm to scan reliably
- **Placement**: Include clear instructions near QR codes
- **Testing**: Always test QR codes before mass distribution
- **Tracking**: Use unique coupon codes for different campaigns to track effectiveness