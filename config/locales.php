<?php

/*
 * The languages the app can show. To add one, add it here and create
 * lang/{code}.json (copy lang/fil.json, keep the keys, translate the values)
 * and lang/{code}/validation.php for form errors; then switch it on under
 * Settings → Language. `php artisan test` checks every key is translated.
 *
 * html: the code for <html lang>. dir: ltr, or rtl for right-to-left
 * scripts. number and date: what "Automatic" picks under Settings →
 * Currency & formats (Format::NUMBER_FORMATS ids, PHP date formats).
 */
return [
    'en' => ['name' => 'English', 'native' => 'English', 'html' => 'en', 'dir' => 'ltr', 'number' => 'comma_dot', 'date' => 'M j, Y'],
    'fil' => ['name' => 'Filipino', 'native' => 'Filipino', 'html' => 'fil', 'dir' => 'ltr', 'number' => 'comma_dot', 'date' => 'M j, Y'],
    'es' => ['name' => 'Spanish', 'native' => 'Español', 'html' => 'es', 'dir' => 'ltr', 'number' => 'dot_comma', 'date' => 'j M Y'],
    'ja' => ['name' => 'Japanese', 'native' => '日本語', 'html' => 'ja', 'dir' => 'ltr', 'number' => 'comma_dot', 'date' => 'Y年n月j日'],
    'zh_CN' => ['name' => 'Chinese (Simplified)', 'native' => '简体中文', 'html' => 'zh-CN', 'dir' => 'ltr', 'number' => 'comma_dot', 'date' => 'Y年n月j日'],
    'id' => ['name' => 'Indonesian', 'native' => 'Bahasa Indonesia', 'html' => 'id', 'dir' => 'ltr', 'number' => 'dot_comma', 'date' => 'j M Y'],
];
