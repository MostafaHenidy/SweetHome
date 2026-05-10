<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Facades\Storage;

class BulkQRService
{
    /**
     * Generate QR codes for multiple coupons and create a PDF export
     */
    public function generateBulkQRCodesPDF(array $couponIds): string
    {
        $qrService = app(QRCodeService::class);
        $coupons = Coupon::whereIn('id', $couponIds)->get();

        // Generate QR codes data
        $qrCodesData = [];
        foreach ($coupons as $coupon) {
            if ($coupon->type === 'fixed_points') {
                $qrCodesData[] = [
                    'coupon' => $coupon,
                    'qr_code' => $qrService->getQRCodeBase64($coupon),
                ];
            }
        }

        // Generate HTML content
        $html = $this->generatePDFHtml($qrCodesData);

        // Generate PDF using simple text-based approach
        $pdfContent = $this->generateSimplePDF($html, $qrCodesData);

        return $pdfContent;
    }

    /**
     * Generate HTML content for PDF
     */
    protected function generatePDFHtml(array $qrCodesData): string
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    background: #f5f5f5;
                }
                .header {
                    text-align: center;
                    margin-bottom: 30px;
                    padding: 20px;
                    background: #24384a;
                    color: white;
                    border-radius: 8px;
                }
                .coupon-grid {
                    display: grid;
                    grid-template-columns: repeat(2, 1fr);
                    gap: 20px;
                }
                .coupon-card {
                    background: white;
                    border: 2px solid #b89252;
                    border-radius: 8px;
                    padding: 20px;
                    text-align: center;
                    page-break-inside: avoid;
                }
                .coupon-code {
                    font-size: 18px;
                    font-weight: bold;
                    color: #24384a;
                    margin-bottom: 10px;
                }
                .coupon-points {
                    font-size: 16px;
                    color: #b89252;
                    margin-bottom: 15px;
                }
                .qr-code {
                    width: 150px;
                    height: 150px;
                    margin: 0 auto 15px;
                    border: 1px solid #ddd;
                    padding: 10px;
                    background: white;
                }
                .qr-code img {
                    width: 100%;
                    height: 100%;
                }
                .coupon-details {
                    font-size: 12px;
                    color: #666;
                    margin-top: 10px;
                }
                .footer {
                    text-align: center;
                    margin-top: 30px;
                    padding: 20px;
                    color: #666;
                    font-size: 12px;
                }
                .instructions {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 5px;
                    padding: 15px;
                    margin-bottom: 20px;
                    font-size: 14px;
                }
                @media print {
                    body {
                        background: white;
                    }
                    .coupon-card {
                        page-break-inside: avoid;
                    }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Codici QR per Coupon</h1>
                <p>SweetHome Milano</p>
            </div>

            <div class="instructions">
                <strong>Istruzioni per l\'uso:</strong>
                <ul>
                    <li>I clienti scansionano il codice QR con il telefono</li>
                    <li>Verranno reindirizzati alla pagina di riscatto del coupon</li>
                    <li>Dopo il login, i punti verranno aggiunti automaticamente</li>
                    <li>Ogni coupon può essere riscattato fino al numero massimo di usi</li>
                </ul>
            </div>

            <div class="coupon-grid">
        ';

        foreach ($qrCodesData as $data) {
            $coupon = $data['coupon'];
            $html .= '
                <div class="coupon-card">
                    <div class="coupon-code">'.htmlspecialchars($coupon->code).'</div>
                    <div class="coupon-points">'.(int) $coupon->value.' punti</div>
                    <div class="qr-code">
                        <img src="'.$data['qr_code'].'" alt="QR Code '.htmlspecialchars($coupon->code).'">
                    </div>
                    <div class="coupon-details">
                        <div>Valido per: '.($coupon->max_uses ? $coupon->max_uses.' usi' : 'Usi illimitati').'</div>
                        <div>Creato: '.$coupon->created_at->format('d/m/Y').'</div>
                    </div>
                </div>
            ';
        }

        $html .= '
            </div>

            <div class="footer">
                <p>SweetHome Milano - Via Roma 123, 20100 Milano</p>
                <p>Generato il '.now()->format('d/m/Y H:i').'</p>
            </div>
        </body>
        </html>
        ';

        return $html;
    }

    /**
     * Generate a formatted PDF-like document with QR codes (text-based)
     */
    protected function generateSimplePDF(string $html, array $qrCodesData): string
    {
        $content = [];
        $content[] = '═══════════════════════════════════════════════════════════════════════════';
        $content[] = '                   CODICI QR PER COUPON - SWEETHOME MILANO';
        $content[] = '═══════════════════════════════════════════════════════════════════════════';
        $content[] = '';
        $content[] = 'Generato: '.now()->format('d/m/Y H:i');
        $content[] = 'Coupon selezionati: '.count($qrCodesData);
        $content[] = '';
        $content[] = 'ISTRUZIONI:';
        $content[] = '- I clienti scansionano il codice QR con il telefono';
        $content[] = '- Verranno reindirizzati alla pagina di riscatto del coupon';
        $content[] = '- Dopo il login, i punti verranno aggiunti automaticamente';
        $content[] = '- Ogni coupon può essere riscattato fino al numero massimo di usi';
        $content[] = '';
        $content[] = '───────────────────────────────────────────────────────────────────────────';
        $content[] = '';

        foreach ($qrCodesData as $index => $data) {
            $coupon = $data['coupon'];
            $content[] = 'COUPON '.($index + 1).' DI '.count($qrCodesData);
            $content[] = '───────────────────────────────────────────────────────────────────────────';
            $content[] = 'Codice: '.$coupon->code;
            $content[] = 'Punti: '.(int) $coupon->value;
            $content[] = 'Tipo: Punti fissi';
            $content[] = 'Usi massimi: '.($coupon->max_uses ?? 'Illimitato');
            $content[] = 'Usi attuali: '.$coupon->used_count;
            $content[] = 'Creato: '.$coupon->created_at->format('d/m/Y');
            $content[] = 'URL QR Code: '.url('/r/[token]');
            $content[] = '';
            $content[] = '';
        }

        $content[] = '───────────────────────────────────────────────────────────────────────────';
        $content[] = 'SweetHome Milano';
        $content[] = 'Via Roma 123, 20100 Milano';
        $content[] = 'Tel: +39 02 1234567 | Email: info@sweethome.it';
        $content[] = '═══════════════════════════════════════════════════════════════════════════';

        return implode("\n", $content);
    }

    /**
     * Generate HTML file for download (with embedded QR codes)
     */
    public function generateHTMLFile(array $couponIds): string
    {
        $qrService = app(QRCodeService::class);
        $coupons = Coupon::whereIn('id', $couponIds)->get();

        $html = $this->generatePDFHtml(array_map(function ($coupon) use ($qrService) {
            return [
                'coupon' => $coupon,
                'qr_code' => $qrService->getQRCodeBase64($coupon),
            ];
        }, $coupons->all()));

        return $html;
    }

    /**
     * Save bulk QR codes to storage
     */
    public function saveToStorage(array $couponIds, string $filename): string
    {
        $content = $this->generateBulkQRCodesPDF($couponIds);
        $path = "qr-codes/{$filename}";
        Storage::disk('public')->put($path, $content);

        return $path;
    }
}
