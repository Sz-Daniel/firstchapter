<?php
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testThatTwoStringsAreTheSame()
    {
        $str1 = 'grey';
        $str2 = 'gry';
        
        $this->assertTrue($str1 == $str2);
    }
    public function testProductFunction()
    {
       require 'example-functions.php';

       $product = product(10,2);
       
       $this->assertEquals(20,$product);
       $this->assertNotEquals(10,$product);

    }

}?>
