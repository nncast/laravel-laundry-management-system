@extends('layouts.app')

@section('title', __('Settings'))
@section('page-title', __('Settings'))
@section('page-subtitle', __('Your business details, receipts, currency and formats, and data backups'))

@push('styles')
<style>
.logo-preview { display: flex; flex-wrap: wrap; align-items: center; gap: var(--s-3); }
.logo-preview img { width: 32px; height: 32px; border: 1px solid var(--border); border-radius: 6px; padding: 2px; }
.logo-preview img.receipt-logo { width: auto; max-width: 160px; height: 56px; object-fit: contain; background: #fff; }
.backup-status { display: flex; align-items: center; gap: var(--s-3); padding: var(--s-4); border-radius: var(--radius); background: var(--surface-muted); border: 1px solid var(--border); }
.backup-status .bubble-tile { width: 44px; height: 44px; }
.region-preview { display: grid; gap: 4px; padding: var(--s-3) var(--s-4); border-radius: var(--radius); background: var(--accent-soft); }
.region-preview strong { font-size: var(--fs-lg); }
.restore-box { display: grid; gap: var(--s-3); padding: var(--s-4); border: 1px solid #f3c2c4; border-radius: var(--radius); background: #fffafa; }
</style>
@endpush

@section('content')
<div class="grid-2" style="align-items:start">
<div class="stack">
    <section class="card" aria-labelledby="profile-title">
        <header class="card-header">
            <div>
                <h2 class="card-title" id="profile-title"><i class="fas fa-store" aria-hidden="true"></i> {{ __('Business profile') }}</h2>
                <p class="card-subtitle">{{ __('Shown in the menu and on printed receipts') }}</p>
            </div>
        </header>
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body form-grid">
                <x-field name="business_name" :label="__('Business name')" required maxlength="255" :value="$settings->business_name" />
                <x-field name="address" :label="__('Address')" type="textarea" rows="3" maxlength="1000" :value="$settings->address" />
                <x-field name="contact" :label="__('Phone')" type="tel" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" placeholder="09123456789" :value="$settings->contact" :help="__('Optional. 10 to 15 digits, no spaces.')" />
                <div class="stack-sm">
                    <x-field name="favicon" :label="__('Browser icon')" type="file" accept=".ico,image/x-icon,image/vnd.microsoft.icon" :help="__('An .ico file (16, 32 or 48 px). Leave empty to keep the current one.')" />
                    @if(!empty($settings->favicon))
                        <div class="logo-preview"><img src="{{ asset($settings->favicon) }}?v={{ optional($settings->updated_at)->timestamp }}" alt="" onerror="this.parentElement.hidden = true"><span class="muted">{{ __('Current icon') }}</span></div>
                    @endif
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ __('Save profile') }}</button>
            </div>
        </form>
    </section>

    <section class="card" id="receipts" aria-labelledby="receipts-title">
        <header class="card-header">
            <div>
                <h2 class="card-title" id="receipts-title"><i class="fas fa-receipt" aria-hidden="true"></i> {{ __('Receipts & printing') }}</h2>
                <p class="card-subtitle">{{ __('What receipts, claim tickets and job orders show') }}</p>
            </div>
            @if($latestOrderId)
                <a class="btn btn-sm btn-secondary" href="{{ route('orders.document', [$latestOrderId, 'receipt']) }}" target="_blank" rel="noopener"><i class="fas fa-eye" aria-hidden="true"></i> {{ __('Preview') }}</a>
            @endif
        </header>
        <form method="POST" action="{{ route('settings.receipts') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body form-grid">
                <x-field name="receipt_paper" :label="__('Paper')" type="segmented" :options="\App\Http\Controllers\OrderDocumentController::PAPERS" :value="$settings->receipt_paper" :help="__('Receipt printers take 58 or 80 mm rolls. Any other size can still be picked when printing.')" />
                <x-field name="turnaround_days" :label="__('Orders are usually ready after (days)')" type="number" min="0" max="30" inputmode="numeric" required :value="$settings->turnaround_days" :help="__('The POS fills in the ready-by date with this. 0 means the same day.')" />
                <div class="stack-sm">
                    <x-field name="receipt_logo" :label="__('Logo')" type="file" accept="image/png,image/jpeg" :help="__('PNG or JPG up to 300 KB. Black on white prints best on receipt printers.')" />
                    @if($receiptLogo = $settings->receiptLogoUri())
                        <div class="logo-preview"><img class="receipt-logo" src="{{ $receiptLogo }}" alt="{{ __('Current logo') }}"><label class="check"><input type="checkbox" name="remove_logo" value="1"> {{ __('Remove logo') }}</label></div>
                    @endif
                </div>
                <x-field name="receipt_header" :label="__('Header')" type="textarea" rows="2" maxlength="500" :value="$settings->receipt_header" :help="__('Under your business name, e.g. tax number or opening hours.')" />
                <x-field name="receipt_thank_you" :label="__('Thank-you message')" maxlength="255" :placeholder="__('Thank you for choosing us!')" :value="$settings->receipt_thank_you" />
                <x-field name="receipt_footer" :label="__('Promo message')" type="textarea" rows="2" maxlength="500" :value="$settings->receipt_footer" :help="__('At the bottom of receipts.')" />
                <x-field name="receipt_pickup_instructions" :label="__('Pickup instructions')" type="textarea" rows="2" maxlength="1000" :value="$settings->receipt_pickup_instructions" :help="__('On claim tickets, and on receipts until the order is picked up.')" />
                <x-field name="receipt_terms" :label="__('Terms')" type="textarea" rows="3" maxlength="2000" :value="$settings->receipt_terms" />
                <div class="stack-sm">
                    <label class="check"><input type="checkbox" name="receipt_show_qr" value="1" @checked($settings->receipt_show_qr)> {{ __('Print a QR code: a phone camera opens the order') }}</label>
                    <label class="check"><input type="checkbox" name="receipt_show_barcode" value="1" @checked($settings->receipt_show_barcode)> {{ __('Print a barcode for USB scanners (80 mm and up)') }}</label>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ __('Save receipt settings') }}</button>
            </div>
        </form>
    </section>
