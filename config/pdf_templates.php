<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDFtk binary
    |--------------------------------------------------------------------------
    | Used only when strategy is "acroform". On Windows, set PDFTK_PATH to the
    | full path of pdftk.exe (e.g. C:\Program Files (x86)\PDFtk\bin\pdftk.exe).
    */
    'pdftk_binary' => env('PDFTK_PATH', 'pdftk'),

    /*
    |--------------------------------------------------------------------------
    | Strategy
    |--------------------------------------------------------------------------
    | - overlay: import each page of the branded PDF and draw order data (works
    |   with Word/Excel-exported PDFs that are not true AcroForm files).
    | - acroform: fill named PDF fields via PDFtk (requires fillable PDF + binary).
    |
    | Word/Excel PDFs usually need "overlay". Tune overlay.* to align text.
    */
    'templates' => [
        'quote' => [
            'path' => public_path('1 Modulo preventivo.pdf'),
            'strategy' => env('PDF_QUOTE_STRATEGY', 'overlay'),
            'quote_document_city' => env('PDF_QUOTE_CITY', 'Milano'),
            'acroform_field_map' => [
                // 'internal_key' => 'ExactPdfFieldName', // optional when using acroform
            ],
            'overlay' => [
                'content_start_page' => 1,
                'mode' => 'positioned',
                'font_size' => 8,
                'line_height' => 3.8,
                /*
                | mm, top-left origin — measured from "1 Modulo preventivo.pdf" (PyMuPDF text boxes).
                | Template embeds sample "Milano: …" / "N. preventivo: …" lines; clear_regions whites them out.
                | Cliente / Indirizzo share one row (~79.5 mm): left column = nome + contatti, right = indirizzo.
                */
                'clear_regions' => [
                    ['page' => 1, 'x' => 14, 'y' => 70.5, 'w' => 184, 'h' => 7.8],
                ],
                'fields' => [
                    'quote_place_date' => ['page' => 1, 'x' => 20, 'y' => 73.8, 'w' => 88, 'h' => 4.8, 'background' => true],
                    'quote_header_right' => ['page' => 1, 'x' => 118, 'y' => 73.8, 'w' => 88, 'h' => 4.8, 'align' => 'L', 'background' => true],
                    'customer_company_name' => ['page' => 1, 'x' => 48, 'y' => 76.2, 'w' => 68, 'h' => 3.6, 'font_size' => 7, 'single_line' => true],
                    'customer_name' => ['page' => 1, 'x' => 48, 'y' => 80, 'w' => 68, 'h' => 3.8, 'single_line' => true],
                    'shipping_address' => ['page' => 1, 'x' => 146, 'y' => 80, 'w' => 60, 'h' => 3.8, 'font_size' => 7, 'single_line' => true],
                    'customer_email_and_phone' => ['page' => 1, 'x' => 20, 'y' => 89.5, 'w' => 92, 'h' => 2.5, 'font_size' => 6.5, 'single_line' => true],
                    'quote_meta_line' => ['page' => 1, 'x' => 20, 'y' => 92.2, 'w' => 118, 'h' => 2.5, 'font_size' => 6.5, 'single_line' => true],
                    'notes' => ['page' => 1, 'x' => 18, 'y' => 270, 'w' => 175, 'h' => 7],
                    'seller_legal_name' => ['page' => 1, 'x' => 18, 'y' => 278, 'w' => 95, 'h' => 4],
                    'seller_vat_number' => ['page' => 1, 'x' => 18, 'y' => 283, 'w' => 55, 'h' => 4],
                    'seller_iban' => ['page' => 1, 'x' => 18, 'y' => 288, 'w' => 165, 'h' => 4],
                ],
                /*
                | Row Y and € column measured on "1 Modulo preventivo.pdf" (€ at x≈187.5 mm).
                | Price cells are right-aligned to end ~184 mm, immediately left of the printed €.
                */
                'item_rows' => [
                    ['page' => 1, 'description_x' => 22, 'price_x' => 148, 'qty_x' => null, 'total_x' => null, 'y' => 139.5, 'description_w' => 118, 'price_w' => 36, 'qty_w' => 0, 'total_w' => 0],
                    ['page' => 1, 'description_x' => 22, 'price_x' => 148, 'qty_x' => null, 'total_x' => null, 'y' => 149.4, 'description_w' => 118, 'price_w' => 36, 'qty_w' => 0, 'total_w' => 0],
                    ['page' => 1, 'description_x' => 22, 'price_x' => 148, 'qty_x' => null, 'total_x' => null, 'y' => 159.3, 'description_w' => 118, 'price_w' => 36, 'qty_w' => 0, 'total_w' => 0],
                    ['page' => 1, 'description_x' => 47, 'price_x' => 96, 'qty_x' => 138, 'total_x' => 148, 'y' => 204.7, 'description_w' => 48, 'price_w' => 34, 'qty_w' => 14, 'total_w' => 36],
                    ['page' => 1, 'description_x' => 47, 'price_x' => 96, 'qty_x' => 138, 'total_x' => 148, 'y' => 214.7, 'description_w' => 48, 'price_w' => 34, 'qty_w' => 14, 'total_w' => 36],
                    ['page' => 1, 'description_x' => 47, 'price_x' => 96, 'qty_x' => 138, 'total_x' => 148, 'y' => 224.6, 'description_w' => 48, 'price_w' => 34, 'qty_w' => 14, 'total_w' => 36],
                    ['page' => 1, 'description_x' => 47, 'price_x' => 96, 'qty_x' => 138, 'total_x' => 148, 'y' => 234.5, 'description_w' => 48, 'price_w' => 34, 'qty_w' => 14, 'total_w' => 36],
                ],
                'totals' => [
                    'subtotal' => ['page' => 1, 'x' => 148, 'y' => 175, 'w' => 36, 'h' => 4.5, 'align' => 'R', 'single_line' => true],
                    'total' => ['page' => 1, 'x' => 148, 'y' => 250, 'w' => 36, 'h' => 4.5, 'align' => 'R', 'single_line' => true],
                ],
            ],
        ],

        'order_confirmation' => [
            'path' => public_path('1 Modulo preventivo 2.pdf'),
            'strategy' => env('PDF_ORDER_CONFIRMATION_STRATEGY', 'overlay'),
            'acroform_field_map' => [],
            'overlay' => [
                'content_start_page' => 1,
                'margin_x' => 18,
                'margin_y' => 52,
                'content_width' => 175,
                'font_size' => 8,
                'line_height' => 3.8,
            ],
        ],

        'invoice' => [
            'path' => public_path('Fattura-2026_38FE.pdf'),
            'strategy' => env('PDF_INVOICE_STRATEGY', 'overlay'),
            'acroform_field_map' => [],
            'overlay' => [
                'content_start_page' => 1,
                'margin_x' => 18,
                'margin_y' => 58,
                'content_width' => 175,
                'font_size' => 8,
                'line_height' => 3.8,
            ],
        ],
    ],
];
