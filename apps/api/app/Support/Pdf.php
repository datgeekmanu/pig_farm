<?php

namespace App\Support;

use App\Models\Farm;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Barryvdh\DomPDF\PDF as DomPdfDocument;
use Illuminate\Support\Facades\Storage;

class Pdf
{
    /**
     * Render a branded PDF for a farm (logo, accent colour, header data available
     * to every template).
     *
     * @param  array<string, mixed>  $data
     */
    public static function render(string $view, Farm $farm, array $data = []): DomPdfDocument
    {
        $logo = $farm->logo_path && Storage::disk('public')->exists($farm->logo_path)
            ? Storage::disk('public')->path($farm->logo_path)
            : null;

        return DomPdf::loadView($view, array_merge([
            'farm' => $farm,
            'logo' => $logo,
            'color' => $farm->receipt_color ?: '#2980B9',
        ], $data));
    }
}
