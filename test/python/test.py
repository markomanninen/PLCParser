#!/usr/bin/python3
# -*- coding: utf-8 -*-
import unittest
from pyPLCParser import PLCParser, parseInput, evaluateInput, deformatInput

class PLCParserTestCase(unittest.TestCase):

	def runTest(self):

		self.assertEqual(parseInput(""), (True, []), 'empty input')
		self.assertEqual(parseInput("and or ! anything"), (True, []), 'without any parentheses')

		self.assertEqual(parseInput("()"), (True, [[]]), 'empty parentheses')

		self.assertEqual(parseInput("( 'and' 'or' 'not' 'xor' 'xand' '&' '|' '!' '^' '+' '⊕' '∨' '⊖' '∧' '¬')"), \
		                  (True, [['and', 'or', 'not', 'xor', 'xand', '&', '|', '!', '^', '+', '⊕', '∨', '⊖', '∧', '¬']]), 'single quoted reserved words, chars and math symbols')
		self.assertEqual(parseInput('( "and" "or" "not" "xor" "xand" "&" "|" "!" "^" "+" "⊕" "∨" "⊖" "∧" "¬")'), \
		                  (True, [['and', 'or', 'not', 'xor', 'xand', '&', '|', '!', '^', '+', '⊕', '∨', '⊖', '∧', '¬']]), 'double quoted reserved words, chars and math symbols')

		self.assertEqual(parseInput("( A B )"), (True, [['A', 'B']]), 'plain A B')
		self.assertEqual(parseInput("( 'A' 'B' )"), (True, [['A', 'B']]), 'quoted A B')
		#self.assertEqual(parseInput("( 'A' and 'B' )"), (True, [['A', 'B']]), 'quoted A B with and keyword')

		self.assertEqual(parseInput("( 'A' & 'B' )"), (True, [['A', 'B']]), 'quoted A B with & char')
		#self.assertEqual(parseInput("( 'A' ∧ 'B' )"), (True, [['A', 'B']]), 'quoted A B with ∧ math symbol')

		self.assertEqual(parseInput("( A or B )"), (False, [['A', 'B']]), '')
		self.assertEqual(parseInput("( A | B )"), (False, [['A', 'B']]), '')

		self.assertEqual(parseInput("(( 'A' and 'B' ) OR (A B))"), (False, [[['A', 'B'], ['A', 'B']]]), '')

		self.assertEqual(parseInput("( 'A' 'B' ('A' 'C'))"), (True, [['A', 'B', ['A', 'C']]]), '')
		self.assertEqual(parseInput("( 'A' and 'B' and ('A' or 'C'))"), (True, [['A', 'B', ['A', 'C']]]), '')
		self.assertEqual(parseInput("( 'A' or 'B' or ('A' and 'C'))"), (False, [['A', 'B', ['A', 'C']]]), '')
		self.assertEqual(parseInput("( 'A' or 'B' or ('A' 'C'))"), (False, [['A', 'B', ['A', 'C']]]), '')
		self.assertEqual(parseInput("( 'A' or 'B' ('A' 'C'))"), (False, [['A', 'B', ['A', 'C']]]), '')
		self.assertEqual(parseInput("( 'A' 'B' or ('A' 'C'))"), (False, [['A', 'B', ['A', 'C']]]), '')

		self.assertEqual(parseInput("( 'A\\'')"), (True, [["A'"]]), '')
		self.assertEqual(parseInput('( "A\\"")'), (True, [['A"']]), '')
		self.assertEqual(parseInput("( 'A(B)')"), (True, [['A(B)']]), '')

		self.assertEqual(parseInput('( ( "M" AND ( "(" or "AND" ) ) OR "T" )'), \
		                  (False, [[['M', ['(', 'AND']], 'T']]), '')
		self.assertEqual(parseInput('( ( "M" AND ( "(" OR "AND" ) ) OR \'T\' )'), \
		                  (False, [[['M', ['(', 'AND']], 'T']]), '')

		self.assertEqual(parseInput('( 1 2 )'), (True, [['1', '2']]), '')
		self.assertEqual(parseInput('( 1.0 2.0 )'), (True, [['1.0', '2.0']]), '')
		self.assertEqual(parseInput('( 1,0 2,0 )'), (True, [['1,0', '2,0']]), '')
		self.assertEqual(parseInput('( 12*12 2.0-1.0 )'), (True, [['12*12', '2.0-1.0']]), '')

		self.assertEqual(parseInput("(!a b)"), (True, [[-1, 'a', 'b']]), '')
		self.assertEqual(parseInput("(!(a b))"), (True, [[-1, ['a', 'b']]]), '')
		self.assertEqual(parseInput("(!(a | b))"), (True, [[-1, ['a', 'b']]]), '')
		self.assertEqual(parseInput("((!(a b)))"), (True, [[[-1, ['a', 'b']]]]), '')
		self.assertEqual(parseInput("!(a | b)"), (False, [-1, ['a', 'b']]), '')
		self.assertEqual(parseInput("!!(a | b)"), (False, [-1, -1, ['a', 'b']]), '')

		self.assertEqual(parseInput("^(a b)"), (True, [-2, ['a', 'b']]), '')
		self.assertEqual(parseInput("(^(a b))"), (True, [[-2, ['a', 'b']]]), '')
		self.assertEqual(parseInput("((^(a b)))"), (True, [[[-2, ['a', 'b']]]]), '')
		self.assertEqual(parseInput("((xor(a b)))"), (True, [[[-2, ['a', 'b']]]]), '')

		self.assertEqual(parseInput("^!(a b)"), (True, [-2, -1, ['a', 'b']]), '')
		self.assertEqual(parseInput('( ! ( ! a ! b ) )'), (True, [[-1, [-1, 'a', -1, 'b']]]), '')
		self.assertEqual(parseInput('( not ( not a not b ) )'), (True, [[-1, [-1, 'a', -1, 'b']]]), '')

		c = PLCParser(parentheses=['[', ']'], wrappers=['´'])

		self.assertEqual(c.parse("(´A´)"), (True, []), 'wrong parentheses')
		self.assertEqual(c.parse("['A']"), (True, [["'A'"]]), 'wrong wrappers')
		self.assertEqual(c.parse("[´A´]"), (True, [['A']]), 'right parentheses and wrappers')

if __name__ == '__main__':
    unittest.main()
