<?php

use \elonmedia\plcparser\php\PLCParser;

class PLCParserTest extends PHPUnit_Framework_TestCase
{
	protected void setUp() {
		
	}
	protected void tearDown() {
		
	}

	public function testPLCParserDefault() {
		$c = new PLCParser();

		$this->assertEquals(
			$c->parse("(A & B)"), 
			array(TRUE, array(array('A', 'B')))
		);

		$this->assertEquals(
			$c->parse("(A | B)"), 
			array(FALSE, array(array('A', 'B')))
		);
	}

	public function testPLCParserCustom() {
		$c = new PLCParser(array('{' '}'), array('´'));

		$this->assertEquals(
			$this->plcparser->parse("{A & B}"), 
			array(TRUE, array(array('A', 'B')))
		);

		$this->assertEquals(
			$this->plcparser->parse("(´A´ | ´B´)"), 
			array(FALSE, array(array('A', 'B')))
		);
	}

	public function testPLCParserNestedInput() {
		$c = new PLCParser();

		$this->assertEquals(
			$c->parse("(A & B & (C | D))"), 
			array(TRUE, array(array('A', 'B', array('C', 'D'))))
		);

		$this->assertEquals(
			$c->parse("(A | B | (C & D))"), 
			array(FALSE, array(array('A', 'B', array('C', 'D'))))
		);
	}

	public function testPLCParserWordOperators() {
		$c = new PLCParser();

		$this->assertEquals(
			$c->parse("(A and B and (C or D))"), 
			array(TRUE, array(array('A', 'B', array('C', 'D'))))
		);

		$this->assertEquals(
			$c->parse("(A or B or (C and D))"), 
			array(FALSE, array(array('A', 'B', array('C', 'D'))))
		);
	}

	public function testPLCParserNoOperators() {
		$c = new PLCParser();

		$this->assertEquals(
			$c->parse("(A B (C D))"), 
			array(TRUE, array(array('A', 'B', array('C', 'D'))))
		);

		$this->assertEquals(
			$c->parse("(A or B (C D))"), 
			array(FALSE, array(array('A', 'B', array('C', 'D'))))
		);
	}

	public function testPLCParserNotOperators() {
		$c = new PLCParser();

		$this->assertEquals(
			$c->parse("(A B !(C !D))"), 
			array(TRUE, array(array('A', 'B', -1, array('C', -1, 'D'))))
		);

		$this->assertEquals(
			$c->parse("!(A or B (C D))"), 
			array(FALSE, array(array(-1, array('A', 'B', array('C', 'D'))))
		);
	}

	public function testPLCParserXorOperators() {
		$c = new PLCParser();

		$this->assertEquals(
			$c->parse("(A B ^(C D))"), 
			array(TRUE, array(array('A', 'B', -2, array('C', 'D'))))
		);

		$this->assertEquals(
			$c->parse("^(A or B (C D))"), 
			array(FALSE, array(array(-2, array('A', 'B', array('C', 'D'))))
		);
	}

	public function testPLCParserDeformat() {
		# list
		# short
		# firstonly
		# latex
	}

	public function testPLCParserEvaluate() {
		# input = output
		# with table
	}

}
