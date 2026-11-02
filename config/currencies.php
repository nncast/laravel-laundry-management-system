<?php

/*
| Currencies the business can pick under Settings → Region.
| To add one, add a line: ISO 4217 code => name, the symbol shown before amounts,
| digits after the decimal point, and the bills/notes offered as quick cash at the POS.
*/
return [
    'PHP' => ['name' => 'Philippine Peso', 'symbol' => '₱', 'decimals' => 2, 'bills' => [100, 200, 500, 1000]],
    'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2, 'bills' => [5, 10, 20, 50, 100]],
    'EUR' => ['name' => 'Euro', 'symbol' => '€', 'decimals' => 2, 'bills' => [5, 10, 20, 50, 100]],
    'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'decimals' => 0, 'bills' => [1000, 2000, 5000, 10000]],
    'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'decimals' => 2, 'bills' => [5, 10, 20, 50]],
    'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'decimals' => 2, 'bills' => [5, 10, 20, 50, 100]],
    'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'decimals' => 2, 'bills' => [5, 10, 20, 50, 100]],
    'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'decimals' => 2, 'bills' => [2, 5, 10, 50, 100]],
    'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'decimals' => 2, 'bills' => [5, 10, 20, 50, 100]],
    // The rupiah has no coins in use below 100, so amounts are shown without decimals
    'IDR' => ['name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'decimals' => 0, 'bills' => [10000, 20000, 50000, 100000]],
];