</div>

<div class="stack">
    @php
        $fmt = app(\App\Support\Format::class);
        $language = config('locales.' . app()->getLocale());
        $currencyOptions = collect(config('currencies'))->mapWithKeys(fn ($c, $code) => [$code => "{$c['symbol']}  " . __($c['name']) . " ({$code})"])->all();
        $numberSample = fn ($id) => '1' . \App\Support\Format::NUMBER_FORMATS[$id][0] . '234' . \App\Support\Format::NUMBER_FORMATS[$id][1] . '56';
        $numberOptions = ['auto' => __('Automatic (:example)', ['example' => $numberSample($language['number'])])]
            + collect(\App\Support\Format::NUMBER_FORMATS)->keys()->mapWithKeys(fn ($id) => [$id => $numberSample($id)])->all();
        $sample = now()->setTime(21, 5);
        $dateOptions = ['auto' => __('Automatic (:example)', ['example' => $sample->translatedFormat($language['date'])])]
            + collect(\App\Support\Format::DATE_FORMATS)->mapWithKeys(fn ($label, $format) => [$format => $sample->translatedFormat($format) . ($label !== 'Nov 3, 2026' ? "  ({$label})" : '')])->all();
        $timeOptions = ['12h' => $sample->translatedFormat('g:i A') . ' (' . __('12-hour') . ')', '24h' => $sample->format('H:i') . ' (' . __('24-hour') . ')'];
        // For the live preview
        $previewData = [
            'currencies' => collect(config('currencies'))->map(fn ($c) => ['symbol' => $c['symbol'], 'decimals' => $c['decimals']]),
            'numbers' => \App\Support\Format::NUMBER_FORMATS + ['auto' => \App\Support\Format::NUMBER_FORMATS[$language['number']]],
            'dates' => collect(\App\Support\Format::DATE_FORMATS)->mapWithKeys(fn ($l, $format) => [$format => $sample->translatedFormat($format)])->put('auto', $sample->translatedFormat($language['date'])),
            'times' => ['12h' => $sample->translatedFormat('g:i A'), '24h' => $sample->format('H:i')],
            'saved' => $fmt->currency,
        ];
    @endphp
    @php $languageOptions = collect(config('locales'))->map(fn ($l) => $l['native'] . ($l['native'] !== $l['name'] ? ' (' . __($l['name']) . ')' : ''))->all(); @endphp
    <section class="card" id="language" aria-labelledby="language-title">
        <header class="card-header">
            <div>
                <h2 class="card-title" id="language-title"><i class="fas fa-language" aria-hidden="true"></i> {{ __('Language') }}</h2>
                <p class="card-subtitle">{{ __('Each staff member picks their own from the menu') }}</p>
            </div>
        </header>
        <form method="POST" action="{{ route('settings.language') }}">
            @csrf
            <div class="card-body form-grid">
                <fieldset class="field">
                    <legend class="label">{{ __('Languages staff can pick') }}</legend>
                    @foreach($languageOptions as $code => $name)
                        <label class="check"><input type="checkbox" name="enabled_locales[]" value="{{ $code }}" @checked(in_array($code, old('enabled_locales', $settings->enabledLocales()), true))> <span lang="{{ config("locales.$code.html") }}">{{ $name }}</span></label>
                    @endforeach
                    <p class="field-error">{{ $errors->first('enabled_locales') ?: $errors->first('enabled_locales.*') }}</p>
                </fieldset>
                <div class="form-row">
                    <x-field name="default_locale" :label="__('Default language')" :options="$languageOptions" :value="$settings->default_locale" :help="__('For the sign-in page and staff who have not picked one.')" />
                    <x-field name="fallback_locale" :label="__('Fallback language')" :options="$languageOptions" :value="$settings->fallback_locale" :help="__('Shown where a translation is missing.')" />
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ __('Save language settings') }}</button>
            </div>
        </form>
    </section>

    <section class="card" id="region" aria-labelledby="region-title">
        <header class="card-header">
            <div>
                <h2 class="card-title" id="region-title"><i class="fas fa-earth-asia" aria-hidden="true"></i> {{ __('Currency & formats') }}</h2>
                <p class="card-subtitle">{{ __('Used on every screen, receipt and export') }}</p>
            </div>
            <span class="badge badge-info">{{ $fmt->currency }} {{ currency_symbol() }}</span>
        </header>
        <form method="POST" action="{{ route('settings.region') }}" id="regionForm">
            @csrf
            <div class="card-body form-grid">
                <x-field name="currency" :label="__('Currency')" :options="$currencyOptions" :value="$fmt->currency" />
                <div class="form-row">
                    <x-field name="number_format" :label="__('Numbers')" :options="$numberOptions" :value="$settings->number_format ?: $fmt->numberFormat" />
                    <x-field name="date_format" :label="__('Dates')" :options="$dateOptions" :value="$settings->date_format ?: $fmt->dateFormat" />
                </div>
                <x-field name="time_format" :label="__('Time')" type="segmented" :options="$timeOptions" :value="$fmt->timeFormat" />
                <div class="region-preview" aria-live="polite">
                    <span class="eyebrow">{{ __('Preview') }}</span>
                    <div><strong id="previewMoney">{{ money(1250) }}</strong> · <span id="previewDate">{{ fmt_date($sample) }}</span> · <span id="previewTime">{{ fmt_time($sample) }}</span></div>
                </div>
                <div class="alert alert-warning" id="currencyWarning" hidden>
                    <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                    <span>{!! __('Prices are not converted: a :price service becomes :converted. Update service, add-on and product prices after switching. Orders already taken keep their currency.', ['price' => e(money(180)), 'converted' => '<strong id="previewConverted"></strong>']) !!}</span>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> {{ __('Save currency & formats') }}</button>
            </div>
        </form>
    </section>

    <section class="card" id="backups" aria-labelledby="backup-title">
        <header class="card-header">
            <div>
                <h2 class="card-title" id="backup-title"><i class="fas fa-database" aria-hidden="true"></i> {{ __('Backups') }}</h2>
                <p class="card-subtitle">{{ __('A copy is saved on this computer automatically once a day') }}</p>
            </div>
        </header>
        <div class="card-body stack-sm">
            <div class="backup-status">
                @if(!empty($lastBackup))
                    <span class="bubble-tile tone-green" aria-hidden="true"><i class="fas fa-circle-check"></i></span>
                    <div>
                        <strong>{{ __('Last backup :time.', ['time' => \Carbon\Carbon::createFromTimestamp($lastBackup)->diffForHumans()]) }}</strong>
                        <div class="muted">{{ \Carbon\Carbon::createFromTimestamp($lastBackup)->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</div>
                    </div>
                @else
                    <span class="bubble-tile tone-amber" aria-hidden="true"><i class="fas fa-triangle-exclamation"></i></span>
                    <div><strong>{{ __('No backup yet') }}</strong><div class="muted">{{ __('Download one now and keep it somewhere safe.') }}</div></div>
                @endif
            </div>
            <a href="{{ route('backup.download') }}" class="btn btn-primary btn-lg"><i class="fas fa-download" aria-hidden="true"></i> {{ __('Download a backup') }}</a>
            <p class="help">{{ __('The file (.sql) holds all orders, customers and settings. Keep a copy off this computer too, on a USB drive or cloud storage.') }}</p>

            <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data" class="restore-box"
                  data-confirm="{{ __('Everything in the app now will be replaced with the backup. A safety copy of the current data is saved first.') }}" data-confirm-title="{{ __('Restore this backup?') }}" data-confirm-button="{{ __('Restore') }}">
                @csrf
                <strong><i class="fas fa-rotate-left" aria-hidden="true"></i> {{ __('Restore from a backup') }}</strong>
                <x-field name="backup_file" :label="__('Backup file')" type="file" accept=".sql" required :help="__('Only .sql files downloaded from this page.')" />
                <label class="check"><input type="checkbox" name="confirm" value="1" required> {{ __('I understand this replaces all current data') }}</label>
                <div><button type="submit" class="btn btn-danger"><i class="fas fa-upload" aria-hidden="true"></i> {{ __('Restore backup') }}</button></div>
            </form>
        </div>
    </section>
</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // Live preview of the currency and formats before saving
    const data = @json($previewData);
    const form = document.getElementById('regionForm');
    const formatMoney = (amount, code, numbers) => {
        const currency = data.currencies[code];
        const [thousands, decimal] = data.numbers[numbers];
        const [whole, fraction] = amount.toFixed(currency.decimals).split('.');
        return currency.symbol + whole.replace(/\B(?=(\d{3})+(?!\d))/g, thousands) + (fraction ? decimal + fraction : '');
    };
    const update = () => {
        const code = form.currency.value;
        const numbers = form.number_format.value;
        document.getElementById('previewMoney').textContent = formatMoney(1250, code, numbers);
        document.getElementById('previewDate').textContent = data.dates[form.date_format.value];
        document.getElementById('previewTime').textContent = data.times[form.querySelector('[name="time_format"]:checked').value];
        const changed = code !== data.saved;
        document.getElementById('currencyWarning').hidden = !changed;
        if (changed) document.getElementById('previewConverted').textContent = formatMoney(180, code, numbers);
    };
    form.addEventListener('change', update);
})();
</script>
@endpush
