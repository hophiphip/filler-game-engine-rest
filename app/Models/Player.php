<?php

namespace App\Models;

use App\Traits\FromNamedArray;

class Player {
    use FromNamedArray;

    public int $id;
    public string $color;

    public function __construct(int $id, string $color) {
        $this->id = $id;
        $this->color = $color;
    }
}
