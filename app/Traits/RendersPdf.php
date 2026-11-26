<?php

namespace App\Traits;

use Illuminate\Http\Response;
use Mpdf\Mpdf;

trait RendersPdf
{
    /**
     * Render a Blade view as an inline A4 PDF response using mPDF.
     */
    protected function renderPdfResponse(string $view, array $data, string $filename, string $orientation = 'P'): Response
    {
        $tempDir = storage_path('app/mpdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'orientation' => $orientation,
            'format' => 'A4',
            'tempDir' => $tempDir,
            'default_font' => 'sans-serif',
        ]);

        $mpdf->WriteHTML(view($view, $data)->render());

        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }
}
