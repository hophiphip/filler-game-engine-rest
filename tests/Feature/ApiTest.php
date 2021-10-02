<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Colors;
use App\Models\Player;

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
    public function testPutGameMove(array $getRequest): array
    {
        $allowedColor = Colors::allowedColor(
            $getRequest['players'][1]['color'],
            $getRequest['players'][2]['color']
        );
        
        $this->assertTrue($allowedColor != null);

        $response = $this->json('PUT', '/api/game/' . $getRequest['id'], [
            'playerId' => $getRequest['currentPlayerId'],
            'color' => $allowedColor, 
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'id' => $getRequest['id'],
                'currentPlayerId' => Player::nextPlayerId($getRequest['currentPlayerId']),
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
    public function testPutGameMoveWithCurrentPlayerColor(array $putRequest): array
    {
        $response = $this->json('PUT', '/api/game/' . $putRequest['id'], [
            'playerId' => $putRequest['currentPlayerId'],
            'color' => $putRequest['players'][$putRequest['currentPlayerId']]['color'], 
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'error' => 'provided player can\'t choose this color',
            ]);
        
        return [
            'id' => $putRequest['id'],
            'players' => $putRequest['players'],
            'currentPlayerId' => $putRequest['currentPlayerId'],
        ];
    }
    
    /**
     * Test putting a new game move with other player color.
     *
     * @depends testPutGameMoveWithCurrentPlayerColor
     *
     * @return array
     */
    public function testPutGameMoveWithOtherPlayerColor(array $putRequest): array
    {
        $response = $this->json('PUT', '/api/game/' . $putRequest['id'], [
            'playerId' => $putRequest['currentPlayerId'],
            'color' => $putRequest['players'][Player::nextPlayerId($putRequest['currentPlayerId'])]['color'], 
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'error' => 'provided player can\'t choose this color',
            ]);
        
        return [
            'id' => $putRequest['id'],
            'players' => $putRequest['players'],
            'currentPlayerId' => $putRequest['currentPlayerId'],
        ];
    }


    /**
     * Test putting a new game move with other player.
     *
     * @depends testPutGameMoveWithOtherPlayerColor
     *
     * @return array
     */
    public function testPutGameMoveWithOtherPlayer(array $putRequest): array
    {
        $response = $this->json('PUT', '/api/game/' . $putRequest['id'], [
            'playerId' => Player::nextPlayerId($putRequest['currentPlayerId']),
            'color' => $putRequest['players'][Player::nextPlayerId($putRequest['currentPlayerId'])]['color'], 
        ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'error' => 'provided player can\'t move right now',
            ]);
        
        return [
            'id' => $putRequest['id'],
            'players' => $putRequest['players'],
            'currentPlayerId' => $putRequest['currentPlayerId'],
        ];
    }

    /**
     * Test putting a new game move with unsupported color.
     *
     * @depends testPutGameMoveWithOtherPlayer
     *
     * @return array
     */
    public function testPutGameMoveWithUnsupportedColor(array $putRequest): array
    {
        $response = $this->json('PUT', '/api/game/' . $putRequest['id'], [
            'playerId' => $putRequest['currentPlayerId'],
            'color' => 'orange', 
        ]);

        $response
            ->assertStatus(400);
        
        return [
            'id' => $putRequest['id'],
            'players' => $putRequest['players'],
            'currentPlayerId' => $putRequest['currentPlayerId'],
        ];
    }
}
