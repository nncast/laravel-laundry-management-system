@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('page-subtitle', 'Your business details, currency and formats, and data backups')

@push('styles')
<style>
.logo-preview { display: flex; align-items: center; gap: var(--s-3); }
.logo-preview img { width: 32px; height: 32px; border: 1px solid var(--border); border-radius: 6px; padding: 2px; }
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
                <h2 class="card-title" id="profile-title"><i class="fas fa-store" aria-hidden="true"></i> Business profile</h2>
                <p class="card-subtitle">Shown in the menu and on printed receipts</p>
            </div>
        </header>
        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body form-grid">
                <x-field name="business_name" label="Business name" required maxlength="255" :value="$settings->business_name" />
                <x-field name="address" label="Address" type="textarea" rows="3" maxlength="1000" :value="$settings->address" />
                <x-field name="contact" label="Phone" type="tel" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" placeholder="09123456789" :value="$settings->contact" help="Optional. 10 to 15 digits, no spaces." />
                <div class="stack-sm">
                    <x-field name="favicon" label="Browser icon" type="file" accept=".ico,image/x-icon,image/vnd.microsoft.icon" help="An .ico file (16, 32 or 48 px). Leave empty to keep the current one." />
                    @if(!empty($settings->favicon))
                        <div class="logo-preview"><img src="{{ asset($settings->favicon) }}?v={{ optional($settings->updated_at)->timestamp }}" alt="" onerror="this.parentElement.hidden = true"><span class="muted">Current icon</span></div>
                    @endif
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Save profile</button>
            </div>
        </form>
    </section>

    @php
        $fmt = app(\App\Support\Format::class);
        $currencyOptions = collect(config('currencies'))->mapWithKeys(fn ($c, $code) => [$code => "{$c['symbol']}  {$c['name']} ({$code})"])->all();
        $numberOptions = ['comma_dot' => '1,234.56', 'dot_comma' => '1.234,56', 'space_comma' => "1\u{202F}234,56"];
        $sample = now()->setTime(21, 5);
        $dateOptions = collect(\App\Support\Format::DATE_FORMATS)->mapWithKeys(fn ($label, $format) => [$format => $sample->translatedFormat($format) . ($label !== 'Nov 3, 2026' ? "  ({$label})" : '')])->all();
        $timeOptions = ['12h' => $sample->translatedFormat('g:i A') . ' (12-hour)', '24h' => $sample->format('H:i') . ' (24-hour)'];
        // For the live preview
        $previewData = [
            'currencies' => collect(config('currencies'))->map(fn ($c) => ['symbol' => $c['symbol'], 'decimals' => $c['decimals']]),
            'numbers' => \App\Support\Format::NUMBER_FORMATS,
            'dates' => collect(\App\Support\Format::DATE_FORMATS)->mapWithKeys(fn ($l, $format) => [$format => $sample->translatedFormat($format)]),
            'times' => ['12h' => $sample->translatedFormat('g:i A'), '24h' => $sample->format('H:i')],
            'saved' => $fmt->currency,
        ];
    @endphp
    <section class="card" id="region" aria-labelledby="region-title">
        <header class="card-header">
            <div>
                <h2 class="card-title" id="region-title"><i class="fas fa-earth-asia" aria-hidden="true"></i> Currency &amp; formats</h2>
                <p class="card-subtitle">Used on every screen, receipt and export</p>
            </div>
            <span class="badge badge-info">{{ $fmt->currency }} {{ currency_symbol() }}</span>
        </header>
        <form method="POST" action="{{ route('settings.region') }}" id="regionForm">
            @csrf
            <div class="card-body form-grid">
                <x-field name="currency" label="Currency" :options="$currencyOptions" :value="$fmt->currency" />
                <div class="form-row">
                    <x-field name="number_format" label="Numbers" :options="$numberOptions" :value="$fmt->numberFormat" />
                    <x-field name="date_format" label="Dates" :options="$dateOptions" :value="$fmt->dateFormat" />
                </div>
                <x-field name="time_format" label="Time" type="segmented" :options="$timeOptions" :value="$fmt->timeFormat" />
                <div class="region-preview" aria-live="polite">
                    <span class="eyebrow">Preview</span>
                    <div><strong id="previewMoney">{{ money(1250) }}</strong> · <span id="previewDate">{{ fmt_date($sample) }}</span> · <span id="previewTime">{{ fmt_time($sample) }}</span></div>
                </div>
                <div class="alert alert-warning" id="currencyWarning" hidden>
                    <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
                    <span>Prices are not converted: a {{ money(180) }} service becomes <strong id="previewConverted"></strong>. Update service, add-on and product prices after switching. Orders already taken keep their currency.</span>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk" aria-hidden="true"></i> Save currency &amp; formats</button>
            </div>
        </form>
    </section>

</div>

    <section class="card" id="backups" aria-labelledby="backup-title">
        <header class="card-header">
            <div>
                <h2 class="card-title" id="backup-title"><i class="fas fa-database" aria-hidden="true"></i> Backups</h2>
                <p class="card-subtitle">A copy is saved on this computer automatically once a day</p>
            </div>
        </header>
        <div class="card-body stack-sm">
            <div class="backup-status">
                @if(!empty($lastBackup))
                    <span class="bubble-tile tone-green" aria-hidden="true"><i class="fas fa-circle-check"></i></span>
                    <div>
                        <strong>Last backup {{ \Carbon\Carbon::createFromTimestamp($lastBackup)->diffForHumans() }}</strong>
                        <div class="muted">{{ \Carbon\Carbon::createFromTimestamp($lastBackup)->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</div>
                    </div>
                @else
                    <span class="bubble-tile tone-amber" aria-hidden="true"><i class="fas fa-triangle-exclamation"></i></span>
                    <div><strong>No backup yet</strong><div class="muted">Download one now and keep it somewhere safe.</div></div>
                @endif
            </div>
            <a href="{{ route('backup.download') }}" class="btn btn-primary btn-lg"><i class="fas fa-download" aria-hidden="true"></i> Download a backup</a>
            <p class="help">The file (.sql) holds all orders, customers and settings. Keep a copy off this computer too, on a USB drive or cloud storage.</p>

            <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data" class="restore-box"
                  data-confirm="Everything in the app now will be replaced with the backup. A safety copy of the current data is saved first." data-confirm-title="Restore this backup?" data-confirm-button="Restore">
                @csrf
                <strong><i class="fas fa-rotate-left" aria-hidden="true"></i> Restore from a backup</strong>
                <x-field name="backup_file" label="Backup file" type="file" accept=".sql" required help="Only .sql files downloaded from this page." />
                <label class="check"><input type="checkbox" name="confirm" value="1" required> I understand this replaces all current data</label>
                <div><button type="submit" class="btn btn-danger"><i class="fas fa-upload" aria-hidden="true"></i> Restore backup</button></div>
            </form>
        </div>
    </section>
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
