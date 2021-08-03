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

// TODO: UID(_id) is needed to be like xxxx.yyyy.zzzz ...
// TODO: Mb. return auth. token for the game ? --> not needed mb. in other branch

class GameController extends Controller {
    
    // POST - new game
    public function store(Request $request) {
        $request->validate([
            'width' => 'required|numeric|gt:2',
            'height' => 'required|numeric|gt:2',
        ]);
        
        $width = $request->input('width');
        $height = $request->input('height');

        if (($width % 2) == 0) {
            return response(json_encode(['error' => 'incorrect field size'], 400))
                    ->header('Content-Type', 'application/json');
        }

        $colorPair = Colors::randomColorPair();
        
        $playerOne = new Player(1, $colorPair[1]);
        $playerTwo = new Player(2, $colorPair[2]);

        //Log::channel('stderr')->info('Creating a new field..');
        $field = new Field($width, $height);

        // DEBUG:
        Log::channel('stderr')->info($field->width);
        // NOTE: This will fail, will need ArrayAccess or recursive FromNamedArray trait
        //Log::channel('stderr')->info($field["width"]);

        // Initialize players staring cells:
        // Bottom left cell for the 1st player
        $field->cells[count($field->cells) - $field->width]->playerId = $playerOne->id;
        $field->cells[count($field->cells) - $field->width]->color = $playerOne->color;
        // Top right cell for the 2nd player
        $field->cells[$field->width - 1]->playerId = $playerTwo->id;
        $field->cells[$field->width - 1]->color = $playerTwo->color;
        

        $game = Game::create([
            'currentPlayerId' => 1,
            'winnerPlayerId' => 0,
            'field' => $field,
            'players' => [
                1 => $playerOne,
                2 => $playerTwo,
            ],
            // players' cells
            'stats' => [
                1 => [count($field->cells) - $field->width],
                2 => [$field->width - 1],
            ],
        ]);

        return response(json_encode([
            'id' => $game->id,
        ]), 201)->header('Content-Type', 'application/json');
    }

    // GET - game same state
    public function show($id) {
        // TODO: Improve incorect parameters handler
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

    // PUT - update game state / make a player move
    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [ 
            'playerId' => 'required|numeric|max:2|min:1',
            'color' => [
                'required',
                // match colors like #000000-#FFFFFF(#ffffff) / 6-letter ones
                'regex:/^#([0-9a-f]{6})$/i', 
            ],
        ]);

        // TODO: Improve incorect parameters handler
        // Handle incorrect parameters
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
            
            // Check & Handle incorrect color
            else if (
                // Players can't have same color
                strcasecmp($color, $game->players[($game->currentPlayerId % 2) + 1]['color']) == 0 ||
                // Player can't choose own color
                strcasecmp($color, $game->players[$game->currentPlayerId]['color']) == 0
            ){
                return response(json_encode([
                    "error" => "provided player can't choose this color",
                ]), 409)->header('Content-Type', 'application/json');
            }

            // Handle player move
            else {
                //Log::channel('stderr')->info("Game:");
                //Log::channel('stderr')->info($game->currentPlayerId);
        
                $game->handleMove($color);
                
                //Log::channel('stderr')->info($game->currentPlayerId);

                // TODO: mb. Return only updated cells ? 
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
