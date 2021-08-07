<?php

namespace App\Models;

// TODO: Different color representation: 
//      Colors can be represented not only in hex form ->
//          add support for colors: [red, green, ... ]

final class Colors {
    public static $colorsTable = array(
        '#ff0000' => 0,
        '#00ff00' => 1,
        '#0000ff' => 2,
        '#ffff00' => 3,
        '#ff00ff' => 4,
        '#00ffff' => 5,
        '#ffffff' => 6,

        'red'     => 0,
        'green'   => 1,
        'blue'    => 2,
        'yellow'  => 3,
        'magenta' => 4,
        'cyan'    => 5,
        'white'   => 6,
    )   
    

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

    // NOTE: Not needed, use shuffledColors
    public static function randomColorPair(): array {
        $one = 0;
        $two = 0;
        while ($one == $two) {
            $one = random_int(0, count(self::$colors) - 1);
            $two = random_int(0, count(self::$colors) - 1);
        }

        return array(
            1 => self::$colors[$one],
            2 => self::$colors[$two],
        );
    }

    public static function shuffledColors(): array {
        $out = array();
        $out = self::$colors;

        shuffle($out);
        return $out;
    }
}
