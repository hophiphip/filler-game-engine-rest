<?php

namespace App\Models;

// TODO: Different color representation: 
//      Colors can be represented not only in hex form ->
//          add support for colors: [red, green, ... ]

final class Colors {
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

    // TODO:  `Color-pair` is not enough. Need `color-set`: 
    //  Yeah, color pair is cool, but what about case when player's first move is
    //      blocked -> neighbour cell has the same color as any of the players
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
}
