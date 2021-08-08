<?php

namespace App\Models;

use App\Traits\FromNamedArray;
use Illuminate\Support\Facades\Log;

class Field {
    use FromNamedArray;

    public int $width;
    public int $height;
    public $cells = array();

    public function __construct(int $width = 0, int $height = 0) {
        $this->width = $width;
        $this->height = $height;

        // Field structure:
        //
        // NOTE: width  -> any
        //       height -> odd 
        //
        // o o o o
        //  o o o
        // o o o o
        //  o o o
        // o o o o
        //
        $this->cells = new \SplFixedArray($this->width * $this->height - intdiv($this->height, 2));

        for ($i = 0; $i < count($this->cells); $i++) {
            $this->cells[$i] = new Cell(0, Colors::randomColorString());
        }
    }

    public function isValidCell(int $y, int $x): bool {
        return ($x >= 0 && $x < $this->width) &&
               ($y >= 0 && $y < $this->height);
    }

    public function isValidIndex(int $i): bool {
        return $i >= 0 && $i < count($this->cells);
    }

    /* Leftmost cells
     *  x o o o
     *   o o o
     *  x o o o
     *   o o o
     *  x o o o
     *  
    */  
    public function isInLeftCorner(int $i): bool {
        return ($i % (2 * $this->width - 1)) == 0;
    }

    
    /* Top cells
     *  x x x x
     *   o o o
     *  o o o o
     *   o o o
     *  o o o o  
     *
     */ 
    public function isInTopCorner(int $i): bool {
        return $i < $this->width;
    }


    /* Rightmost cells
     *  o o o x
     *   o o o
     *  o o o x
     *   o o o
     *  o o o x
     *    
     */
    public function isInRightCorner(int $i): bool {
        // TODO: Might have some issues with negative numbers
        return (($i - $this->width + 1) % (2 * $this->width - 1)) == 0;
    }


    /* Bottom cells
     *  o o o o
     *   o o o
     *  o o o o
     *   o o o
     *  x x x x
     *    
     */
    public function isInBottomCorner(int $i): bool {
        $cellCount = count($this->cells); 
        return ($i < $cellCount) &&
               ($i >= ($cellCount - $this->width)); 
    }


    /*
     *  Cell corners:
     *    [right]      [top]       
     *            c
     *  [bottom]     [left]     
     *
     */

    /* 
     *  x o o o
     *   o o o
     *  x o o o
     *   o o o
     *  x x x x
     *    
     */
    public function hasNoBottomCell(int $i): bool {
        return $this->isInLeftCorner($i) ||
               $this->isInBottomCorner($i);
    }

    /* 
     *  x x x x
     *   o o o
     *  x o o o
     *   o o o
     *  x o o o
     *    
     */
    public function hasNoLeftCell(int $i): bool {
        return $this->isInLeftCorner($i) ||
               $this->isInTopCorner($i);
    }

    /* 
     *  x x x x
     *   o o o
     *  o o o x
     *   o o o
     *  o o o x
     *    
     */
    public function hasNoTopCell(int $i): bool {
        return $this->isInTopCorner($i) ||
               $this->isInRightCorner($i);
    }
        
    /* 
     *  o o o x
     *   o o o
     *  o o o x
     *   o o o
     *  x x x x
     *    
     */
    public function hasNoRightCell(int $i): bool {
        return $this->isInRightCorner($i) ||
               $this->isInBottomCorner($i);
    }

    // TODO: Could be inlined / but leave it like that for possible logging/testing stuff 
    // Test if cell can be colored | assigned to player
    public function isAssignable(int $currentIndex, int $otherIndex, string $playerColor): bool {
        if ($this->isValidIndex($currentIndex)) {
            if ($this->isValidIndex($otherIndex)) {
                if ($this->cells[$currentIndex]["playerId"] != 
                    $this->cells[$otherIndex]["playerId"]) {
                    if (strcasecmp($playerColor, $this->cells[$otherIndex]["color"]) == 0) {
                        return true;
                    }
                }
            }
        }


        return false;
    }
}
