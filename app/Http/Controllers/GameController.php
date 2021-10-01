<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Log;

use App\Models\Colors;
use App\Models\Player;
use App\Models\Cell;
use App\Models\Field;
use App\Models\Game;

class GameController extends Controller {
    /**
     * Create a new game.
     *
     * @param  \Illuminate\Http\Request  $request contains `width` and `height` of the game field 
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request) {
        $request->validate([
            'width' => 'required|numeric|gt:2',
            'height' => 'required|numeric|gt:2',
        ]);
        
        $width = $request->input('width');
        $height = $request->input('height');

        if (($height % 2) == 0) {
            return response(json_encode(['error' => 'incorrect field size'], 400))
                    ->header('Content-Type', 'application/json');
        }

        $colors = Colors::shuffledColors(); 

        $playerOne = new Player(1, $colors[1]);
        $playerTwo = new Player(2, $colors[2]);

        $field = new Field($width, $height);

        // Initialize players staring cells:
        //
        // 1st player starts with bottom-left cell
        $bottomLeftIndex = count($field->cells) - $field->width; 
        // 2nd player starts with top-right cell
        $topRightIndex = $field->width - 1; 
        
        $field->cells[$bottomLeftIndex]->playerId = $playerOne->id;
        $field->cells[$bottomLeftIndex]->color = $playerOne->color;
        
        $field->cells[$topRightIndex]->playerId = $playerTwo->id;
        $field->cells[$topRightIndex]->color = $playerTwo->color;

        // Neighbour cells must differ from initial player cells
        //
        // 1st player's neighbour cell
        $field->cells[$bottomLeftIndex - $field->width + 1]->color = $colors[3];
        // 2nd player's neighbour cell
        $field->cells[$topRightIndex + $field->width - 1]->color = $colors[4];

        $game = Game::create([
            'currentPlayerId' => 1,
            'winnerPlayerId' => 0,
            'field' => $field,
            'players' => [
                1 => $playerOne,
                2 => $playerTwo,
            ],
            'stats' => [
                1 => [$bottomLeftIndex],
                2 => [$topRightIndex],
            ],
        ]);

        return response(json_encode([
            'id' => $game->id,
        ]), 201)->header('Content-Type', 'application/json');
    }

    /**
     * Get game via ID.
     *
     * @param  string  $id unique game Id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id) {
        if ($id == null) {
            return response(json_encode([
                "error" => "incorrect request parameters",
            ]), 400)->header('Content-Type', 'application/json');
        }

        $game = Game::find($id);

        if ($game) {
            return response(json_encode([
                'id'              => $game->id,
                'currentPlayerId' => $game->currentPlayerId,
                'winnerPlayerId'  => $game->winnerPlayerId,
                'players'         => $game->players,
                'field'           => $game->field,
            ]), 200)->header('Content-Type', 'application/json');
        } else {
            return response(json_encode([
                "error" => "incorrect game id",
            ]), 404)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Make a player move.
     *
     * @param  \Illuminate\Http\Request  $request contains `playerId` and player `color`
     * @param  string  $id unique game Id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [ 
            'playerId' => 'required|numeric|max:2|min:1',
            'color' => [
                'required',
                'regex:' . Colors::$colorsRegex, 
            ],
        ]);

        if ($validator->fails()) {    
            return response()->json([
                $validator->messages(),
            ], 400)->header('Content-Type', 'application/json');
        }
        
        if ($id == null) {
            return response(json_encode([
                "error" => "incorrect request parameters",
            ]), 400)->header('Content-Type', 'application/json');
        }
        
        $playerId = $request->input('playerId');
        $color = $request->input('color');
        $game = Game::find($id);

        if ($game) {
            // Check & Handle incorrect player id
            if ($game->currentPlayerId != $playerId) {
                return response(json_encode([
                    "error" => "provided player can't move right now",
                ]), 403)->header('Content-Type', 'application/json');
            }
            // Handle player move
            else {
                $updateStatus = $game->handleMove($color);

                if ($updateStatus == false) {
                    return response(json_encode([
                        "error" => "provided player can't choose this color",
                    ]), 409)->header('Content-Type', 'application/json');
                }

                return response(json_encode([
                    'id'              => $game->id,
                    'currentPlayerId' => $game->currentPlayerId,
                    'winnerPlayerId'  => $game->winnerPlayerId,
                    'players'         => $game->players,
                    'field'           => $game->field,
                ]), 201)->header('Content-Type', 'application/json');
            } 
        } else {
            return response(json_encode([
                "error" => "incorrect game id",
            ]), 404)->header('Content-Type', 'application/json');
        }
    }
}
