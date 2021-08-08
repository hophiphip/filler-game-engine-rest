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

    public function testColorsRegex() {
        foreach (Colors::$colorsTable as $color => $_) {
            $this->assertTrue(preg_match(Colors::$colorsRegex, $color) == 1);
        }

       $this->assertTrue(1 != preg_match(Colors::$colorsRegex, '#fff'));
       $this->assertTrue(1 != preg_match(Colors::$colorsRegex, '#000000000'));
       $this->assertTrue(1 != preg_match(Colors::$colorsRegex, '#red'));
       $this->assertTrue(1 != preg_match(Colors::$colorsRegex, '#000000red'));
       $this->assertTrue(1 != preg_match(Colors::$colorsRegex, 'white#000000'));
       
       $this->assertTrue(preg_match(Colors::$colorsRegex, 'RED') == 1);
       $this->assertTrue(preg_match(Colors::$colorsRegex, 'BLUE') == 1);
    }
}
