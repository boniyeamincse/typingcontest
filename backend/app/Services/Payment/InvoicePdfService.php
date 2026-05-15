<?php

namespace App\Services\Payment;

use App\Models\Invoice;

class InvoicePdfService
{
    public function generate(Invoice $invoice): string
    {
        $lines = [
            'TypingContest Invoice',
            'Invoice: ' . $invoice->invoice_number,
            'User ID: ' . $invoice->user_id,
            'Status: ' . $invoice->status,
            'Subtotal: ' . number_format((float) $invoice->subtotal, 2) . ' ' . $invoice->currency,
            'Discount: ' . number_format((float) $invoice->discount, 2) . ' ' . $invoice->currency,
            'Total: ' . number_format((float) $invoice->total, 2) . ' ' . $invoice->currency,
            'Issued At: ' . optional($invoice->issued_at)->toDateTimeString(),
        ];

        $text = '';
        $y = 760;
        foreach ($lines as $line) {
            $safe = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $text .= "BT /F1 12 Tf 50 {$y} Td ({$safe}) Tj ET\n";
            $y -= 18;
        }

        $objects = [];
        $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
        $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
        $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n";
        $objects[] = "4 0 obj << /Length " . strlen($text) . " >> stream\n{$text}endstream endobj\n";
        $objects[] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj;
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }
}
