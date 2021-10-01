<?php

namespace App\Models;

use App\Traits\FromNamedArray;

/**
 *  This class stores player representation.
 */
class Player {
    use FromNamedArray;

    /**
     *  @var int $id player in game id
     */
    public int $id;

    /**
     *  @var string $color player current color
     */
    public string $color;

    /**
     *  Construct a new player.
     *
     *  @param int $id player id
     *  @param string $color player initial color
     *
     *  @return Player
     */
    public function __construct(int $id, string $color) {
        $this->id = $id;
        $this->color = $color;
    }
}
