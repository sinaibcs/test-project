<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use PDF;

class PDFController extends Controller
{
    public function index()
    {

        $data = ['applications' => []];

//        $pdf = PDF::loadView('pdf.document', $data);


        $pdf = LaravelMpdf::loadView('pdf.document', $data, [],
            [
                'mode' => 'utf-8',
                'format' => 'A4-P',
                'title' => 'চিকিৎসা সংক্রান্ত আবেদন প্রাপ্তির প্রস্তাব প্রেরণের নমুনার ছক',
                'orientation' => 'L',
                'default_font_size' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 10,
            ]);

        $pdfPath = public_path('/pdf/document.pdf');

        $pdf->save($pdfPath);

        return $this->sendResponse(['url' => asset('/pdf/document.pdf')]);

        return $pdf->download('document.pdf');
    }
}
