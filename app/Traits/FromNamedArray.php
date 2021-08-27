<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

use App\Models\Cell;

// TODO: But subclasses are still arrays .. update trait to make subclasses accesssible or implement AccessArray trait on Models to prevent possible errors 

// TODO: Mb. fail on missing key -> or add flag to fail on missing key

trait FromNamedArray {
    public static function fromArray(array $data = []) {
        foreach (get_object_vars($object = new self) as $property => $default) {
            if (!array_key_exists($property, $data))
                continue;

            class_uses(Cell::class, true);

            $object->{$property} = $data[$property];
        }

        return $object;
    }
}
