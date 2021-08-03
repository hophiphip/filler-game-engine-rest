<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

use Illuminate\Support\Facades\Log;

// TODO: Add new game creation initializer and not do all this in controller

class Game extends Model {
    protected $collection = 'game_collection';

    protected $fillable = [
        // 'Public' - part of the model
        'players',
        'field',
        'currentPlayerId',
        'winnerPlayerId',

        // 'Private' - only for API usage
        // Stores players progres (array of player cells indexes)
        'stats'  
    ];

    // Updates model by itself
    // get -> update -> set
    public function handleMove(string $_color) {
        $color = strtolower($_color);

        // GET
        $currentPlayerId = $this->currentPlayerId;
        $players = $this->players;
        $field = Field::fromArray($this->field);
        $stats = $this->stats;

        // UPDATE
        // update player color
        $players[$currentPlayerId]["color"] = $color;

        // update cells field & stats : IDs
        for ($i = 0; $i < count($stats[$currentPlayerId]); $i++) {
            $cellIndex = $stats[$currentPlayerId][$i];

            // left 
            if (!($field->hasNoLeftCell($cellIndex))) {
                $leftIndex = $cellIndex - $field->width;
                if ($field->isAssignable($cellIndex, $leftIndex, $color)) { 
                    // assign other cell to current player id
                    $field->cells[$leftIndex]["playerId"] = $field->cells[$cellIndex]["playerId"];
                    // add other cell to current player cells
                    array_push($stats[$currentPlayerId], $leftIndex);
                }
            }

            // top
            if (!($field->hasNoTopCell($cellIndex))) {
                $topIndex = $cellIndex - $field->width + 1;
                if ($field->isAssignable($cellIndex, $topIndex, $color)) { 
                    // assign other cell to current player id
                    $field->cells[$topIndex]["playerId"] = $field->cells[$cellIndex]["playerId"];
                    // add other cell to current player cells
                    array_push($stats[$currentPlayerId], $topIndex);
                }
            }

            // right
            if (!($field->hasNoRightCell($cellIndex))) {
                $rightIndex = $cellIndex + $field->width;
                if ($field->isAssignable($cellIndex, $rightIndex, $color)) { 
                    // assign other cell to current player id
                    $field->cells[$rightIndex]["playerId"] = $field->cells[$cellIndex]["playerId"];
                    // add other cell to current player cells
                    array_push($stats[$currentPlayerId], $rightIndex);
                }
            }

            // bottom
            if (!($field->hasNoBottomCell($cellIndex))) {
                $bottomIndex = $cellIndex + $field->width - 1;
                if ($field->isAssignable($cellIndex, $bottomIndex, $color)) { 
                    // assign other cell to current player id
                    $field->cells[$bottomIndex]["playerId"] = $field->cells[$cellIndex]["playerId"];
                    // add other cell to current player cells
                    array_push($stats[$currentPlayerId], $bottomIndex);
                }
            }
        } 

        // update field : cells color
        foreach ($stats[$currentPlayerId] as $cellIndex) {
            // NOTE: cell[i] is still an array
            $field->cells[$cellIndex]["playerId"] = $currentPlayerId;
            $field->cells[$cellIndex]["color"] = $color;
        }

        // update current player id : next player turn
        $currentPlayerId = (($currentPlayerId % 2) + 1);


        // SET
        // TODO: Too many too big updates --> can be improved --> update inly specific field/subfields
        // TODO: Try using DB array methods -> push & pull (with 'stats')
        $this->currentPlayerId = $currentPlayerId;
        $this->players = $players;
        $this->field = $field;
        $this->stats = $stats;
        $this->save(); 
    }
}
