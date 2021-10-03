<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Colors;
use App\Models\Player;
use App\Models\Field;

class ApiTest extends TestCase
{
    /**
     * Get non existent game.
     *
     * @return void
     */
    public function testGetNonExistentGame()
    {
        $response = $this->call('GET', '/api/game/aaaaaaaaaaaaaaaaaaaaaaaa');

        $response
            ->assertStatus(404)
            ->assertJson([
                'error' =>  'incorrect game id',        
            ]);
    }
    
    /**
     * Put a move in non existent game.
     *
     * @return void
     */
    public function testPutNonExistentGame()
    {
        $response = $this->call('PUT', '/api/game/aaaaaaaaaaaaaaaaaaaaaaaa', [
            'playerId' => 1,
            'color' => 'red',
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'error' =>  'incorrect game id',        
            ]);
    }


    /**
     * Test new game creation with missing `width`.
     */
    public function testPostNewGameWithMissingWidth()
    {
        $response = $this->json('POST', '/api/game', [
            'height' => 15,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test new game creation with missing `height`.
     */
    public function testPostNewGameWithMissingHeight()
    {
        $response = $this->json('POST', '/api/game', [
            'width' => 25,
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test new game creation with incorrect (even) `height`.
     */
    public function testPostNewGameWithIncorrectHeight()
    {
        $response = $this->json('POST', '/api/game', [
            'width' => 25,
            'height' => 14,
        ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'error' => 'incorrect field size'
            ]);
    }

    /**
     * Test new game creation.
     *
     * @return array
     */
    public function testPostNewGame(): array
    {
        $response = $this->json('POST', '/api/game', [
            'width' => 25,
            'height' => 15,
        ]);

        $response
            ->assertStatus(201);

        $this->assertTrue($response['id'] != null);

        return [
            'id' => $response['id'],
        ];
    }

    /**
     * Test getting existing game status.
     *
     * @depends testPostNewGame
     *
     * @return array
     */
    public function testGetNewGame(array $postResponse): array
    {
        $response = $this->json('GET', '/api/game/' . $postResponse['id']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $postResponse['id'],
                'currentPlayerId' => 1,
                'winnerPlayerId' => 0,
            ]);

        return [
            'id' => $response['id'],
            'players' => $response['players'],
            'currentPlayerId' => $response['currentPlayerId'],
        ];
    }
    
    /**
     * Test putting a new game move.
     *
     * @depends testGetNewGame
     *
     * @return array
     */
    public function testPutGameMove(array $getResponse): array
    {
        $allowedColor = Colors::allowedColor(
            $getResponse['players'][1]['color'],
            $getResponse['players'][2]['color']
        );
        
        $this->assertTrue($allowedColor != null);

        $response = $this->json('PUT', '/api/game/' . $getResponse['id'], [
            'playerId' => $getResponse['currentPlayerId'],
            'color' => $allowedColor, 
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'id' => $getResponse['id'],
                'currentPlayerId' => Player::nextPlayerId($getResponse['currentPlayerId']),
            ]);

        return [
            'id' => $response['id'],
            'players' => $response['players'],
            'currentPlayerId' => $response['currentPlayerId'],
        ];
    }
    
    /**
     * Test putting a new game move with current player color.
     *
     * @depends testPutGameMove
     *
     * @return array
     */
    public function testPutGameMoveWithCurrentPlayerColor(array $putResponse): array
    {
        $response = $this->json('PUT', '/api/game/' . $putResponse['id'], [
            'playerId' => $putResponse['currentPlayerId'],
            'color' => $putResponse['players'][$putResponse['currentPlayerId']]['color'], 
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'error' => 'provided player can\'t choose this color',
            ]);
        
        return [
            'id' => $putResponse['id'],
            'players' => $putResponse['players'],
            'currentPlayerId' => $putResponse['currentPlayerId'],
        ];
    }
    
    /**
     * Test putting a new game move with other player color.
     *
     * @depends testPutGameMoveWithCurrentPlayerColor
     *
     * @return array
     */
    public function testPutGameMoveWithOtherPlayerColor(array $putResponse): array
    {
        $response = $this->json('PUT', '/api/game/' . $putResponse['id'], [
            'playerId' => $putResponse['currentPlayerId'],
            'color' => $putResponse['players'][Player::nextPlayerId($putResponse['currentPlayerId'])]['color'], 
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'error' => 'provided player can\'t choose this color',
            ]);
        
        return [
            'id' => $putResponse['id'],
            'players' => $putResponse['players'],
            'currentPlayerId' => $putResponse['currentPlayerId'],
        ];
    }


    /**
     * Test putting a new game move with other player.
     *
     * @depends testPutGameMoveWithOtherPlayerColor
     *
     * @return array
     */
    public function testPutGameMoveWithOtherPlayer(array $putResponse): array
    {
        $response = $this->json('PUT', '/api/game/' . $putResponse['id'], [
            'playerId' => Player::nextPlayerId($putResponse['currentPlayerId']),
            'color' => $putResponse['players'][Player::nextPlayerId($putResponse['currentPlayerId'])]['color'], 
        ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'error' => 'provided player can\'t move right now',
            ]);
        
        return [
            'id' => $putResponse['id'],
            'players' => $putResponse['players'],
            'currentPlayerId' => $putResponse['currentPlayerId'],
        ];
    }

    /**
     * Test putting a new game move with unsupported color.
     *
     * @depends testPutGameMoveWithOtherPlayer
     *
     * @return array
     */
    public function testPutGameMoveWithUnsupportedColor(array $putResponse): array
    {
        $response = $this->json('PUT', '/api/game/' . $putResponse['id'], [
            'playerId' => $putResponse['currentPlayerId'],
            'color' => 'orange', 
        ]);

        $response
            ->assertStatus(400);
        
        return [
            'id' => $putResponse['id'],
            'players' => $putResponse['players'],
            'currentPlayerId' => $putResponse['currentPlayerId'],
        ];
    }


    /**
     * Perform PUT request (with handling HTTP 429 error)
     *
     * @param arrray $params PUT request parameters
     * @param int $timeout timeout in microseconds
     *
     * @return Illuminate\Testing\TestResponse
     */
    public function performPut(array $params, int $timeout)
    {
        $backupResponse = $this->json('PUT', '/api/game/' . $params['id'], [
            'playerId' => $params['currentPlayerId'],
            'color' => $params['nextColor'], 
        ]);

        // handle timeout in case of too many requests
        while ($backupResponse->status() == 429) {
            $backupResponse = $this->json('PUT', '/api/game/' . $params['id'], [
                'playerId' => $params['currentPlayerId'],
                'color' => $params['nextColor'], 
            ]);

            usleep($timeout);
        };

        return $backupResponse;
    }

    /**
     * Trying to complete a game (not optimal way).
     *
     * @depends testPutGameMoveWithUnsupportedColor
     *
     * @return array
     */
    public function testCompleteAGame(array $putResponse): array
    {
        $response = $this->json('GET', '/api/game/' . $putResponse['id']);

        $response
            ->assertStatus(200);

        // Initialze player stats
        $stats = [
            1 => [],
            2 => [],
        ];
        foreach ($response['field']['cells'] as $i => $cell) {
            if ($cell['playerId'] != 0) {
                array_push($stats[$cell['playerId']], $i);
            }
        }

        $moves = 0;
        while ($response['winnerPlayerId'] == 0) {
            $field = Field::fromArray($response['field']);
            
            // map color to cell index
            $colorStats = [
                0 => [],   
                1 => [],   
                2 => [],   
                3 => [],   
                4 => [],   
                5 => [],   
                6 => [],   
            ];

            // TODO: Must check cells recuresively/in a loop (neighbour of a neighbour)

            //foreach ($stats[$response['currentPlayerId']] as $i) {
            foreach ($field->cells as $i => $cell) {
                if ($cell['playerId'] == $response['currentPlayerId']) {
                // left
                if (!($field->hasNoLeftCell($i))) {
                    $leftIndex = $i - $field->width;
                    if ($field->isNotPlayerCell($leftIndex)) {
                        $key = Colors::$colorsTable[$field->cells[$leftIndex]["color"]]; 
                        array_push($colorStats[$key], $leftIndex);
                    }
                }
                
                // top
                if (!($field->hasNoTopCell($i))) {
                    $topIndex = $i - $field->width + 1;
                    if ($field->isNotPlayerCell($topIndex)) {
                        $key = Colors::$colorsTable[$field->cells[$topIndex]["color"]]; 
                        array_push($colorStats[$key], $topIndex);
                    }
                }
                
                // right
                if (!($field->hasNoRightCell($i))) {
                    $rightIndex = $i + $field->width;
                    if ($field->isNotPlayerCell($rightIndex)) {
                        $key = Colors::$colorsTable[$field->cells[$rightIndex]["color"]]; 
                        array_push($colorStats[$key], $rightIndex);
                    }
                }
                    
                // bottom
                if (!($field->hasNoBottomCell($i))) {
                    $bottomIndex = $i + $field->width - 1;
                    if ($field->isNotPlayerCell($bottomIndex)) {
                        $key = Colors::$colorsTable[$field->cells[$bottomIndex]["color"]]; 
                        array_push($colorStats[$key], $bottomIndex);
                    }
                }
            }
            }

            // Get rid of player colors
            unset($colorStats[Colors::$colorsTable[$response['players'][1]['color']]]);
            unset($colorStats[Colors::$colorsTable[$response['players'][2]['color']]]);

            // Not needed, but just in case
            $this->assertTrue(count($colorStats) == 5);

            // Cells must be unique
            foreach ($colorStats as $colorKey => $colorStat) {
                $colorStats[$colorKey] = array_unique($colorStat);
            }

            // Get the most popular color
            $maxIdx = array_key_first($colorStats);
            foreach ($colorStats as $colorKey => $cells) {
                if (count($colorStats[$maxIdx]) < count($cells)) {
                    $maxIdx = $colorKey;
                }
            }
            $nextColor = Colors::$colors[$maxIdx];

            // Merge new cells into stats
            $stats[$response['currentPlayerId']] = 
                array_merge($stats[$response['currentPlayerId']], $colorStats[$maxIdx]);

            // Test for duplicates
            if(count($stats[$response['currentPlayerId']]) != count(array_unique($stats[$response['currentPlayerId']]))) {
                var_dump($stats);
                var_dump($colorStats);
                var_dump($nextColor);
                var_dump($response['players'][$response['currentPlayerId']]);
            }

            $this->assertTrue(
                count($stats[$response['currentPlayerId']]) == count(array_unique($stats[$response['currentPlayerId']]))
            );

            // Cleanup
            unset($colorStats);

            // NOTE: this call blocks
            $response = $this->performPut([
                'id' => $response['id'],
                'currentPlayerId' => $response['currentPlayerId'],
                'nextColor' => $nextColor,
            ], 1000);

            $response
                ->assertStatus(201);

            $moves++;
        }

        var_dump(["move count" => $moves]);

        return [
            'id' => $response['id'],
        ];
    }

}
