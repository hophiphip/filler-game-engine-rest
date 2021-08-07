<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Colors;

class ColorsTest extends TestCase
{
    public function testCompareColors()
    {
        $this->assertTrue(Colors::compareColors('#FFFFFF', '#ffffff'));
        $this->assertTrue(Colors::compareColors('#FF0000', 'red'));
        $this->assertTrue(Colors::compareColors('#00FF00', 'green'));
        $this->assertTrue(Colors::compareColors('#0000FF', 'blue'));
        $this->assertTrue(Colors::compareColors('#ffff00', 'yellow'));
        $this->assertTrue(Colors::compareColors('#ff00ff', 'magenta'));
        $this->assertTrue(Colors::compareColors('#00ffff', 'cyan'));
        $this->assertTrue(Colors::compareColors('#ffffff', 'white'));
        $this->assertTrue(Colors::compareColors('white', 'white'));
        $this->assertTrue(!Colors::compareColors('white', '#000000'));
    }
}
