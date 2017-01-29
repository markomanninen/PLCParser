<?php

/**
 * PLCParser - Prepositional logic clause parser (PHP)
 *
 * Author: Marko Manninen <elonmedia@gmail.com>
 * Date: 22.1.2017
*/

class PLCParser
{
    # normalize string to standard format i.e.
    # separate operators from other strings and add spaces
    # for keywords: xor or and not
    private $PREPROCESS_OPERATORS1 = 
            '/([\)])[\s]*(xor|or|xand|and|not)[\s]+|'.
            '[\s]+(xor|or|xand|and|not)[\s]*([\(])|'.
            '[\s]+(xor|or|xand|and|not)[\s]+/i';
    # for special chars: ^ | & !
    private $PREPROCESS_OPERATORS2 = 
            '/([\)])[\s]*(\^|\||\+|\&|\!)[\s]+|'.
            '[\s]+(\^|\||\+|\&|\!)[\s]*([\(])|'.
            '[\s]+(\^|\||\+|\&|\!)[\s]+/';
    // for math chars: ⊖ ⊕ ∨ ∧ ¬
    private $PREPROCESS_OPERATORS3 = 
            '/([\)])[\s]*(⊕|∨|⊖|∧|¬)[\s]+|'.
            '[\s]+(⊕|∨|⊖|∧|¬)[\s]*([\(])|'.
            '[\s]+(⊕|∨|⊖|∧|¬)[\s]+/';
    # get operators from start, middle and end of the string
    private $OPERATORS = '/(^|\s+)(or|and|\||\&|∨|∧)(\s+|$)/i';
    # find xor operator
    private $XOR = '/(\^|xor|⊕)/i';
    // find xand operator, meaningful in groups only
    private $XAND = '/(\\+|xand|⊖)/i';
    # find not operator
    private $NOT = '/(\!|not|¬)/i';

    public function __construct($parentheses = ['(', ')'], $wrappers = ["'", '"']) {
        # constructor
        list($this->OPEN_PARENTHESES, $this->CLOSE_PARENTHESES) = $parentheses;
        # http://stackoverflow.com/questions/430759/regex-for-managing-escaped-characters-for-items-like-string-literals
        $this->wrappers = $wrappers;
        while (list($k, $w) = each($wrappers)) {
            $a[] = $w.'[^'.$w.'\\\\]*(?:\\.[^'.$w.'\\\\]*)*'.$w;
        }
        $this->STRING_LITERALS = '/'.implode('|', $a).'/';
        $this->mutual = NULL;
    }
    
    private function setLiterals($input_string) {
        $n = 1;
        $this->literals = [];
        # find literals
        preg_match($this->STRING_LITERALS, $input_string, $groups);
        while ($groups) {
            foreach ($groups as $g) {
                $key = 'LITERAL' . $n;
                # set literal by key and value by removing " and ' wrapper chars
                $this->literals[$key] = substr($g, 1, -1);
                # remove literal from original input and replace with key
                $input_string = str_replace($g, " $key ", $input_string);
                # next literal number
                $n += 1;
            }
            # make a new search and loop until all is found
            preg_match($this->STRING_LITERALS, $input_string, $groups);
        }
        # set literal string and its length for recursive parser
        $this->literal_string = $input_string;
        $this->literal_string_length = strlen($input_string);
    }
    
    # sanitize or / and keywords, they are optional anyway and 
    # just needed for readability and to decide what is the mutual 
    # starting point for boolean logic on the first node level
    public function sanitize($s) {
        # make sentence well formatted: "(A and(B)   or C)" -> "(A and (B) or C)"
        $s = preg_replace($this->PREPROCESS_OPERATORS1, '$1 $2$5$3 $4', $s);
        $s = preg_replace($this->PREPROCESS_OPERATORS2, '$1 $2$5$3 $4', $s);
        $s = preg_replace($this->PREPROCESS_OPERATORS3, '$1 $2$5$3 $4', $s);
        # replace operators with empty space
        $s = preg_replace($this->OPERATORS, ' ', $s);
        # prepare to remove exclamation mark, that is used for NOT boolean logic tree
        $s = preg_replace($this->NOT, ' $1 ', $s);
        # prepare to remove ^ mark, that is used for XOR boolean logic tree
        $s = preg_replace($this->XOR, ' $1 ', $s);
        // prepare to remove + mark, that is used for XAND boolean logic tree
        $s = preg_replace($this->XAND, ' $1 ', $s);
        # remove extra double, triple and other longs whitespaces
        # only single spaces between literals are left
        return implode(' ', array_filter(explode(' ', $s)));
    }

