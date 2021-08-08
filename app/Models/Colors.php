<?php

namespace App\Models;

final class Colors {
    // Should match all colors from $colorsTable (case insensitive)
    public static $colorsRegex = '/^#([0-9a-f]{6})$|^red$|^green$|^blue$|^yellow$|^magenta$|^cyan$|^white$/i';

    public static $colorsTable = array(
        '#ff0000' => 0,
        '#00ff00' => 1,
        '#0000ff' => 2,
        '#ffff00' => 3,
        '#ff00ff' => 4,
        '#00ffff' => 5,
        '#ffffff' => 6,

            'red' => 0,
          'green' => 1,
           'blue' => 2,
         'yellow' => 3,
        'magenta' => 4,
           'cyan' => 5,
          'white' => 6,
    );   
    

    public static $colors = array(
        0 => '#ff0000',
        1 => '#00ff00',
        2 => '#0000ff',
        3 => '#ffff00',
        4 => '#ff00ff',
        5 => '#00ffff',
        6 => '#ffffff',
    );

    public static function randomColorString(): string {
        return self::$colors[random_int(0, count(self::$colors) - 1)];
    }

    public static function shuffledColors(): array {
        $out = array();
        $out = self::$colors;

        shuffle($out);
        return $out;
    }

    public static function compareColors(string $_r, string $_l): bool {
        $r = strtolower($_r);
        $l = strtolower($_l);
        if (!array_key_exists($r, self::$colorsTable) || !array_key_exists($l, self::$colorsTable))
            return false;

        return self::$colorsTable[$r] == self::$colorsTable[$l];
    }
}
