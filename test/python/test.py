#!/usr/bin/python3
# -*- coding: utf-8 -*-
import sys
import unittest
from pyPLCParser import PLCParser, parseInput, evaluateInput, deformatInput, validateInput

class PLCParserTestCase(unittest.TestCase):

	def runTest(self):

		# PARSE
		self.assertEqual(parseInput(""), (True, []), 'empty input')
		self.assertEqual(parseInput("and or ! anything"), (True, []), 'without any parentheses')

		self.assertEqual(parseInput("()"), (True, [[]]), 'empty parentheses')

		self.assertEqual(parseInput("( 'and' 'or' 'not' 'xor' 'xand' '&' '|' '!' '^' '+' '⊕' '∨' '⊖' '∧' '¬')"), \
		                  (True, [['and', 'or', 'not', 'xor', 'xand', '&', '|', '!', '^', '+', '⊕', '∨', '⊖', '∧', '¬']]), 'single quoted reserved words, chars and math symbols')
		self.assertEqual(parseInput('( "and" "or" "not" "xor" "xand" "&" "|" "!" "^" "+" "⊕" "∨" "⊖" "∧" "¬")'), \
		                  (True, [['and', 'or', 'not', 'xor', 'xand', '&', '|', '!', '^', '+', '⊕', '∨', '⊖', '∧', '¬']]), 'double quoted reserved words, chars and math symbols')

		self.assertEqual(parseInput("( A B )"), (True, [['A', 'B']]), 'plain A B')
		self.assertEqual(parseInput("( 'A' 'B' )"), (True, [['A', 'B']]), 'quoted A B')
		self.assertEqual(parseInput("( 'A' and 'B' )"), (True, [['A', 'B']]), 'quoted A B with and keyword')
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

		if sys.version_info < (3,0):
			c = PLCParser(parentheses=['[', ']'], wrappers=[unicode('´', 'utf-8')])
			self.assertEqual(c.parse(unicode('(´A´)', 'utf-8')), (True, []), 'wrong parentheses')
			self.assertEqual(c.parse(unicode('[´A´]', 'utf-8')), (True, [['A']]), 'right parentheses and wrappers')
		else:
			c = PLCParser(parentheses=['[', ']'], wrappers=['´'])
			self.assertEqual(c.parse('(´A´)'), (True, []), 'wrong parentheses')
			self.assertEqual(c.parse('[´A´]'), (True, [['A']]), 'right parentheses and wrappers')
		
		self.assertEqual(c.parse("['A']"), (True, [["'A'"]]), 'wrong wrappers')
		
		# EVALUATE
		# basic
		assert evaluateInput('(1 1)') == True
		assert evaluateInput('(1 0)') == False
		assert evaluateInput('(1 or 1)') == True
		assert evaluateInput('(1 or 0)') == True
		# true false literals
		assert evaluateInput('(true true)') == True
		assert evaluateInput('(true false)') == False
		assert evaluateInput('(true or true)') == True
		assert evaluateInput('(true or false)') == True
		# group
		assert evaluateInput('(1 (0 0))') == False
		assert evaluateInput('(1 (1 0))') == True
		assert evaluateInput('(1 (1 1))') == True
		assert evaluateInput('(0 (1 1))') == False
		# multiple groups
		assert evaluateInput('(1 (1 0) (1 0))') == True
		assert evaluateInput('(1 and (1 or 0) and (1 or 0))') == True
		assert evaluateInput('(1 or (1 and 0) or (1 and 0))') == True
		assert evaluateInput('(0 or (1 and 0) or (1 and 0))') == False
		# NOT
		assert evaluateInput('!(1 (0 0))') == True
		assert evaluateInput('(!0 1)') == True
		assert evaluateInput('(1 !(0 0))') == True
		# and or chars
		assert evaluateInput('(0 | 1)') == True
		assert evaluateInput('(0 & 1)') == False
		# and or math symbols
		assert evaluateInput('(0 ∨ 1)') == True
		assert evaluateInput('(0 ∧ 1)') == False
		# XOR single
		assert evaluateInput('(^(1))') == False
		assert evaluateInput('(^(0))') == False
		# NOT XOR
		assert evaluateInput('(!^(1))') == True
		assert evaluateInput('(!^(0))') == True
		# XOR double
		assert evaluateInput('(^(1 0))') == True
		assert evaluateInput('(^(0 0))') == False
		assert evaluateInput('(^(1 1))') == False
		assert evaluateInput('(^(0 1))') == True
		# XOR multiple
		assert evaluateInput('(^(1 0 0))') == True
		assert evaluateInput('(^(0 0 0))') == False
		assert evaluateInput('(^(1 1 0))') == False
		assert evaluateInput('(^(0 1 0))') == True
		assert evaluateInput('(^(1 1 1))') == False
		# XAND single
		assert evaluateInput('(+(1))') == False
		assert evaluateInput('(+(0))') == False
		# NOT XAND
		assert evaluateInput('(!+(1))') == True
		assert evaluateInput('(!+(0))') == True
		# XAND double
		assert evaluateInput('(+(1 0))') == True
		assert evaluateInput('(+(0 0))') == False
		assert evaluateInput('(+(1 1))') == False
		assert evaluateInput('(+(0 1))') == True
		# XAND multiple
		assert evaluateInput('(+(1 0 0))') == True
		assert evaluateInput('(+(0 0 0))') == False
		assert evaluateInput('(+(1 1 0))') == True
		assert evaluateInput('(+(0 1 0))') == True
		assert evaluateInput('(+(1 1 1))') == False
		# deeply nested
		assert evaluateInput('(1(1(1(1(1)))))') == True
		# truth table
		table = {'A': 1, 'B': 0}
		assert evaluateInput('(A or B)', table) == True
		assert evaluateInput('(1 ("A" "B"))', table) == True
		
		# DEFORMAT
		assert deformatInput([True, [[1, 0]]]) == '( ( 1 and 0 ) )'
		assert deformatInput([True, [-1, [1, 0]]]) == '( not ( 1 and 0 ) )'
		assert deformatInput([False, [-1, [1, 0]]]) == '( not ( 1 or 0 ) )'
		assert deformatInput([False, [-2, [1, 0]]]) == '( xor ( 1 or 0 ) )'
		assert deformatInput([False, [-3, [1, 0]]]) == '( xand ( 1 or 0 ) )'

		assert deformatInput([True, [[1, [0, 1]]]], first=True) == '( ( 1 and ( 0 1 ) ) )'
		assert deformatInput([True, [[1, [0, 1]]]], latex=True) == '( ( 1 ∧ ( 0 ∨ 1 ) ) )'
		assert deformatInput([True, [[1, [0, 1]]]], short=True) == '( ( 1 & ( 0 | 1 ) ) )'
		assert deformatInput([True, [[1, [0, 1]]]], first=True, latex=True) == '( ( 1 ∧ ( 0 1 ) ) )'
		assert deformatInput([True, [[1, [0, 1]]]], first=True, short=True) == '( ( 1 & ( 0 1 ) ) )'
		
		# VALIDATE
		assert validateInput('') == True
		assert validateInput('(') == False
		assert validateInput(')') == False
		assert validateInput('()') == True
		assert validateInput('(")') == False
		assert validateInput('("")') == True
		assert validateInput('("\\"")') == True
		assert validateInput('("\'")') == True
		assert validateInput('(")" "(")') == True
		assert validateInput('())') == False
		assert validateInput('(()') == False
		assert validateInput("('A')") == True
		assert validateInput("(A and B)") == True
		assert validateInput("(A and (B or C))") == True
		assert validateInput("('A and (B or C))") == False
		assert validateInput("('A' and (´B or C))") == True
		assert validateInput("(\\()") == True

if __name__ == '__main__':
    unittest.main()
