#!/usr/bin/python3
# -*- coding: utf-8 -*-
import unittest
from pyPLCParser import PLCParser, parseInput, evaluateInput, deformatInput

class PLCParserTestCase(unittest.TestCase):

	def runTest(self):

		assert parseInput("") == (True, [])
		assert parseInput("and or ! anything") == (True, [])

		assert parseInput("()") == (True, [[]])

		assert parseInput("( 'and' 'or' 'not' 'xor' '&' '|' '!' '^')") == \
		                  (True, [['and', 'or', 'not', 'xor', '&', '|', '!', '^']])
		assert parseInput('( "and" "or" "not" "xor" "&" "|" "!" "^")') == \
		                  (True, [['and', 'or', 'not', 'xor', '&', '|', '!', '^']])

		assert parseInput("( A B )") == (True, [['A', 'B']])
		assert parseInput("( 'A' 'B' )") == (True, [['A', 'B']])
		assert parseInput("( 'A' and 'B' )") == (True, [['A', 'B']])
		assert parseInput("( 'A' & 'B' )") == (True, [['A', 'B']])

		assert parseInput("( A or B )") == (False, [['A', 'B']])
		assert parseInput("( A | B )") == (False, [['A', 'B']])

		assert parseInput("(( 'A' and 'B' ) OR (A B))") == (False, [[['A', 'B'], ['A', 'B']]])

		assert parseInput("( 'A' 'B' ('A' 'C'))") == (True, [['A', 'B', ['A', 'C']]])
		assert parseInput("( 'A' and 'B' and ('A' or 'C'))") == (True, [['A', 'B', ['A', 'C']]])
		assert parseInput("( 'A' or 'B' or ('A' and 'C'))") == (False, [['A', 'B', ['A', 'C']]])
		assert parseInput("( 'A' or 'B' or ('A' 'C'))") == (False, [['A', 'B', ['A', 'C']]])
		assert parseInput("( 'A' or 'B' ('A' 'C'))") == (False, [['A', 'B', ['A', 'C']]])
		assert parseInput("( 'A' 'B' or ('A' 'C'))") == (False, [['A', 'B', ['A', 'C']]])

		assert parseInput("( 'A\\'')") == (True, [["A'"]])
		assert parseInput('( "A\\"")') == (True, [['A"']])
		assert parseInput("( 'A(B)')") == (True, [['A(B)']])

		assert parseInput('( ( "M" AND ( "(" or "AND" ) ) OR "T" )') == \
		                  (False, [[['M', ['(', 'AND']], 'T']])
		assert parseInput('( ( "M" AND ( "(" OR "AND" ) ) OR \'T\' )') == \
		                  (False, [[['M', ['(', 'AND']], 'T']])

		assert parseInput('( 1 2 )') == (True, [['1', '2']])
		assert parseInput('( 1.0 2.0 )') == (True, [['1.0', '2.0']])
		assert parseInput('( 1,0 2,0 )') == (True, [['1,0', '2,0']])
		assert parseInput('( 12*12 2.0-1.0 )') == (True, [['12*12', '2.0-1.0']])

		assert parseInput("(!a b)") == (True, [[-1, 'a', 'b']])
		assert parseInput("(!(a b))") == (True, [[-1, ['a', 'b']]])
		assert parseInput("(!(a | b))") == (True, [[-1, ['a', 'b']]])
		assert parseInput("((!(a b)))") == (True, [[[-1, ['a', 'b']]]])
		assert parseInput("!(a | b)") == (False, [-1, ['a', 'b']])
		assert parseInput("!!(a | b)") == (False, [-1, -1, ['a', 'b']])

		assert parseInput("^(a b)") == (True, [-2, ['a', 'b']])
		assert parseInput("(^(a b))") == (True, [[-2, ['a', 'b']]])
		assert parseInput("((^(a b)))") == (True, [[[-2, ['a', 'b']]]])
		assert parseInput("((xor(a b)))") == (True, [[[-2, ['a', 'b']]]])

		assert parseInput("^!(a b)") == (True, [-2, -1, ['a', 'b']])
		assert parseInput('( ! ( ! a ! b ) )') == (True, [[-1, [-1, 'a', -1, 'b']]])
		assert parseInput('( not ( not a not b ) )') == (True, [[-1, [-1, 'a', -1, 'b']]])

		c = PLCParser(parentheses=['[', ']'], wrappers=['´'])

		assert c.parse("(´A´)") == (True, [])
		assert c.parse("['A']") == (True, [["'A'"]])
		assert c.parse("[´A´]") == (True, [['A']])

if __name__ == '__main__':
    unittest.main()