    private function substitute(&$x) {
        # not operator becomes -1
        if ($x == "!" || $x == "not" || $x == "¬") {
            $x = -1;
        # xor operator becomes -2
        } elseif ($x == "^" || $x == "xor" || $x == "⊕") {
            $x = -2;
        # xand operator becomes -3
        } elseif ($x == "+" || $x == "xand" || $x == "⊖") {
            $x = -3;
        # literal placeholders gets replaced
        } elseif (isset($this->literals[$x])) {
            $y = $this->literals[$x];
            # double escaped wrapper characters should be decoded
            foreach ($this->wrappers as $w)
                $y = str_replace("\\$w", $w, $y);
            $x = $y;
        }
        # if we find something else, return that
        return $x;
    }

    # convert literal placeholders back to original strings
    # and remove reserved boolean operator keywords from string
    private function convertLiteralToList($tail) {
        # before substitution split sentence to literals
        if ($s = $this->sanitize($tail)) {
            $a = explode(' ', $s);
            array_walk($a, array($this, 'substitute'));
            return $a;
        }
        return NULL;
    }

    private function setMutual($s, $level, $n) {
        # if it is not yet set and level is n (0 or 1) and OPERATOR is found from string
        # this will take the first boolean AND/OR from the first levels of recursion
        # calculating from left and make it the mutual starting point
        if ($level == $n) {
            preg_match($this->OPERATORS, $s, $groups);
            if ($groups) {
                $t = trim(strtolower(implode('', $groups)));
                $this->mutual = strpos($t, "and") > -1 || 
                                strpos($t, "&") > -1 || 
                                strpos($t, "∧") > -1 ? TRUE : FALSE;
            }
        }
    }

    # sub routine for open and close parentheses
    private function _sub(&$root, $tail, $level, $n) {
        # join tail to string
        # only if string is not empty
        if ($x = trim(implode('', $tail))) {
            # only id mutual is not set,
            # try to retrieve mutual boolean starting point
            if ($this->mutual == NULL) {
                $this->setMutual($x, $level, $n);
            }
            # add literals to root and flush tail
            if ($y = $this->convertLiteralToList($x)) {
                $root[] = $y; #extend!
            }
        }
    }

    private function recursiveParenthesesGroups($i=0, $level=0) {

        # collect sub and final result to these variables
        list($tail, $root) = [[], []];

        while ($i < $this->literal_string_length) {
            $char = substr($this->literal_string, $i, 1);
            # create a new node if (
            if ($char == $this->OPEN_PARENTHESES) {
                $this->_sub($root, $tail, $level, 1);
                $tail = [];
                # now recursively get the next data
                list($sub, $i, $level) = $this->recursiveParenthesesGroups($i+1, $level+1);
                $root[] = $sub;
            # close the node and return back to parent if )
            } elseif ($char == $this->CLOSE_PARENTHESES) {
                $level -= 1;
                $this->_sub($root, $tail, $level, 0);
                # it is important to return these information back to parent node
                return [$root, $i + 1, $level];
            # we are staying on same node level, collect characters
            } else {
                $tail[] = $char;
                $i += 1;
            }
        }
        # when the whole input string is processed:
        # add mutual boolean value to the root level for mutual change logic
        $this->mutual = is_bool($this->mutual) ? $this->mutual : TRUE;
        # finally return recursively constructed list
        return [$this->mutual, $root];
    }

    public function parse($input_string) {
        #""" main method """
        $this->input_string = trim($input_string);
        $this->setLiterals($input_string);
        return $this->recursiveParenthesesGroups();
    }

    static function parseInput($input_string){
        # bypass object construct
        $c = new PLCParser();
        try {
            return $c->parse($input_string);
        } catch (Exception $e) {
            return NULL;
        }
    }

    public function deFormat($lst, $short=FALSE, $first=FALSE, $latex=FALSE) {
        return NULL;
    }
    
    static function deformatInput($lst, $short=FALSE, $first=FALSE, $latex=FALSE){
        # bypass object construct
        $c = new PLCParser();
        try {
            return $c->deformat($lst, $short, $first, $latex);
        } catch (Exception $e) {
            return NULL;
        }
    }

    public function evaluate($input, $table=array()) {
        return NULL;
    }

    static function evaluateInput($input, $table=array()){
        # bypass object construct
        $c = new PLCParser();
        try {
            return $c->evaluate($input, $table);
        } catch (Exception $e) {
            return NULL;
        }
    }

    public function validate($input) {
        return NULL;
    }

    static function validateInput($input){
        # bypass object construct
        $c = new PLCParser();
        try {
            return $c->validate($input);
        } catch (Exception $e) {
            return NULL;
        }
    }

}
