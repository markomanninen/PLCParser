#!/usr/bin/python3
# -*- coding: utf-8 -*-
import re
from . JsonSchema import JsonPropositionalLogicSchema

"""

Propositional Logic Clause Parser

Author: Marko Manninen <elonmedia@gmail.com>
Date: 11.2.2017

"""

NOT_OPERATOR = -1
AND_OPERATOR = -2
XOR_OPERATOR = -3
OR_OPERATOR = -4
# TODO: operator precedence?
NAND_OPERATOR = -5
XNOR_OPERATOR = -6
NOR_OPERATOR = -7

def xor(a):
    # parity check
    return len(list(filter(lambda x: x, a))) % 2 != 0

class ParseException(Exception):
    pass

class PLCParser():

    def __init__(self, parentheses = ['(', ')'], wrappers = ["'", '"']):
        """ PLCParser class constructor """
        self.OPEN_PARENTHESES, self.CLOSE_PARENTHESES = parentheses
        # http://stackoverflow.com/questions/430759/regex-for-managing-escaped-characters-for-items-like-string-literals
        self.wrappers = wrappers
        self.STRING_LITERALS = re.compile('|'.join([r"%s[^%s\\]*(?:\\.[^%s\\]*)*%s" % 
                                          (w,w,w,w) for w in self.wrappers]))
        self.operators = {
            NOT_OPERATOR: {'word': 'not', 'char': '!', 'math': '¬', 'func': lambda x: not x},
            AND_OPERATOR: {'word': 'and', 'char': '&', 'math': '∧', 'func': all},
            XOR_OPERATOR: {'word': 'xor', 'char': '^', 'math': '⊕', 'func': xor},
            OR_OPERATOR:  {'word': 'or', 'char': '|', 'math': '∨', 'func': any},
            NAND_OPERATOR: {'word': 'nand', 'char': '↑', 'math': '↑', 'func': lambda a: not all(a)},
            XNOR_OPERATOR: {'word': 'xnor', 'char': '⊖', 'math': '⊖', 'func': lambda a: not xor(a)},
            NOR_OPERATOR:  {'word': 'nor', 'char': '↓', 'math': '↓', 'func': lambda a: not any(a)}
        }
        self.operator_schemas = {}
        self.negate_unary_operator = False
    
    def setLiterals(self, input_string):
        self.literals = {}
        # find literals
        lit = self.STRING_LITERALS.search(input_string)
        n = 1
        while lit:
            g = lit.group()
            key = "L%s" % n
            # set literal by key and value by removing 
            # for example " and ' wrapper chars
            self.literals[key] = g[1:-1]
            # remove literal from original input and replace with key
            input_string = input_string.replace(g, " %s " % key)
            # make a new search and loop until all is found
            lit = self.STRING_LITERALS.search(input_string)
            # next literal number
            n += 1
        # wrap parenthesis and operator symbols with space for later usage
        input_string = input_string.replace(self.OPEN_PARENTHESES, ' %s ' % self.OPEN_PARENTHESES)
        input_string = input_string.replace(self.CLOSE_PARENTHESES, ' %s ' % self.CLOSE_PARENTHESES)
        for operator, options in self.operators.items():
            input_string = input_string.replace(options['math'], ' %s ' % options['math'])
            input_string = input_string.replace(options['char'], ' %s ' % options['char'])
        # set literal string and its length for recursive parser
        self.literal_string = input_string
      
    def _parse(self, l, operators=(-7, -6, -5, -4, -3, -2)):
        # http://stackoverflow.com/questions/42032418/group-operands-by-logical-connective-precedence-from-python-list
        # if nothing on list, raise error
        if len(l) < 1:
            raise ParseException("Malformed input. Length 0 or multiple operators.")
        # one item on list
        if len(l) == 1:
            # if not negation or other operators
            if l[0] != NOT_OPERATOR and not l[0] in operators:
                # substitute literals back to original content if available
                if not isinstance(l[0], list) and l[0] in self.literals:
                    l[0] = self.literals[l[0]]
                    # finally replace escaped content with content
                    for w in self.wrappers:
                        l[0] = l[0].replace("\\%s" % w, w)
                # return data
                return l[0]
            # raise error because operator should not exist at this point
            else:
                raise ParseException("Malformed input. Operator not found: %s." % l[0])
        # we are looping over all binary operators in order: 
        # -4 = or, -3 = xor, -2 = and
        if len(operators) > 0:
            operator = operators[0]
            try:
                # for left-associativity of binary operators
                position = len(l) - 1 - l[::-1].index(operator)
                return [self._parse(l[:position], operators), operator, self._parse(l[position+1:], operators[1:])]
            except ValueError:
                return self._parse(l, operators[1:])
        # expecting only not operator at this point
        if l[0] != NOT_OPERATOR:
            raise ParseException("Malformed input. Operator expected, %s found." % l[0])
        # return data with not operator
        return [NOT_OPERATOR, self._parse(l[1:], operators)]

    def substitute(self, x):
        y = x.strip().lower()
        # not operator becomes -1
        if y == "!" or y == "not" or y == "¬":
            return NOT_OPERATOR
        # and operator becomes -2
        elif y == "&" or y == "and" or y == "∧":
            return AND_OPERATOR
        # xor operator becomes -3
        elif y == "^" or y == "xor" or y == "⊕":
            return XOR_OPERATOR
        # or operator becomes -4
        elif y == "|" or y == "or" or y == "∨":
            return OR_OPERATOR
        # or operator becomes -5
        elif y == "↑" or y == "nand" or y == "↑":
            return NAND_OPERATOR
        # or operator becomes -6
        elif y == "⊖" or y == "xnor" or y == "⊖":
            return XNOR_OPERATOR
        # or operator becomes -7
        elif y == "↓" or y == "nor" or y == "↓":
            return NOR_OPERATOR
        elif y == "1" or y == "0":
            return int(y)
        return x

    def recursiveParenthesesGroups(self):
        
        def rec(a):
            # http://stackoverflow.com/questions/17140850/how-to-parse-a-string-and-return-a-nested-array/17141899#17141899
            stack = [[]]
            for x in a:
                if x == self.OPEN_PARENTHESES:
                    stack[-1].append([])
                    stack.append(stack[-1][-1])
                elif x == self.CLOSE_PARENTHESES:
                    stack.pop()
                    # special treatment for prefix notation
                    # (^ a b c) => (a ^ b ^ c) => (a ^ (b ^ c))
                    # (& a b c) => (a & b & c) => (a & (b & c))
                    # (| a b c) => (a | b | c) => (a | (b | c))
                    # also (& a b (| c d)) => (a & b) & (c | d) is possible!
                    if len(stack[-1][-1]) > 1 and \
                       isinstance(stack[-1][-1][0], int) and \
                       stack[-1][-1][0] in self.operators and \
                       stack[-1][-1][0] != NOT_OPERATOR:
                        op = stack[-1][-1][0]
                        b = []
                        # append operator between all operands
                        for a in stack[-1][-1][1:]:
                            b.extend([a, op])
                        # in special case where only one operand and one operator is found
                        # we should handle the case differently on evaluation process
                        # TODO: it is not possible to deformat this kind of structure
                        if op < -4 and len(b) == 2:
                            self.negate_unary_operator = True
                        stack[-1][-1] = b[:-1]
                    # see if remaining content has more than one
                    # operator and make them nested set in that case
                    stack[-1][-1] = self._parse(stack[-1][-1])
                    if not stack:
                        raise ParseException('Malformed input. Parenthesis mismatch. Opening parentheses missing.')
                else:
                    stack[-1].append(x)
            if len(stack) > 1:
                raise ParseException('Malformed input. Parenthesis mismatch. Closing parentheses missing.')
            return stack.pop()

        # remove whitespace from literal string (!=input string at this point already)
        a = ' '.join(self.literal_string.split()).split(' ')
        # artificially add parentheses if not provided
        a = [self.OPEN_PARENTHESES] + a + [self.CLOSE_PARENTHESES]
        # substitute different operators by numeral representatives
        a = map(self.substitute, a)
        # loop over the list of literals placeholders and operators and parentheses
        return rec(list(a))

    def parse(self, input_string):
        """ main method """
        # first set literals
        self.setLiterals(input_string.strip())
        # then recursively parse clause
        return self.recursiveParenthesesGroups()[0]

    @staticmethod
    def parseInput(input_string):
        """ bypass object construct """
        c = PLCParser()
        try:
            return c.parse(input_string)
        except ParseException as pe:
            return None

    @staticmethod
    def validateInput(input_string):
        """ bypass object construct """
        c = PLCParser()
        try:
            return c.validate(input_string)
        except:
            return None
    
    def validate(self, input_string, open_parenthesis=None, close_parenthesis=None, wrappers=[], escape_char=None):
        # check parentheses and wrappers characters that they match
        # for example (, [, {
        open_parenthesis = open_parenthesis if open_parenthesis else self.OPEN_PARENTHESES
        # for example: }, ], )
        close_parenthesis = close_parenthesis if close_parenthesis else self.CLOSE_PARENTHESES
        # multiple wrapper chars accepted, for example ['"', "'", "´"]
        wrappers = wrappers if wrappers else self.wrappers
        # is is possible to pass a different escape char, but it is probably
        # not a good idea because many of the string processors use the same
        escape_char = escape_char if escape_char else '\\'
        # init vars
        stack, current, last, previous = ([], None, None, None)
        # loop over all characters in a string
        for current in input_string:
            #if previous character was escape character, then 
            # swap it with the current one and continue to the next char
            if previous == escape_char:
                # see if current character is escape char, then there are
                # two of them in row and we should reset previous marker
                if current == escape_char:
                    previous = None
                else:
                    previous = current
                continue
            # last stacked char. not that this differs from the previous value which
            # is the previous char from string. last is the last char from stack
            last = stack[-1] if stack else None
            # if we are inside a wrapper accept ANY character 
            # until the next unescaped wrapper char occurs
            if last in wrappers and current != last:
                # swap the current so that we can escape wrapper inside wrappers: "\""
                previous = current
                continue
            # push open parenthesis or wrapper to the stack
            if current == open_parenthesis or (current in wrappers and current != last):
                stack.append(current)
            # prepare to pop last parenthesis or wrapper
            elif current == close_parenthesis or current in wrappers:
                # if there is nothing on stack, should already return false
                if len(stack) == 0:
                    return False
                else:
                    # if we encounter wrapper char take the last wrapper char out from stack
                    # or if the last char was open and current close parenthsis
                    if last in wrappers or (last == open_parenthesis and current == close_parenthesis):
                        stack.pop()
                    else:
                        return False
            # update previous char
            previous = current
        # if there is something on stack then no closing char was found
        return len(stack) == 0

    @staticmethod
    def deformatInput(lst, operator_type="word"):
        """ bypass object construct. Types are: word, char, math"""
        c = PLCParser()
        try:
            return c.deformat(lst, operator_type=operator_type)
        except:
            return None
    
    def deformat(self, lst, operator_type = "word"):
        def _(current_item, operator_type, first=True):
            # if item is not a list, return value
            if not isinstance(current_item, list):
                # boolean values
                if current_item == 1 or str(current_item).lower() == 'true' or \
                   current_item == 0 or str(current_item).lower() == 'false':
                    return current_item
                # normal items wrapped by the first configured wrapper char
                # escaping wrapper char inside the string
                current_item = current_item.replace(self.wrappers[0], '\\'+self.wrappers[0])
                return self.wrappers[0]+current_item+self.wrappers[0]
            # item is a list, open clause
            a = []
            if not first:
                a.append(self.OPEN_PARENTHESES)
            # loop all items
            for item in current_item:
                # operators
                if not isinstance(item, list) and item in self.operators:
                    a.append(self.operators[item][operator_type])
                # item or list
                else:
                    # recursively add next items
                    a.append(_(item, operator_type, False))
            # close clause
            if not first:
                a.append(self.CLOSE_PARENTHESES)
            return ' '.join(map(str, a))
        # call sub routine to deformat structure
        return _(lst, operator_type)

    @staticmethod
    def evaluateInput(i, table={}):
        """ bypass object construct """
        c = PLCParser()
        try:
            return c.evaluate(i, table)
        except:
            return None

    def evaluate(self, i, table={}):
        # parse input if string, otherwise expecting correctly structured list
        return self.truth_value(self.parse(i) if type(i) == str else i, table)

    def truth_value(self, current_item, table, negate=True):
        # if item is not a list, check the truth value
        if not isinstance(current_item, list):
            # truth table is possibly given
            if table and current_item in table:
                current_item = table[current_item]
            # force item to string and lowercase for simpler comparison
            # if only single operator is given on input, then self.negate_unary_operator
            # is set to true, thus comparison here is done
            return (str(current_item).lower() in ['true', '1']) != self.negate_unary_operator
        # item is a list
        a = []
        # default operator
        operator = AND_OPERATOR
        for item in current_item:
            # operators
            if not isinstance(item, list) and item in self.operators:
                if item == NOT_OPERATOR:
                    negate = False
                else:
                    operator = item
            else:
                a.append(self.truth_value(item, table))
        # all operators have a function to check the truth value
        # we must compare returned boolean against negate parameter
        return self.operators[operator]['func'](a) == negate

    @staticmethod
    def jsonSchema(i, table={}):
        """ bypass object construct """
        c = PLCParser()
        try:
            return c.buildJsonSchema(i, table)
        except:
            return None

    def buildJsonSchema(self, i, table={}):
        schema = self.schema(self.parse(i) if type(i) == str else i, table)
        # after running schema method all required schema components are collected
        ## and returned as a json string
        jpls = JsonPropositionalLogicSchema()
        return jpls.get(schema, **self.operator_schemas)

    def schema(self, current_item, table, negate=True):
        # item is a list
        a = []
        # should we negate next item, was it a list or values
        operator = -2
        for item in current_item:
            # operators
            if not isinstance(item, list) and item in self.operators:
                if item == NOT_OPERATOR:
                    negate = False
                else:
                    operator = item
            else:
                if isinstance(item, list):
                    a.append(self.schema(item, table))
        # is group AND / OR / XOR
        # take care of negation for the list result too
        if operator == XOR_OPERATOR or operator == XNOR_OPERATOR:
            # if any of the values are true, but not all
            if operator == XNOR_OPERATOR:
                op = '_xnor' if negate else '_xor'
            op = '_xor' if negate else '_xnor'
        elif operator == OR_OPERATOR or operator == NOR_OPERATOR:
            # if some of the values is true
            if operator == NOR_OPERATOR:
                op = '_nor' if negate else '_or'
            op = '_or' if negate else '_nor'
        # operator == AND_OPERATOR
        else:
            # if all values are true
            if operator == NAND_OPERATOR:
                op = '_nand' if negate else '_and'
            op = '_and' if negate else '_nand'
        # add used operator to the schema
        self.operator_schemas[op] = True
        # generate schema
        if len(a) > 0:
            items = '{"items": {%s}}' % ',\r\n\t'.join(a)
            return '{"allOf": [\r\n\t{"$ref": "#/definitions/%s"},\r\n\t%s]}' % (op, items)
        # the deep most nested level has no appended items on a
        else:
            return '"$ref": "#/definitions/%s"' % op

parseInput = PLCParser.parseInput
evaluateInput = PLCParser.evaluateInput
deformatInput = PLCParser.deformatInput
validateInput = PLCParser.validateInput
jsonSchema = PLCParser.jsonSchema
