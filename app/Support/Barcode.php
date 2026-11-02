<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Code 128 barcodes (character set B: printable ASCII) as SVG, for the order
 * number on tickets. Any USB barcode scanner reads them; it types the number
 * into the search box, which opens the order.
 */
class Barcode
{
    /** Bar and space widths of each symbol value 0-106 (103-105 = start A/B/C, 106 = stop). */
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    /** The symbol values for $text: start B, one per character, checksum, stop. */
    public static function code128Values(string $text): array
    {
        $values = [104];
        foreach (str_split($text) as $char) {
            if (ord($char) < 32 || ord($char) > 126) {
                throw new InvalidArgumentException('A Code 128 barcode here holds printable ASCII only.');
            }
            $values[] = ord($char) - 32;
        }

        $checksum = 104;
        foreach (array_slice($values, 1) as $position => $value) {
            $checksum += $value * ($position + 1);
        }

        return [...$values, $checksum % 103, 106];
    }

    /**
     * The barcode as an <img>-ready data: URI, 12 mm tall, with a quiet zone of
     * 10 modules each side. A module is 0.25 mm: two dots on a 203 dpi receipt
     * printer, the narrowest bar receipt printers and scanners agree on. The
     * order number comes to about 60 mm.
     */
    public static function code128(string $text): string
    {
        $x = 10;
        $bars = '';
        foreach (self::code128Values($text) as $value) {
            foreach (str_split(self::PATTERNS[$value]) as $i => $width) {
                if ($i % 2 === 0) {
                    $bars .= "<rect x=\"{$x}\" width=\"{$width}\" height=\"40\"/>";
                }
                $x += (int) $width;
            }
        }
        $width = $x + 10;
        $mm = $width / 4;

        return 'data:image/svg+xml;base64,' . base64_encode(
            "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$mm}mm\" height=\"12mm\" viewBox=\"0 0 {$width} 40\" preserveAspectRatio=\"none\" shape-rendering=\"crispEdges\">{$bars}</svg>"
        );
    }
}
