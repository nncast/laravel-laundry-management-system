<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SystemSetting;
use App\Support\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SystemSettingController extends Controller
{
    public function edit()
    {
        $settings = SystemSetting::first() ?? new SystemSetting();
        $lastBackup = BackupController::lastBackupTime();
        $latestOrderId = Order::max('id');   // for the receipt preview

        return view('settings-mastersettings', compact('settings', 'lastBackup', 'latestOrderId'));
    }

    /**
     * What receipts, claim tickets and job orders print, and the POS's ready-by default.
     */
    public function updateReceipts(Request $request)
    {
        $validated = $request->validate([
            'receipt_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:300',
            'receipt_header' => 'nullable|string|max:500',
            'receipt_thank_you' => 'nullable|string|max:255',
            'receipt_footer' => 'nullable|string|max:500',
            'receipt_pickup_instructions' => 'nullable|string|max:1000',
            'receipt_terms' => 'nullable|string|max:2000',
            'receipt_paper' => ['required', Rule::in(array_keys(OrderDocumentController::PAPERS))],
            'turnaround_days' => 'required|integer|min:0|max:30',
        ], [
            'receipt_logo.max' => __('The logo must be smaller than 300 KB.'),
        ]);

        $settings = SystemSetting::first() ?? new SystemSetting();
        $settings->fill(Arr::except($validated, 'receipt_logo'));
        $settings->receipt_show_qr = $request->boolean('receipt_show_qr');
        $settings->receipt_show_barcode = $request->boolean('receipt_show_barcode');

        // The logo lives in storage/app (documents embed it), replacing any earlier one
        if ($request->boolean('remove_logo') || $request->hasFile('receipt_logo')) {
            if ($settings->receipt_logo) {
                File::delete(storage_path('app/' . $settings->receipt_logo));
            }
            $settings->receipt_logo = null;
        }
        if ($request->hasFile('receipt_logo')) {
            $name = 'receipt-logo.' . $request->file('receipt_logo')->extension();
            $request->file('receipt_logo')->move(storage_path('app'), $name);
            $settings->receipt_logo = $name;
        }

        $settings->save();

        return redirect()->to(route('settings.mastersettings') . '#receipts')->with('success', __('Receipt settings saved.'));
    }

    /**
     * The languages staff can pick, the shop's default and the fallback for missing translations.
     */
    public function updateLanguage(Request $request)
    {
        $available = array_keys(config('locales'));
        $validated = $request->validate([
            'enabled_locales' => 'required|array|min:1',
            'enabled_locales.*' => ['string', Rule::in($available)],
            'default_locale' => ['required', Rule::in((array) $request->input('enabled_locales'))],
            'fallback_locale' => ['required', Rule::in($available)],
        ], [
            'enabled_locales.required' => __('Pick at least one language.'),
            'default_locale.in' => __('The default language must be one staff can pick.'),
        ]);

        $settings = SystemSetting::first() ?? new SystemSetting();
        $settings->fill([
            'enabled_locales' => array_values(array_intersect($available, $validated['enabled_locales'])),
            'default_locale' => $validated['default_locale'],
            'fallback_locale' => $validated['fallback_locale'],
        ])->save();

        return redirect()->to(route('settings.mastersettings') . '#language')->with('success', __('Language settings saved.'));
    }

    /**
     * Currency and number/date/time formats (Settings → Currency & formats).
     * Changing the currency does not convert prices; orders keep the currency they were taken in.
     */
    public function updateRegion(Request $request)
    {
        $validated = $request->validate([
            'currency' => ['required', Rule::in(array_keys(config('currencies')))],
            'number_format' => ['required', Rule::in([Format::AUTO, ...array_keys(Format::NUMBER_FORMATS)])],
            'date_format' => ['required', Rule::in([Format::AUTO, ...array_keys(Format::DATE_FORMATS)])],
            'time_format' => ['required', Rule::in(array_keys(Format::TIME_FORMATS))],
        ]);

        $settings = SystemSetting::first() ?? new SystemSetting();
        $currencyChanged = ($settings->currency ?? 'PHP') !== $validated['currency'];
        $settings->fill($validated)->save();

        return redirect()->to(route('settings.mastersettings') . '#region')->with('success', $currencyChanged
            ? __('Currency changed to :currency. Prices were not converted: check your service, add-on and product prices.', ['currency' => $validated['currency']])
            : __('Currency and formats saved.'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'contact' => ['nullable', 'regex:/^[0-9]{10,15}$/'],
            // .ico files are often reported with a generic MIME type, so check the extension
            'favicon' => 'nullable|file|extensions:ico|max:200',
        ], [
            'contact.regex' => __('Contact number must be 10 to 15 digits.'),
            'favicon.extensions' => __('The logo must be an .ico file.'),
            'favicon.max' => __('The logo must be smaller than 200 KB.'),
        ]);

        $settings = SystemSetting::first() ?? new SystemSetting();

        $settings->business_name = $validated['business_name'];
        $settings->address = $validated['address'] ?? null;
        $settings->contact = $validated['contact'] ?? null;

        if ($request->hasFile('favicon')) {
            $dir = public_path('images');
            File::ensureDirectoryExists($dir);

            try {
                $request->file('favicon')->move($dir, 'favicon.ico');
            } catch (\Throwable $e) {
                Log::error('Favicon upload failed: ' . $e->getMessage());

                return back()->withInput()
                    ->with('error', __('Settings were not saved: the logo could not be uploaded (:error).', ['error' => $e->getMessage()]));
            }

            $settings->favicon = 'images/favicon.ico';
        }

        $settings->save();

        return redirect()->back()->with('success', __('Settings updated successfully.'));
    }
}
