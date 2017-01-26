"use strict";

function PLCParser(parentheses, wrappers) {
	// main object to collect methods. it will be returned to the caller
	var parser = parser || {}

	// normalize string to standard format i.e.
	// separate operators from other strings and add spaces
	// for keywords: xor or and not
	parser.PREPROCESS_OPERATORS1 = new RegExp(
			'([\)])[\s]*(xor|or|and|not)[\s]+|'+
			'[\s]+(xor|or|and|not)[\s]*([\(])|'+
			'[\s]+(xor|or|and|not)[\s]+'
			, 'ig')
	// for special chars: ^ | & !
	parser.PREPROCESS_OPERATORS2 = new RegExp(
			'([\)])[\s]*(\^|\\||\&|\!)[\s]+|'+
			'[\s]+(\^|\\||\&|\!)[\s]*([\(])|'+
			'[\s]+(\^|\\||\&|\!)[\s]+')
	// for math chars: ⊕ ∨ ∧ ¬
	parser.PREPROCESS_OPERATORS3 = new RegExp(
			'([\)])[\s]*(⊕|∨|∧|¬)[\s]+|'+
			'[\s]+(⊕|∨|∧|¬)[\s]*([\(])|'+
			'[\s]+(⊕|∨|∧|¬)[\s]+')
	// get operators from start, middle and end of the string
	parser.OPERATORS = new RegExp('(^|\s*)(or|and|\\||\&|∨|∧)(\s*|$)', 'ig')
	// find xor operator
	parser.XOR = new RegExp('(\^|xor|⊕)', 'ig')
	// find not operator
	parser.NOT = new RegExp('(\!|not|¬)', 'ig')
	// setup parentheses
	parser.OPEN_PARENTHESES = null
	if (parentheses && parentheses[0]) parser.OPEN_PARENTHESES = parentheses[0]
	else parser.OPEN_PARENTHESES = '('
	parser.CLOSE_PARENTHESES = null
	if (parentheses && parentheses[1]) parser.CLOSE_PARENTHESES = parentheses[1]
	else parser.CLOSE_PARENTHESES = ')'
	//http://stackoverflow.com/questions/430759/regex-for-managing-escaped-characters-for-items-like-string-literals  
	parser.wrappers = wrappers || ["'", '"']

	function createLiteralsRegEx(wrappers) {
		var i, s, len = wrappers.length, a = []
		var t = "%s[^%s\\\\]*(?:\\\\.[^%s\\\\]*)*%s"
		var re = new RegExp('%s', 'g')
		for (i=0; i<len; ++i) {
		  if (i in wrappers) {
			s = wrappers[i];
			a.push(t.replace(re, s))
		  }
		}
		return a.join('|')
	}
	
	parser.STRING_LITERALS = new RegExp(createLiteralsRegEx(parser.wrappers), 'g')
	parser.mutual = null
	parser.input_string = ""
	parser.literals = {}

	parser.setLiterals = function(input_string) {
		var n = 1
		this.literals = {}
		// find literals
		var groups = input_string.match(this.STRING_LITERALS)
		while (groups !== null) {
			var i, len = this.wrappers.length
			for (i=0; i<len; ++i) {
			  if (i in groups) {
				var g = groups[i]
				var key = 'LITERAL' + n
				// set literal by key and value by removing " and ' wrapper chars
				this.literals[key] = g.substring(1, g.length-1)
				// remove literal from original input and replace with key
				input_string = input_string.replace(g, " "+key+" ")
				// next literal number
				n += 1
			  }
			}
			// make a new search and loop until all is found
			groups = input_string.match(this.STRING_LITERALS)
		}
		// set literal string and its length for recursive parser
		this.literal_string = input_string
		this.literal_string_length = this.literal_string.length
	}

	parser.sanitize = function(s) {
		// make sentence well formatted: 
		// "(A and(B)   or C)" -> "(A and (B) or C)"
		s = s.replace(this.PREPROCESS_OPERATORS1, "$1 $2$5$3 $4")
		s = s.replace(this.PREPROCESS_OPERATORS2, "$1 $2$5$3 $4")
		s = s.replace(this.PREPROCESS_OPERATORS3, "$1 $2$5$3 $4")
		// replace operators with empty space
		s = s.replace(this.OPERATORS, ' ')
		// prepare to remove exclamation mark, that is used for NOT boolean logic tree
		s = s.replace(this.NOT, ' $1 ')
		// prepare to remove ^ mark, that is used for XOR boolean logic tree
		s = s.replace(this.XOR, ' $1 ')
		// remove extra double, triple and other longs whitespaces
		// only single spaces between literals are left
		return s.split(" ").filter(function(e){ return e === 0 || e }).join(' ')
	}

	parser.substitute = function(x) {
		// not operator becomes -1
		if (x == "!" || x == "not" || x == "¬") {
			x = -1
		// xor operator becomes 0
		} else if (x == "^" || x == "xor" || x == "⊕") {
			x = -2
		// literal placeholders gets replaced
		} else if (x in parser.literals) {
			var y = parser.literals[x]
			// double escaped wrapper characters should be decoded
			var r, i, w, len = parser.wrappers.length
			for (i=0; i<len; ++i) {
			  if (i in parser.wrappers) {
				  w = parser.wrappers[i]
				  r = new RegExp('\\\\'+w, 'g')
				  y = y.replace(r, w)
			  }
			}
			x = y
		}
		// if we find something else, return that
		return x
	}


	parser.convertLiteralToList = function(tail) {
		// before substitution split sentence to literals
		var s = this.sanitize(tail)
		if (s) {
			var a = s.split(' ')
			return a.map(this.substitute)
		}
		return NULL;
	}

	parser.setMutual = function(s, level, n) {
		// if it is not yet set and level is n (0 or 1) and OPERATOR is found from string
		// this will take the first boolean AND/OR from the first levels of recursion
		// calculating from left and make it the mutual starting point
		if (level == n) {
			var o = s.match(this.OPERATORS)
			if (o) {
				var t = o[0].toLowerCase().trim()
				this.mutual = (t == "and" || t == "&" || t == "∧")
			}
		}
	}

	// sub routine for open and close parentheses
	parser._sub = function(root, tail, level, n) {
		// join tail to string
		// only if string is not empty
		var x = tail.join('').trim()
		if (x) {
			// only id mutual is not set,
			// try to retrieve mutual boolean starting point
			if (this.mutual == null) {
				this.setMutual(x, level, n)
			}
			// add literals to root and flush tail
			var y = this.convertLiteralToList(x)
			if (y) {
				root = root.concat(y)
			}
		}
		return root
	}

	parser.recursiveParenthesesGroups = function(i, level) {
		// defaults
		var i = i || 0
		var level = level || 0

		// collect sub and final result to these variables
		var tail = [], root = []

		while (i < this.literal_string_length) {
			var char = this.literal_string.substring(i, i+1)
			// create a new node if (
			if (char == this.OPEN_PARENTHESES) {
				root = this._sub(root, tail, level, 1)
				tail = []
				// now recursively get the next data
				var l = this.recursiveParenthesesGroups(i+1, level+1)
				root.push(l[0])
				i = l[1]
				level = l[2]
			// close the node and return back to parent if )
			} else if (char == this.CLOSE_PARENTHESES) {
				level -= 1;
				root = this._sub(root, tail, level, 0)
				// it is important to return these information back to parent node
				return [root, i + 1, level]
			// we are staying on same node level, collect characters
			} else {
				tail.push(char)
				i += 1
			}
		}
		// when the whole input string is processed:
		// add mutual boolean value to the root level for mutual change logic
		if (this.mutual === null)
			this.mutual = true
		// finally return recursively constructed list
		return [this.mutual, root]
	}

	parser.parse = function(input_string) {
		// main method
		this.input_string = input_string.trim()
		this.setLiterals(this.input_string)
		return this.recursiveParenthesesGroups()
	}

	parser.validate = function validate(s) {
		// check parentheses and wrappers characters that they match
		// TODO: use class parentheses and wrappers variable
		var result = true
		var stack = []
		var current, last, previous
		for (var i = 0, l = s.length; i < l; i++) {
			// current character
			current = s.substring(i, i+1)
			// if clas character was escape character, then 
			// swap it with the current one and continue to the next char
			if (previous == '\\') {
				previous = current
				continue;
			}
			// last stacked char
			last = stack.last()

			if (
			((current == '(' || current == '[' || current == '{') && last != "'" && last != "'" ) ||
				((current == "'" && last != "'" && last != '\\')) ||
				 (current == '"' && last != '"' && last != '\\')) {
				stack.push(current);
			} else if(
			((current == ')' || current == ']' || current == '}' ) && last != "'" && last != '"') || 
											  current == "'" ||
											  current == '"') {
				if (stack.length == 0) {
					result = false
				} else {
					if((current == ')' && last == '(') ||
					   (current == ']' && last == '[') ||
					   (current == '}' && last == '{') ||
					   (current == '"' && last == '"') ||
					   (current == "'" && last == "'")) {
						stack.pop()
					} else {
						result = false
					}
				}
			}
			// update previous char
			previous = current
		}
		// no closing char found
		if (stack.length > 0) result = false;
		return result
	}

	parser.deformat = function(lst, short, first, latex) {
		var first_operator_used = false
		function _(current_item, mutual, negate, xor) {
			// if item is not a list, return value
			if (typeof(current_item) !== typeof([])) {
				if ((current_item == 1 || current_item.toLowerCase() == 'true' ||
					 current_item == 0 || current_item.toLowerCase() == 'false'))
					return current_item
				return parser.wrappers[0]+current_item+parser.wrappers[0]
			}
			// item is a list
			var a = ['(']
			// should we negate next item, was it a list or values
			var next_item_negate = false, next_item_xor = false, was_first = false
			var i, len = current_item.length
			for (i=0; i<len; ++i) {
				var item = current_item[i]
				// negation marker
				if (item == -1) {
					next_item_negate = true
					if (i==0) was_first = true
				// xor marker
				} else if (item == -2) {
					next_item_xor = true
					if (i==0) was_first = true
				// item or list
				} else {
					// should we add operators?
					// no if item is the first OR
					// not / xor was used OR
					// we use only the first
					if (i > 0 && !was_first && !first_operator_used) {
						if (mutual) {
							if (short) a.push('&')
							else if (latex) a.push('∧')
							else a.push('and')
						} else {
							if (short) a.push('|')
							else if (latex) a.push('∨')
							else a.push('or')
						}
						if (first) {
							first = false
							first_operator_used = true
						}
					}
					if (next_item_negate) {
						if (short) a.push('!')
						else if (latex) a.push('¬')
						else a.push('not')
					}
					if (next_item_xor && typeof(item) === typeof([])) {
						if (short) a.push('^')
						else if (latex) a.push('⊕')
						else a.push('xor')
					}
					a.push(_(item, !mutual, next_item_negate, next_item_xor))
					// reset negation and xor
					next_item_negate = false
					next_item_xor = false
					was_first = false
				}
			}
			a.push(')');
			return a.join(' ')
		}
		return _(lst[1], !lst[0])
	}

	parser.evaluate = function(input, table) {
		if (typeof(input) == typeof("")) {
			input = this.parse(input)
		}
		if (typeof(input) == typeof([])) {
			return this.truthValue(input[1], !input[0], table, false, false)
		}
		return null
	}

	parser.any = function(a) {
		return a.some(function(x) {return x===true})
	}
	
	parser.all = function(a) {
		return a.every(function(x) {return x===true})
	}
	
	parser.one = function(a) {
		return this.any(a) && !this.all(a)
	}

	parser.truthValue = function(current_item, mutual, table, negate, xor) {
		// if item is not a list, check the truth value
		if (typeof(current_item) !== typeof([])) {
			// see if translation table is given
			if (table && current_item in table)
				current_item = table[current_item]
			// cast to string and lower case for easier comparison
			var v = current_item+"".toLowerCase()
			if (v == 'true' || v == '1') {
				return !negate 
			}
			return negate
		}
		// item is a list
		var a = []
		// should we negate next item, was it a list or values
		var next_item_negate = false, next_item_xor = false
		var i, len = current_item.length
		for (i=0; i<len; ++i) {
			var item = current_item[i]
			// negation marker
			if (item == -1) {
				next_item_negate = true
			// xor marker
			} else if (item == -2) {
				next_item_xor = true
			} else {
				a.push(this.truthValue(item, !mutual, table, next_item_negate, next_item_xor))
				// reset negation and xor
				next_item_negate = false
				next_item_xor = false
			}
		}
		// is group AND / OR / XOR
		// take care of negation for the list result too
		if (xor) {
			// if any of the values is true, but not all
			if (negate) {
				return !parser.one(a)
			}
			return parser.one(a)
		} else if (mutual) {
			// if all values are true
			if (negate) {
				return !parser.all(a)
			}
			return parser.all(a)
		} else {
			// if some of the values is true
			if (negate) {
				return !parser.any(a)
			}
			return parser.any(a)
		}
	}
	return parser
}

PLCParser.parseInput = function(input_string) {
	var c = PLCParser()
	try {
		return c.parse(input_string)
	} catch(err) {
		return null
	}
}

PLCParser.evaluateInput = function(input_string, table) {
	var c = PLCParser()
	try {
		return c.evaluate(input_string, table)
	} catch(err) {
		return null
	}
}

PLCParser.deformatInput = function(a, short, first, latex) {
	var c = PLCParser()
	try {
		return c.deformat(a, short, first, latex)
	} catch(err) {
		return null
	}
}

PLCParser.validateInput = function(s) {
	var c = PLCParser()
	try {
		return c.validate(s)
	} catch(err) {
		return null
	}
}

// for node environment require call
if ( typeof module !== 'undefined' ) {
	if ( typeof module.exports === 'undefined' ) {
		module.exports = {}
	}
	exports.PLCParser = PLCParser
}
