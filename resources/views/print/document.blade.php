@php
    // One page for every printed document; it also renders the PDFs (dompdf: CSS 2, tables, no flexbox)
    $documents = \App\Http\Controllers\OrderDocumentController::DOCUMENTS;
    $page = ['58mm' => 'margin: 0', '80mm' => 'margin: 0', 'a4' => 'size: A4; margin: 14mm', 'letter' => 'size: letter; margin: 0.5in'][$paper];
    $pdfUrl = route('orders.document.pdf', [$order, $document] + $query);
    $fileName = "{$order->order_number}-{$document}.pdf";
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __($documents[$document]) }} {{ $order->order_number }}</title>
@unless($pdf)
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="{{ asset_v('css/theme.css') }}">
<link rel="stylesheet" href="{{ asset_v('css/app.css') }}">
@endunless
<style>
@page { {{ $page }} }
@if(!empty($cjkFont))
@font-face { font-family: "Noto Sans CJK"; src: url("{{ $cjkFont }}"); }
@font-face { font-family: "Noto Sans CJK"; font-weight: bold; src: url("{{ $cjkFont }}"); }   /* one weight is bundled; bold uses it too */
@endif
body { margin: 0; }
{{-- PDFs: DejaVu Sans has the peso sign; the CJK font (only when needed) comes first and DejaVu fills its gaps --}}
.doc { margin: 0 auto; color: #000; background: #fff; font: 12px/1.4 {!! !$pdf ? 'system-ui, "Segoe UI", Roboto, Arial, sans-serif' : (empty($cjkFont) ? '"DejaVu Sans", sans-serif' : '"Noto Sans CJK", "DejaVu Sans", sans-serif') !!}; }
.paper-80mm .doc { width: 72mm; padding: 4mm 0 6mm; }
.paper-58mm .doc { width: 48mm; padding: 3mm 0 6mm; font-size: 10.5px; }
.paper-a4 .doc, .paper-letter .doc { font-size: 13px; }
.doc p, .doc h1, .doc table { margin: 0; }
.doc table { width: 100%; border-collapse: collapse; }
.doc th, .doc td { padding: 0.5mm 0; text-align: left; vertical-align: top; font-weight: normal; }
.doc .masthead { margin-bottom: 3mm; text-align: center; }
.doc .logo { max-width: 60%; max-height: 20mm; margin-bottom: 2mm; }
.doc .biz { font-size: 1.4em; font-weight: bold; line-height: 1.2; }
.doc h1 { margin: 3mm 0 2mm; font-size: 1.15em; text-align: center; letter-spacing: 0.12em; text-transform: uppercase; }
.doc .order-no { margin: 1mm 0; font-size: 1.6em; font-weight: bold; text-align: center; }
.doc .rule { margin: 2.5mm 0; border-top: 1px dashed #000; }
.doc .meta th { width: 36%; padding-right: 2mm; }
.doc .num { padding-left: 2mm; text-align: right; white-space: nowrap; }
.doc .sub, .doc .small { font-size: 0.88em; }
.doc .strong, .doc .strong td { font-weight: bold; }
.doc .total td { padding-top: 1.5mm; font-size: 1.35em; font-weight: bold; }
.doc .center { text-align: center; }
.doc .heading { margin: 3mm 0 1mm; font-weight: bold; }
.doc .box { min-height: 10mm; padding: 1.5mm 2mm; border: 1px solid #000; }
.doc .box.tall { min-height: 18mm; }
.doc .tick { display: inline-block; width: 3mm; height: 3mm; margin-right: 1.5mm; border: 1px solid #000; vertical-align: -0.4mm; }
.doc .codes { margin-top: 2.5mm; text-align: center; }   /* dompdf ignores auto margins on images */
.doc .qr { width: 28mm; height: 28mm; }
.paper-58mm .doc .order-no { font-size: 1.3em; }
.paper-a4 .doc .meta th, .paper-letter .doc .meta th { width: 35mm; }
@unless($pdf)
@media screen {
    body { background: var(--bg); }
    .print-bar { position: sticky; top: 0; z-index: 2; display: flex; flex-wrap: wrap; align-items: center; gap: var(--s-2) var(--s-3); padding: var(--s-3) var(--s-4); background: var(--surface); border-bottom: 1px solid var(--border); }
    .print-bar .spacer { flex: 1; }
    .sheet { margin: var(--s-5) auto var(--s-8); background: #fff; box-shadow: var(--shadow-md); }
    .paper-58mm .sheet { width: 58mm; }
    .paper-80mm .sheet { width: 80mm; }
    .paper-a4 .sheet { width: 210mm; min-height: 297mm; padding: 14mm; }
    .paper-letter .sheet { width: 8.5in; min-height: 11in; padding: 0.5in; }
}
@media screen and (max-width: 899px) {
    .paper-a4 .sheet, .paper-letter .sheet { width: auto; min-height: 0; margin: var(--s-3); padding: var(--s-4); }
}
@media print {
    .print-bar { display: none; }
}
@endunless
</style>
</head>
<body class="paper-{{ $paper }}">
@unless($pdf)
<nav class="print-bar" aria-label="{{ __('Print options') }}">
    <a class="btn btn-secondary" href="{{ route('orders.details', $order) }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('Back') }}</a>
    <div class="chips" role="group" aria-label="{{ __('Document') }}">
        @foreach($documents as $key => $label)
            <a class="chip" href="{{ route('orders.document', [$order, $key] + $query) }}" @if($key === $document) aria-current="page" @endif>{{ __($label) }}</a>
        @endforeach
    </div>
    <div class="chips" role="group" aria-label="{{ __('Paper') }}">
        @foreach(\App\Http\Controllers\OrderDocumentController::PAPERS as $key => $label)
            <a class="chip" href="{{ route('orders.document', [$order, $document, 'paper' => $key] + $query) }}" @if($key === $paper) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </div>
    <span class="spacer"></span>
    <button type="button" class="btn btn-secondary" id="shareDoc" hidden><i class="fas fa-share-nodes" aria-hidden="true"></i> {{ __('Share') }}</button>
    <a class="btn btn-secondary" href="{{ $pdfUrl }}"><i class="fas fa-file-pdf" aria-hidden="true"></i> {{ __('PDF') }}</a>
    <button type="button" class="btn btn-primary" id="printDoc"><i class="fas fa-print" aria-hidden="true"></i> {{ __('Print') }}</button>
</nav>
@endunless

<main class="sheet">
    <div class="doc">
        @if($document !== 'job')
            <header class="masthead">
                @if($logo)<img class="logo" src="{{ $logo }}" alt="">@endif
                <div class="biz">{{ $settings->business_name ?: 'Soap Opera' }}</div>
                @if($settings->address)<div class="small">{!! nl2br(e($settings->address)) !!}</div>@endif
                @if($settings->contact)<div class="small">{{ $settings->contact }}</div>@endif
                @if($settings->receipt_header)<div class="small">{!! nl2br(e($settings->receipt_header)) !!}</div>@endif
            </header>
        @endif
        @include("print.{$document}")
    </div>
</main>

@unless($pdf)
<script>
(function () {
    document.getElementById('printDoc').addEventListener('click', () => print());
    @if(request()->boolean('autoprint'))
    addEventListener('load', () => print());
    @endif

    // Phones and tablets can hand the PDF to email or chat apps
    const share = document.getElementById('shareDoc');
    const name = @json($fileName);
    if (!navigator.canShare || !navigator.canShare({ files: [new File([''], name, { type: 'application/pdf' })] })) return;
    share.hidden = false;
    share.addEventListener('click', async () => {
        share.classList.add('is-loading');
        try {
            const blob = await (await fetch(@json($pdfUrl))).blob();
            await navigator.share({ files: [new File([blob], name, { type: 'application/pdf' })], title: document.title });
        } catch (e) { /* cancelled */ }
        share.classList.remove('is-loading');
    });
})();
</script>
@endunless
</body>
</html>
