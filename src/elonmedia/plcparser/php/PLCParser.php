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
        #$s = preg_replace($this->PREPROCESS_OPERATORS1, '$1 $2$5$3 $4', $s);
        #$s = preg_replace($this->PREPROCESS_OPERATORS2, '$1 $2$5$3 $4', $s);
        #$s = preg_replace($this->PREPROCESS_OPERATORS3, '$1 $2$5$3 $4', $s);
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
        # array filter needs to have strlen to keep 0 numerals!
        return implode(' ', array_filter(explode(' ', $s), 'strlen'));
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
	}

	# convert literal placeholders back to original strings
	# and remove reserved boolean operator keywords from string
	private function convertLiteralToList($tail) {
		# before substitution split sentence to literals
		$s = $this->sanitize($tail);
		if (count($s)) {
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
	private function subRecursiveParenthesesGroups(&$root, $tail, $level, $n) {
		# join tail to string
		# only if string is not empty
		if ($x = trim(implode('', $tail))) {
			# only id mutual is not set,
			# try to retrieve mutual boolean starting point
			if ($this->mutual == NULL) {
				$this->setMutual($x, $level, $n);
			}
			# add literals to root and flush tail
			$y = $this->convertLiteralToList($x);
			if (count($y)) {
				$root = array_merge($root, $y);
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
				$this->subRecursiveParenthesesGroups($root, $tail, $level, 1);
				$tail = [];
				# now recursively get the next data
				@list($sub, $i, $level) = $this->recursiveParenthesesGroups($i+1, $level+1);
				$root[] = $sub;
			# close the node and return back to parent if )
			} elseif ($char == $this->CLOSE_PARENTHESES) {
				$level -= 1;
				$this->subRecursiveParenthesesGroups($root, $tail, $level, 0);
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

        $first_operator_used = FALSE;
        $was_first = FALSE;

        $recursive = function ($current_item, $mutual, $negate=FALSE, $xor=FALSE, $xand=FALSE) use (&$recursive, &$short, &$first, &$latex, &$first_operator_used, &$was_first) {
			// if item is not a list, return value
			if (!is_array($current_item)) {
				// boolean values
				if (($current_item == 1 || strtolower($current_item) == 'true' ||
					 $current_item == 0 || strtolower($current_item) == 'false'))
					return $current_item;
				// normal items wrapped by the first configured wrapper char
				// escaping wrapper char inside the string
				$current_item = replace($this->wrappers[0], '\\'.$this->wrappers[0], $current_item);
				return $this->wrappers[0].$current_item.$this->wrappers[0];
			}
			// item is a list
			$a = array($this->OPEN_PARENTHESES);
			// should we negate next item
			$next_item_negate = FALSE;
			// is next item group xor
			$next_item_xor = FALSE;
			// is next item group xand
			$next_item_xand = FALSE;
			// loop all items
			foreach ($current_item as $i => $item) {
				// negation marker
				if ($item == -1) {
					$next_item_negate = TRUE;
					if ($i == 0) $was_first = TRUE;
				// xor marker
				} else if ($item == -2) {
					$next_item_xor = TRUE;
					if ($i == 0) $was_first = TRUE;
				// xand marker
				} else if ($item == -3) {
					$next_item_xand = TRUE;
					if ($i == 0) $was_first = TRUE;
				// item or list
				} else {
					// should we add operators?
					// no if item is the first OR
					// not / xor was used OR
					// we use only the first
					if ($i > 0 && !$was_first && !$first_operator_used) {
						if ($mutual) {
							if ($short) $a[] = '&';
							else if ($latex) $a[] = '∧';
							else $a[] = 'and';
						} else {
							if ($short) $a[] = '|';
							else if ($latex) $a[] = '∨';
							else $a[] = 'or';
						}
						if ($first) {
							$first = FALSE;
							$first_operator_used = TRUE;
						}
					}
					// negate works both for groups and items
					if ($next_item_negate) {
						if ($short) $a[] = '!';
						else if ($latex) $a[] = '¬';
						else $a[] = 'not';
					}
					// xor and xand works for groups only
					if (is_array($item)) {
						if ($next_item_xor) {
							if ($short) $a[] = '^';
							else if ($latex) $a[] = '⊕';
							else $a[] = 'xor';
						}
						if ($next_item_xand) {
							if ($short) $a[] = '+';
							else if ($latex) $a[] = '⊖';
							else $a[] = 'xand';
						}
					}
					// recursively add next items
					$a[] = $recursive($item, !$mutual, $next_item_negate, $next_item_xor, $next_item_xand);
					// reset negation, xor and xand
					$next_item_negate = FALSE;
					$next_item_xor = FALSE;
					$next_item_xand = FALSE;
					$was_first = FALSE;
				}
			}
			$a[] = $this->CLOSE_PARENTHESES;
			return implode(' ', $a);
		};
		// call sub routine to deformat structure
		return $recursive($lst[1], !$lst[0]);
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
		// if input is string, parse it first
		if (is_string($input)) {
			$input = $this->parse($input);
		}
		// assume input is well formed so that we can calculate the truth value
		if (is_array($input)) {
			return $this->truthValue($input[1], !$input[0], $table, FALSE, FALSE, FALSE);
		}
		return NULL;
	}

	// xand, one or more, but not all
	// single true is false, single false is false
	private function some($a) {
		return $this->any($a) && !$this->all($a);
	}
	// or, at least one, may be all, maybe one
	private function any($a) {
		return !empty(array_filter($a));
	}
	// all, may be one
	private function all($a) {
		return count(array_filter($a)) == count($a);
	}
	// xor, only one of many
	// single true is true, single false is true
	private function one($a) {
		return count($a) == 1 || count(array_filter($a)) == 1;
	}

	private function truthValue($current_item, $mutual, $table, $negate, $xor, $xand) {
		// if item is not a list, check the truth value
		if (!is_array($current_item)) {
			// see if translation table is given
			if ($table && isset($table[$current_item])) {
				$current_item = $table[$current_item];
			}
			// cast to string and lower case for easier comparison
			$v = strtolower("".$current_item);
			if ($v == 'true' || $v == '1') {
				return !$negate;
			}
			return $negate;
		}
		// item is a list
		$a = array();
		// should we negate next item, was it a list or values
		$next_item_negate = FALSE;
		$next_item_xor = FALSE;
		$next_item_xand = FALSE;

		foreach ($current_item as $item) {
			// negation marker
			if ($item == -1) {
				$next_item_negate = TRUE;
			// xor marker
			} else if ($item == -2) {
				$next_item_xor = TRUE;
			// xand marker
			} else if ($item == -3) {
				$next_item_xand = TRUE;
			} else {
				$a[] = $this->truthValue($item, !$mutual, $table, $next_item_negate, $next_item_xor, $next_item_xand);
				// reset negation and xor
				$next_item_negate = FALSE;
				$next_item_xor = FALSE;
				$next_item_xand = FALSE;
			}
		}
		// is group AND / OR / XOR
		// take care of negation for the list result too
		if ($xor) {
			// if only one of the values is true, but not more
			if ($negate) {
				return !$this->one($a);
			}
			return $this->one($a);
		} else if ($xand) {
			// if any of the values is true, but not all
			if ($negate) {
				return !$this->some($a);
			}
			return $this->some($a);
		} else if ($mutual) {
			// if all values are true
			if ($negate) {
				return !$this->all($a);
			}
			return $this->all($a);
		} else {
			// if some of the values is true
			if ($negate) {
				return !$this->any($a);
			}
			return $this->any($a);
		}
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

	public function validate($input_string, $open=NULL, $close=NULL, $wrappers=NULL, $escape_char=NULL) {
		// check parentheses and wrappers characters that they match
		// for example (, [, {
		$open = $open ? $open : $this->OPEN_PARENTHESES;
		// for example: }, ], )
		$close = $close ? $close : $this->CLOSE_PARENTHESES;
		// multiple wrapper chars accepted, for example ['"', "'", "´"]
		$wrappers = $wrappers ? $wrappers : $this->wrappers;
		// is is possible to pass a different escape char, but it is probably
		// not a good idea because many of the string processors use the same
		$escape_char = $escape_char ? $escape_char : '\\';

		$stack = array();
		$previous = NULL;

		// loop over all characters in a string
		$chars = preg_split('/(?<!^)(?!$)/u', $input_string);
		foreach ($chars as $current) {
			// if previous character was escape character, then 
			// swap it with the current one and continue to the next char
			if ($previous == $escape_char) {
				// see if current character is escape char, then there are
				// two of them in row and we should reset previous marker
				if ($current == $escape_char) $previous = NULL;
				else $previous = $current;
				continue;
			}
			// last stacked char. not that this differs from the previous value which
			// is the previous char from string. last is the last char from stack
			$last = @array_slice($stack, -1)[0];
			// if we are inside a wrapper accept ANY character 
			// until the next unescaped wrapper char occurs
			if (in_array($last, $wrappers) && $current != $last) {
				// swap the current so that we can escape wrapper inside wrappers: "\""
				$previous = $current;
				continue;
			}
			// push open parenthesis or wrapper to the stack
			if ($current == $open ||
				(in_array($current, $wrappers) && $current != $last)) {
				$stack[] = $current;
			// prepare to pop last parenthesis or wrapper
			} else if ($current == $close || 
				 	   in_array($current, $wrappers)) {
				// if there is nothing on stack, should already return false
				if (count($stack) == 0) {
					return FALSE;
				} else {
					// if we encounter wrapper char take the last wrapper char out from stack
					if (in_array($last, $wrappers) ||
						// or if the last char was open and current close parenthsis
						($last == $open && $current == $close)) {
						array_pop($stack);
					} else {
						return FALSE;
					}
				}
			}
			// update previous char
			$previous = $current;
		}
		// if there is something on stack then no closing char was found
		return count($stack) == 0;
	}

	static function validateInput($input_string){
		# bypass object construct
		$c = new PLCParser();
		try {
			return $c->validate($input_string);
		} catch (Exception $e) {
			return NULL;
		}
	}

}
