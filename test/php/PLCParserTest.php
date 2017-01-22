<?php

use \elonmedia\plcparser\php\PLCParser;

class PLCParserTest extends PHPUnit_Framework_TestCase
{
  public function testPLCParser()
  {
    $c = new PLCParser;

    $this->assertEquals(
    	$c->parse("(A & B)"), 
    	array(True, array('A', 'B'))
    );
  }
}
