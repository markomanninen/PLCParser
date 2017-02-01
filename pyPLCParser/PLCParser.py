#!/usr/bin/python3
# -*- coding: utf-8 -*-
import re

"""

Propositional Logic Clause Parser

Author: Marko Manninen <elonmedia@gmail.com>
Date: 22.1.2017

"""

class PLCParser():
    
    # normalize string to standard format i.e.
    # separate operators from other strings and add spaces
    # for keywords: xor or xand and not
    PREPROCESS_OPERATORS1 = re.compile(
            r'([\)])[\s]*(xor|or|xand|and|not)[\s]+|'+\
            r'[\s]+(xor|or|xand|and|not)[\s]*([\(])|'+\
            r'[\s]+(xor|or|xand|and|not)[\s]+'
            , re.IGNORECASE)
    # for special chars: ^ | + & !
    PREPROCESS_OPERATORS2 = re.compile(
            r'([\)])[\s]*(\^|\||\+|\&|\!)[\s]+|'+\
            r'[\s]+(\^|\||\+|\&|\!)[\s]*([\(])|'+\
            r'[\s]+(\^|\||\+|\&|\!)[\s]+')
    # for math chars: ⊖ ⊕ ∨ ∧ ¬
    PREPROCESS_OPERATORS3 = re.compile(
            r'([\)])[\s]*(⊕|∨|⊖|∧|¬)[\s]+|'+\
            r'[\s]+(⊕|∨|⊖|∧|¬)[\s]*([\(])|'+\
            r'[\s]+(⊕|∨|⊖|∧|¬)[\s]+')
    # get operators from start, middle and end of the string
    OPERATORS = re.compile(r'(^|\s+)(or|and|\||\&|∨|∧)(\s+|$)', re.IGNORECASE)
    # find xor operator
    XOR = re.compile(r'(\^|xor|⊕)', re.IGNORECASE)
    # find xand operator, meaningful in groups only
    XAND = re.compile(r'(\+|xand|⊖)', re.IGNORECASE)
    # find not operator
    NOT = re.compile(r'(\!|not|¬)', re.IGNORECASE)

    def __init__(self, parentheses = ['(', ')'], wrappers = ["'", '"']):
        """ constructor """
        self.OPEN_PARENTHESES, self.CLOSE_PARENTHESES = parentheses
        # http://stackoverflow.com/questions/430759/regex-for-managing-escaped-characters-for-items-like-string-literals
        self.wrappers = wrappers
        self.STRING_LITERALS = re.compile('|'.join([r"%s[^%s\\]*(?:\\.[^%s\\]*)*%s" % 
                                          (w,w,w,w) for w in self.wrappers]))
        self.mutual = None
    
    def setLiterals(self, input_string):
        n=1
        self.literals = {}
        # find literals
        lit = self.STRING_LITERALS.search(input_string)
        while lit:
            g = lit.group()
            key = 'LITERAL%s' % n
            # set literal by key and value by removing " and ' wrapper chars
            self.literals[key] = g[1:-1]
            # remove literal from original input and replace with key
            input_string = input_string.replace(g, " %s " % key)
            # make a new search and loop until all is found
            lit = self.STRING_LITERALS.search(input_string)
            # next literal number
            n += 1
        # set literal string and its length for recursive parser
        self.literal_string = input_string
        self.literal_string_length = len(self.literal_string)
    
    # sanitize or / and keywords, they are optional anyway and just needed for readability
    # and to decide what is the mutual starting point for boolean logic on the first node level
    def sanitize(self, s):
        # make sentence well formatted: "(A and(B)   or C)" -> "(A and (B) or C)"
        #s = self.PREPROCESS_OPERATORS1.sub("\g<1> \g<2>\g<5>\g<3> \g<4>", s)
        #s = self.PREPROCESS_OPERATORS2.sub("\g<1> \g<2>\g<5>\g<3> \g<4>", s)
        #s = self.PREPROCESS_OPERATORS3.sub("\g<1> \g<2>\g<5>\g<3> \g<4>", s)
        # replace operators with empty space
        s = self.OPERATORS.sub(' ', s)
        # prepare to remove exclamation mark, that is used for NOT boolean logic tree
        s = self.NOT.sub(' \g<1> ', s)
        # prepare to remove ^ mark, that is used for XOR boolean logic tree
        s = self.XOR.sub(' \g<1> ', s)
        # remove extra double, triple and other longs whitespaces
        # only single spaces between literals are left
        return ' '.join(s.split())

    # convert literal placeholders back to original strings
    # and remove reserved boolean operator keywords from string
    def convertLiteralToList(self, tail):
        def substitute(x):
            # not operator becomes -1
            if x.strip() == "!" or x.strip() == "not" or x.strip() == "¬":
                return -1
            # xor operator becomes -2
            elif x.strip() == "^" or x.strip() == "xor" or x.strip() == "∨":
                return -2
            # xor operator becomes -3
            elif x.strip() == "+" or x.strip() == "xand" or x.strip() == "∧":
                return -3
            # literal placeholders gets replaced
            elif x in self.literals:
                y = self.literals[x]
                for w in self.wrappers:
                    y = y.replace("\\%s" % w, w)
                return y
            # if we find something else, return that
            else:
                return x
        # before substitution split sentence to literals
        s = self.sanitize(tail)
        if s:
            return map(substitute, s.split(' '))
        else:
            return None
    
    def setMutual(self, s, level, n):
        # if it is not yet set and level is n (0 or 1) and OPERATOR is found from string
        # this will take the first boolean AND/OR from the first levels of recursion
        # calculating from left and make it the mutual starting point
        if level == n:
            o = self.OPERATORS.search(s)
            if o:
                t = o.group().lower().strip()
                self.mutual = True if t == "and" or t == "&" or t == "∧" else False
        
    def recursiveParenthesesGroups(self, i=0, level=0):
        # sub routine for open and close parentheses
        def _(root, tail, level, n):
            # join tail to string
            x = ''.join(tail).strip()
            # only if string is not empty
            if x:
                # only id mutual is not set,
                # try to retrieve mutual boolean starting point
                if self.mutual is None:
                    self.setMutual(x, level, n)
                # add literals to root and flush tail
                y = self.convertLiteralToList(x)
                if y:
                    root.extend(y)
        
        # collect sub and final result to these variables
        tail, root = [], []
        
        while i < self.literal_string_length:
            char = self.literal_string[i]
            # create a new node if (
            if char == self.OPEN_PARENTHESES:
                _(root, tail, level, 1)
                tail = []
                # now recursively get the next data
                sub, i, level = self.recursiveParenthesesGroups(i+1, level+1)
                root.append(sub)
            # close the node and return back to parent if )
            elif char == self.CLOSE_PARENTHESES:
                level -= 1
                _(root, tail, level, 0)
                # it is important to return these information back to parent node
                return root, i + 1, level
            # we are staying on same node level, collect characters
            else:
                tail.append(char)
                i += 1
        # when the whole input string is processed:
        # add mutual boolean value to the root level for mutual change logic
        self.mutual = self.mutual if type(self.mutual) is bool else True
        # finally return recursively constructed list
        return self.mutual, root

    def parse(self, input_string):
        """ main method """
        self.input_string = input_string.strip()
        self.setLiterals(input_string)
        return self.recursiveParenthesesGroups()

    @staticmethod
    def parseInput(input_string):
        """ bypass object construct """
        c = PLCParser()
        try:
            return c.parse(input_string)
        except:
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
    def deformatInput(lst, short=False, first=False, latex=False):
        """ bypass object construct """
        c = PLCParser()
        try:
            return c.deformat(lst, short, first, latex)
        except:
            return None
    
    def deformat(self, lst, short=False, first=False, latex=False):
        
        self.first_operator_used = False
        self.was_first = False

        def _(current_item, mutual, negate=False, xor=False, xand=False, short=False, first=False, latex=False):
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
            a = [self.OPEN_PARENTHESES]
            # should we negate next item
            next_item_negate = False
            # is next item group xor
            next_item_xor = False
            # is next item group xand
            next_item_xand = False
            # loop all items
            for i, item in enumerate(current_item):
                # negation marker
                if item == -1:
                    next_item_negate = True
                    if i == 0:
                        self.was_first = True
                # xor marker
                elif item == -2:
                    next_item_xor = True
                    if i == 0:
                        self.was_first = True
                # xand marker
                elif item == -3:
                    next_item_xand = True
                    if i == 0:
                        self.was_first = True
                # item or list
                else:
                    # should we add operators?
                    # no if item is the first OR
                    # not / xor was used OR
                    # we use only the first
                    if i > 0 and not self.was_first and not self.first_operator_used:
                        if mutual:
                            if short:
                                a.append('&')
                            elif latex:
                                a.append('∧')
                            else:
                                a.append('and')
                        else:
                            if short:
                                a.append('|')
                            elif latex:
                                a.append('∨')
                            else:
                                a.append('or')
                        if first:
                            first = False
                            self.first_operator_used = True
                    # negate works both for groups and items
                    if next_item_negate:
                        if short:
                            a.append('!')
                        elif latex:
                            a.append('¬')
                        else:
                            a.append('not')
                    # xor and xand works for groups only
                    if isinstance(item, list):
                        if next_item_xor:
                            if short:
                                a.append('^')
                            elif latex:
                                a.append('⊕')
                            else:
                                a.append('xor')
                        if next_item_xand:
                            if short:
                                a.append('+')
                            elif latex:
                                a.append('⊖')
                            else:
                                a.append('xand')
                    # recursively add next items
                    a.append(_(item, not mutual, next_item_negate, next_item_xor, next_item_xand, short=short, first=first, latex=latex))
                    # reset negation, xor and xand
                    next_item_negate = False
                    next_item_xor = False
                    next_item_xand = False
                    self.was_first = False
            # close clause
            a.append(self.CLOSE_PARENTHESES)
            return ' '.join(map(str, a))
        # call sub routine to deformat structure
        return _(lst[1], not lst[0], short=short, first=first, latex=latex)

    @staticmethod
    def evaluateInput(input_string, table={}):
        """ bypass object construct """
        c = PLCParser()
        try:
            return c.evaluate(input_string, table)
        except:
            return None

    def evaluate(self, i, table={}):
        if type(i) == str:
            i = self.parse(i)
        if type(i) == tuple:
            return self.truth_value(i[1], not i[0], table)
        return None

    def truth_value(self, current_item, mutual=True, table=None, negate=False, xor=False, xand=False):
        # if item is not a list, check the truth value
        if not isinstance(current_item, list):
            if table and current_item in table:
                current_item = table[current_item]
            if str(current_item).lower() in ['true', '1']:
                return not negate 
            return negate
        # item is a list
        a = []
        # should we negate next item, was it a list or values
        next_item_negate, next_item_xor, next_item_xand = False, False, False
        for item in current_item:
            # negation marker
            if item == -1:
                next_item_negate = True
            # xor marker
            elif item == -2:
                next_item_xor = True
            # xand marker
            elif item == -3:
                next_item_xand = True
            else:
                a.append(self.truth_value(item, not mutual, table, next_item_negate, next_item_xor, next_item_xand))
                # reset negation and xor
                next_item_negate = False
                next_item_xor = False
                next_item_xand = False
        # is group AND / OR / XOR
        # take care of negation for the list result too
        if xor:
            # if only one of the values is true, but not more
            return not (len(a) > 1 and len([b for b in a if b]) == 1) if negate else (len(a) > 1 and len([b for b in a if b]) == 1)
        elif xand:
            # if any of the values are true, but not all
            return not (any(a) and not all(a)) if negate else any(a) and not all(a)
        elif mutual:
            # if all values are true
            return not all(a) if negate else all(a)
        else:
            # if some of the values is true
            return not any(a) if negate else any(a)

parseInput = PLCParser.parseInput
evaluateInput = PLCParser.evaluateInput
deformatInput = PLCParser.deformatInput
validateInput = PLCParser.validateInput
