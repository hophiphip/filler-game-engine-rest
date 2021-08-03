<?php

namespace App\Models;

class Cell {
    public int $playerId;
    public string $color;

    public function __construct(int $playerId, string $color) {
        $this->playerId = $playerId;
        $this->color = $color;
    }
}
