# PLCParser.py
import re

"""

Prepositional logic clause parser

Author: Marko Manninen <elonmedia@gmail.com>
Date: 22.1.2017

"""

class PLCParser():
    
    # normalize string to standard format i.e.
    # separate operators from other strings and add spaces
    # for keywords: xor or and not
    PREPROCESS_OPERATORS1 = re.compile(
            r'([\)])[\s]*(xor|or|and|not)[\s]+|'+\
            r'[\s]+(xor|or|and|not)[\s]*([\(])|'+\
            r'[\s]+(xor|or|and|not)[\s]+'
            , re.IGNORECASE)
    # for special chars: ^ | & !
    PREPROCESS_OPERATORS2 = re.compile(
            r'([\)])[\s]*(\^|\||\&|\!)[\s]+|'+\
            r'[\s]+(\^|\||\&|\!)[\s]*([\(])|'+\
            r'[\s]+(\^|\||\&|\!)[\s]+')
    # get operators from start, middle and end of the string
    OPERATORS = re.compile(r'(^|\s+)(or|and|\||\&)(\s+|$)', re.IGNORECASE)
    # find xor operator
    XOR = re.compile(r'(\^|xor)', re.IGNORECASE)
    # find not operator
    NOT = re.compile(r'(\!|not)', re.IGNORECASE)

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
        s = self.PREPROCESS_OPERATORS1.sub("\g<1> \g<2>\g<5>\g<3> \g<4>", s)
        s = self.PREPROCESS_OPERATORS2.sub("\g<1> \g<2>\g<5>\g<3> \g<4>", s)
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
            if x.strip() == "!" or x.strip() == "not":
                return -1
            # xor operator becomes 0
            elif x.strip() == "^" or x.strip() == "xor":
                return 0
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
                self.mutual = True if t == "and" or t == "&" else False
        
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
    
    def deFormat(self, lst, short=False, firstOnly=False):
        pass

parseInput = PLCParser.parseInput