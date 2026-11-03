<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Support\Barcode;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Dompdf\Frame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * An order's printed documents: the customer's receipt, the claim ticket they
 * keep until pickup, and the job order that travels with the laundry. Each
 * prints on a 58 or 80 mm receipt roll or on A4/Letter, and downloads as a PDF.
 */
class OrderDocumentController extends Controller
{
    public const DOCUMENTS = ['receipt' => 'Receipt', 'ticket' => 'Claim ticket', 'job' => 'Job order'];

    public const PAPERS = ['58mm' => '58 mm', '80mm' => '80 mm', 'a4' => 'A4', 'letter' => 'Letter'];

    public function show(Request $request, Order $order, string $document)
    {
        return view('print.document', $this->data($request, $order, $document) + ['pdf' => false]);
    }

    public function pdf(Request $request, Order $order, string $document)
    {
        $data = $this->data($request, $order, $document);
        $html = view('print.document', $data + ['pdf' => true])->render();
        if (preg_match('/[\p{Han}\p{Hiragana}\p{Katakana}]/u', $html)) {
            // Chinese or Japanese text (in any language: a customer's name too) needs the bundled CJK font;
            // only then, as loading it adds a few hundred milliseconds
            $html = view('print.document', $data + ['pdf' => true, 'cjkFont' => str_replace('\\', '/', resource_path('fonts/NotoSansCJK-Regular.ttf'))])->render();
        }
        $paper = $data['paper'];

        if (str_ends_with($paper, 'mm')) {
            // A roll is cut where the receipt ends: lay it out on a very long page to measure it first
            $width = (int) $paper / 25.4 * 72;
            $height = 0;
            $this->renderPdf($html, [0, 0, $width, 14400], function (Frame $frame) use (&$height) {
                if ($frame->get_node()->nodeName === 'body') {
                    $height = $frame->get_position('y') + $frame->get_margin_height();
                }
            });
            $paper = [0, 0, $width, ceil($height)];
        }

        return response($this->renderPdf($html, $paper)->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$order->order_number}-{$document}.pdf\"",
        ]);
    }

    private function data(Request $request, Order $order, string $document): array
    {
        $order->load(['customer', 'staff:id,name', 'items.service:id,name', 'addons', 'payments.receiver:id,name']);
        $settings = app('system.settings');
        $paper = in_array($request->query('paper'), array_keys(self::PAPERS), true) ? $request->query('paper') : $settings->receipt_paper;

        // A receipt is for one payment: the latest, or ?payment= when reprinting an earlier one
        $payments = $order->payments->sortBy('id');
        $payment = $payments->firstWhere('id', $request->integer('payment')) ?? $payments->last();

        return [
            'order' => $order,
            'document' => $document,
            'paper' => $paper,
            'settings' => $settings,
            'payment' => $payment,
            'paidToDate' => $payment ? (float) $payments->where('id', '<=', $payment->id)->sum('amount') : 0.0,
            'logo' => $settings->receiptLogoUri(),
            // The QR code opens the order on a phone or 2D scanner; the barcode suits USB
            // scanners but, at about 60 mm, does not fit a 58 mm roll
            'qr' => $settings->receipt_show_qr
                ? (new QRCode(new QROptions(['eccLevel' => EccLevel::M, 'drawLightModules' => false])))->render(route('orders.details', $order))
                : null,
            'barcode' => $settings->receipt_show_barcode && $paper !== '58mm' ? Barcode::code128($order->order_number) : null,
            'query' => array_filter(['paper' => $paper, 'payment' => $request->integer('payment') ?: null]),
        ];
    }

    private function renderPdf(string $html, string|array $paper, ?callable $onFrame = null): Dompdf
    {
        File::ensureDirectoryExists(storage_path('fonts'));   // dompdf keeps the CJK font's metrics here
        $dompdf = new Dompdf([
            'defaultMediaType' => 'print',
            'defaultFont' => 'DejaVu Sans',
            'isFontSubsettingEnabled' => true,
            'chroot' => base_path(),
            'fontDir' => storage_path('fonts'),
            'fontCache' => storage_path('fonts'),
        ]);
        if ($onFrame) {
            $dompdf->setCallbacks([['event' => 'end_frame', 'f' => $onFrame]]);
        }
        $dompdf->setPaper($paper);
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf;
    }
}
