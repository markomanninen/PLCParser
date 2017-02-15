"use strict";

Array.prototype.last = function (c) {
	return this[this.length - 1]
}

	/*
	if (c !== undefined)
		return this[this.length - 1]
	else this[this.length - 1] = c
	*/

RegExp.escape = function( value ) {
	 return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");
}

function PLCParser(parentheses, wrappers, operator_precedence) {
	// main object to collect methods and variables
	// it will be returned to the caller
	var parser = parser || {}
	// setup parentheses
	parser.OPEN_PARENTHESES = null
	if (parentheses && parentheses[0]) parser.OPEN_PARENTHESES = parentheses[0]
	else parser.OPEN_PARENTHESES = '('
	parser.CLOSE_PARENTHESES = null
	if (parentheses && parentheses[1]) parser.CLOSE_PARENTHESES = parentheses[1]
	else parser.CLOSE_PARENTHESES = ')'
	//http://stackoverflow.com/questions/430759/regex-for-managing-escaped-characters-for-items-like-string-literals  
	parser.wrappers = wrappers || ["'", '"']
	parser.operator_schemas = {}
	
	parser.operator_precedence = operator_precedence || [-7, -6, -5, -4, -3, -2, -1]

	parser.BOOLEAN_TRUE = 1
	parser.BOOLEAN_FALSE = 0
	parser.NOT_OPERATOR = -1
	parser.AND_OPERATOR = -2
	parser.XOR_OPERATOR = -3
	parser.OR_OPERATOR = -4
	parser.NAND_OPERATOR = -5
	parser.XNOR_OPERATOR = -6
	parser.NOR_OPERATOR = -7

	parser.ParseException = function(message) {
		this.message = message
		this.name = 'ParseException'
	}

	// or, at least one, may be all, maybe one
	parser.any = function(a) {
		return a.some(function(x) {return x === true})
	}
	// all, may be one
	parser.all = function(a) {
		return a.every(function(x) {return x === true})
	}
	// xor, only one of many
	// single true is true, single false is true
	parser.xor = function(a) {
		return a.filter(function(x) {return x === true}).length % 2 !== 0
	}

	parser.list_type = typeof([])
	parser.string_type = typeof("")
	parser.int_type = typeof(0)

	// https://en.wikipedia.org/wiki/List_of_logic_symbols
	parser.OPERATORS = {}
		// https://en.wikipedia.org/wiki/Negation
	parser.OPERATORS[parser.NOT_OPERATOR]  = {'word': 'not', 'char': '!', 'math': '¬', 'func': function(a) { return ! x}},
		// https://en.wikipedia.org/wiki/Logical_conjunction
	parser.OPERATORS[parser.AND_OPERATOR]  = {'word': 'and', 'char': '&', 'math': '∧', 'func': parser.all},
		// https://en.wikipedia.org/wiki/Exclusive_or
	parser.OPERATORS[parser.XOR_OPERATOR]  = {'word': 'xor', 'char': '^', 'math': '⊕', 'func': parser.xor},
		// https://en.wikipedia.org/wiki/Logical_disjunction
	parser.OPERATORS[parser.OR_OPERATOR]   = {'word': 'or', 'char': '|', 'math': '∨', 'func': parser.any},
		// https://en.wikipedia.org/wiki/Sheffer_stroke
	parser.OPERATORS[parser.NAND_OPERATOR] = {'word': 'nand', 'char': '/', 'math': '↑', 'func': function(a) { return ! parser.all(a)}},
		// https://en.wikipedia.org/wiki/Logical_biconditional
	parser.OPERATORS[parser.XNOR_OPERATOR] = {'word': 'xnor', 'char': '=', 'math': '↔', 'func': function(a) { return ! parser.xor(a)}},
		// https://en.wikipedia.org/wiki/Logical_NOR
	parser.OPERATORS[parser.NOR_OPERATOR]  = {'word': 'nor', 'char': '†', 'math': '↓', 'func': function(a) { return ! parser.any(a)}}

	parser.BOOLEANS = {}

	parser.BOOLEANS[parser.BOOLEAN_TRUE]  = {'word': 'true', 'char': '1', 'math': '⊤'},
	parser.BOOLEANS[parser.BOOLEAN_FALSE] = {'word': 'false', 'char': '0', 'math': '⊥'}

	parser.TRUES = [parser.BOOLEANS[parser.BOOLEAN_TRUE]['word'], 
					parser.BOOLEANS[parser.BOOLEAN_TRUE]['char'], 
					parser.BOOLEANS[parser.BOOLEAN_TRUE]['math']]


	// generate regex to match wrapper on string, 
	// supporting multiple wrappers at the same time
	// " and ' are the default, but ´ is a good one too
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
	parser.input_string = ""
	parser.literals = {}

	parser.setLiterals = function(input_string) {
		this.literals = {}
		// key number
		var n = 1
		// find literals
		var groups = input_string.match(this.STRING_LITERALS)
		while (groups !== null) {
			for (var i=0, l=this.wrappers.length; i<l; ++i) {
			  if (i in groups) {
				var g = groups[i]
				var key = 'L' + n
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

		// wrap parenthesis and operator symbols with space for later correct splitting
		input_string = input_string.replace(new RegExp(RegExp.escape(this.OPEN_PARENTHESES), 'g'), " "+this.OPEN_PARENTHESES+" ")
		input_string = input_string.replace(new RegExp(RegExp.escape(this.CLOSE_PARENTHESES), 'g'), " "+this.CLOSE_PARENTHESES+" ")
		for (var operator in this.OPERATORS) {
			if (this.OPERATORS.hasOwnProperty(operator)) {
				var options = this.OPERATORS[operator]
				input_string = input_string.replace(new RegExp(RegExp.escape(options['math']), 'g'), " "+options['math']+" ")
				input_string = input_string.replace(new RegExp(RegExp.escape(options['char']), 'g'), " "+options['char']+" ")
			}
		}
		// set literal string and its length for recursive parser
		this.literal_string = input_string
		this.literal_string_length = this.literal_string.length
	}

	parser.substitute = function(x) {
		var y = x.trim().toLowerCase()
		var iterate = function(list) {
			for (var op in list) {
			  if (list.hasOwnProperty(op)) {
				var d = list[op]
				if (y == d['word'] || y == d['char'] || y == d['math']) {
					// even thou op key is defined as a number, javascript iterator
					// turns it to string, thus conversion is required here
					return parseInt(op)
				}
			  }
			}
		}
		// operators
		var op = iterate(parser.OPERATORS)
		if (op !== undefined) return op
		// booleans
		op = iterate(parser.BOOLEANS)
		if (op !== undefined) return op
		// if we find something else, return as plain
		return x
	}

	parser._parse = function(l, operators, unary_operator) {
		// http://stackoverflow.com/questions/42032418/group-operands-by-logical-connective-precedence-from-python-list
		// one item on list
		if (l.length == 1) {
			// if not negation or other operators
			if (l[0] != unary_operator && !(l[0] in operators)) {
				// substitute literals back to original content if available
				if (typeof(l[0]) !== parser.list_type && l[0] in parser.literals) {
					l[0] = parser.literals[l[0]]
					// finally replace escaped content with content
					for (var w in parser.wrappers) {
						if (parser.wrappers.hasOwnProperty(w)) {
							var c = parser.wrappers[w]
							l[0] = l[0].replace(new RegExp(RegExp.escape("\\"+c), 'g'), c)
						}
					}
				}
				// return data
				return l[0]
			} else {
				// return constants
				return l[0]
			}
		}
		// we are looping over all binary operators in order
		if (operators.length > 0) {
			var operator = operators[0]
			// for left-associativity of binary operators
			var position = l.slice(0).reverse().indexOf(operator)
			if (position > -1) {
				position = l.length - 1 - position
				return [parser._parse(l.slice(0, position), operators, unary_operator), operator, parser._parse(l.slice(position+1, l.operators), operators.slice(1, l.operators), unary_operator)]
			} else {
				 return parser._parse(l, operators.slice(1, l.operators), unary_operator)
			}
		}
		// return data with not operator
		return [unary_operator, parser._parse(l.slice(1, l.length), operators, unary_operator)]
	}


	parser.recursiveParenthesesGroups = function() {

		function add(lastlast) {
			// append operator between all operands
			var op = lastlast[0]
			var a = lastlast.slice(1, lastlast.length)
			var b = []
			var negate = false
			for (var idx in a) {
				if (a.hasOwnProperty(idx)) {
					if (a[idx] === parser.NOT_OPERATOR) {
						negate = !negate
					} else {
						if (negate) {
							b = b.concat([parser.NOT_OPERATOR, a[idx], op])
						} else {
							b = b.concat([a[idx], op])
						}
						negate = false
					}
				}
			}
			return b
		}

		function rem(lastlast) {
			// remove redundant negation marks
			var b = []
			var negate = false
			for (var idx in lastlast) {
				if (lastlast.hasOwnProperty(idx)) {
					if (lastlast[idx] === parser.NOT_OPERATOR) {
						negate = !negate
					} else {
						if (negate)
							b = b.concat([parser.NOT_OPERATOR, lastlast[idx]])
						else
							b.push(lastlast[idx])
						negate = false
					}
				}
			}
			return b
		}

		function rec(a) {
			// http://stackoverflow.com/questions/17140850/how-to-parse-a-string-and-return-a-nested-array/17141899#17141899
			var stack = [[]]
			for (var idx in a) {
				var x = a[idx]
				if (x == parser.OPEN_PARENTHESES) {
					stack.last().push([])
					stack.push(stack.last().last())
				} else if (x == parser.CLOSE_PARENTHESES) {
					stack.pop()
					// special treatment for prefix notation
					// (^ a b c) => (a ^ b ^ c) => (a ^ (b ^ c))
					// (& a b c) => (a & b & c) => (a & (b & c))
					// (| a b c) => (a | b | c) => (a | (b | c))
					// also (& a b (| c d)) => (a & b) & (c | d) is possible!
					var last = stack.last()
					var lastlast = last.last()
					last[last.length-1] = lastlast = rem(lastlast)
					// items that has less than three items are special
					// and need no parsing
					if (lastlast.length > 2) {
						// for prefix notation support parse lines that 
						// start with any other operator than negation
						if (typeof(lastlast[0]) === parser.int_type && 
					   		lastlast[0] in parser.OPERATORS && 
					   		lastlast[0] != parser.NOT_OPERATOR) {
							var b = add(lastlast)
							lastlast = b.slice(0, b.length-1)
						} else {
							// else see if remaining content has more than one
							// operator and make them nested set in that case
							// last operator predecende is usually NOT_OPERATOR
							// but can be configured at the object initialization
						}
						last[last.length-1] = parser._parse(lastlast,
							parser.operator_precedence.slice(0, parser.operator_precedence.length-1), 
							parser.operator_precedence.last())
					}
				} else {
					stack.last().push(x)
				}
			}
			return stack.pop()
		}
		// remove whitespace from literal string (!=input string at this point already)
		var a = this.literal_string.split(" ").filter(function(e){ return e === 0 || e })
		// artificially add parentheses if not provided
		a = [this.OPEN_PARENTHESES].concat(a, [this.CLOSE_PARENTHESES])
		// substitute different operators by numeral representatives
		a = a.map(this.substitute)
		// loop over the list of literals placeholders and operators and parentheses
		return rec(a)
	}

	parser.parse = function(input_string) {
		// main method to parse the string
		this.setLiterals(input_string.trim())
		// recursively construct the structure
		var o = this.recursiveParenthesesGroups()[0]
		if (typeof(o) !== parser.list_type) {
			o = [o]
		}
		return o
	}

	parser.validate = function validate(input_string, open, close, wrappers, escape_char) {
		// check parentheses and wrappers characters that they match
		// for example (, [, {
		open = open || parser.OPEN_PARENTHESES
		// for example: }, ], )
		close = close || parser.CLOSE_PARENTHESES
		// multiple wrapper chars accepted, for example ['"', "'", "´"]
		wrappers = wrappers || parser.wrappers
		// is is possible to pass a different escape char, but it is probably
		// not a good idea because many of the string processors use the same
		escape_char = escape_char || '\\'

		var stack = []
		var current, last, previous
		// loop over all characters in a string
		for (var i = 0, l = input_string.length; i < l; i++) {
			// current character
			current = input_string.substring(i, i+1)
			// if previous character was escape character, then 
			// swap it with the current one and continue to the next char
			if (previous == escape_char) {
				// see if current character is escape char, then there are
				// two of them in row and we should reset previous marker
				if (current == escape_char) previous = null
				else previous = current
				continue
			}
			// last stacked char. not that this differs from the previous value which
			// is the previous char from string. last is the last char from stack
			last = stack.last()
			// if we are inside a wrapper accept ANY character 
			// until the next unescaped wrapper char occurs
			if (wrappers.indexOf(last) > -1 && current != last) {
				// swap the current so that we can escape wrapper inside wrappers: "\""
				previous = current
				continue
			}
			// push open parenthesis or wrapper to the stack
			if (current == open ||
				(wrappers.indexOf(current) > -1 && current != last)) {
				stack.push(current)
			// prepare to pop last parenthesis or wrapper
			} else if (current == close || 
					   wrappers.indexOf(current) > -1) {
				// if there is nothing on stack, should already return false
				if (stack.length == 0) {
					return false
				} else {
					// if we encounter wrapper char take the last wrapper char out from stack
					if ((wrappers.indexOf(last) > -1) ||
						// or if the last char was open and current close parenthsis
						(last == open && current == close)) {
						stack.pop()
					} else {
						return false
					}
				}
			}
			// update previous char
			previous = current
		}
		// if there is something on stack then no closing char was found
		return stack.length == 0
	}

	parser.deformat = function(lst, operator_type) {

		function rec(current_item, operator_type, first) {
			// if item is not a list, return value
			if (typeof(current_item) !== parser.list_type) {
				// boolean values
				for (var op in parser.BOOLEANS)
				  if (parser.BOOLEANS.hasOwnProperty(op))
					if (parseInt(op) === current_item)
						return parser.BOOLEANS[op][operator_type]
				// normal items wrapped by the first configured wrapper char
				// escaping wrapper char inside the string
				var r = new RegExp(parser.wrappers[0], 'g')
				current_item = current_item.replace(r, '\\'+parser.wrappers[0])
				return parser.wrappers[0]+current_item+parser.wrappers[0]
			}
			// init first variable. this prevent to add wrap clause
			// with unnecessary double parentheses
			if (first == undefined) {
				first = true
			}
			// item is a list
			var a = []
			// open clause
			if (!first) {
				a.push(parser.OPEN_PARENTHESES)
			}
			// loop all items
			for (var i=0, l = current_item.length; i<l; ++i) {
				var item = current_item[i]
				// operators
				if (typeof(item) != parser.list_type && item in parser.OPERATORS) {
					a.push(parser.OPERATORS[item][operator_type])
				// item or list
				} else {
					// recursively add next items
					a.push(rec(item, operator_type, false))
				}
			}
			// close clause
			if (!first) {
				a.push(parser.CLOSE_PARENTHESES)
			}
			return a.join(' ')
		}
		// default operator type is word, others are char and math
		operator_type = operator_type || 'word'
		// call sub routine to deformat structure
		return rec(lst, operator_type)
	}

	parser.evaluate = function(input, table) {
		// if input is string, parse it first
		if (typeof(input) == parser.string_type) {
			input = this.parse(input)
		}
		// assume input is well formed so that we can calculate the truth value
		if (typeof(input) == parser.list_type && input.length) {
			return this.truthValue(input, table)
		}
		return null
	}

	parser.truthValue = function(current_item, table) {
		// if item is not a list, check the truth value
		if (typeof(current_item) !== parser.list_type) {
			// truth table is possibly given
			if (table && current_item in table) {
				current_item = table[current_item]
			}
			// force item to string and lowercase for simpler comparison
			return (parser.TRUES.indexOf(current_item+"".toLowerCase()) > -1)
		}
		// item is a list
		var a = []
		var negate = true
		// default operator
		var operator = parser.AND_OPERATOR
		for (var c in current_item) {
			if (current_item.hasOwnProperty(c)) {
				var item = current_item[c]
				// operators
				if (typeof(item) !== parser.list_type && item in parser.OPERATORS) {
					if (item === parser.NOT_OPERATOR) {
						negate = false
					} else {
						operator = item
					}
				} else {
					a.push(parser.truthValue(item, table))
				}
			}
		}
		// all operators have a function to check the truth value
		// we must compare returned boolean against negate parameter
		return parser.OPERATORS[operator]['func'](a) == negate
	}
	
	parser.schema = function(input, table) {
		// if input is string, parse it first
		if (typeof(input) == parser.string_type) {
			input = this.parse(input)[0]
		}
		// assume input is well formed so that we can calculate the truth value
		if (typeof(input) == parser.list_type) {
			return this.buildJsonSchema(input[1], !input[0], table, false, false, false)
		}
		return None
	}

	parser.buildJsonSchema = function(current_item, mutual, table, negate, xor, xand) {
		// if item is not a list, check the truth value
		if (typeof(current_item) !== parser.list_type) {
			// see if translation table is given
			if (table && current_item in table)
				current_item = table[current_item]
			if (negate) {
				return '{"not": {"pattern": "'+current_item+'"}}'
			}
			return '{"pattern": "'+current_item+'"}'
		}
		// item is a list
		var a = []
		// should we negate next item, was it a list or values
		var next_item_negate = false, next_item_xor = false, next_item_xand = false
		var i, len = current_item.length
		for (i=0; i<len; ++i) {
			var item = current_item[i]
			// negation marker
			if (item == -1) {
				next_item_negate = true
			// xor marker
			} else if (item == -2) {
				next_item_xor = true
			// xand marker
			} else if (item == -3) {
				next_item_xand = true
			} else {
				a.push(this.buildJsonSchema(item, !mutual, table, next_item_negate, next_item_xor, next_item_xand))
				// reset negation and xor
				next_item_negate = false
				next_item_xor = false
				next_item_xand = false
			}
		}
	
		// is group AND / OR / XOR
		// take care of negation for the list result too
		if (xor) {
			// if only one of the values is true, but not more
			if (negate) {
				return '{"not": {"oneOf": [' + a + ']}}'
			}
			return '{"oneOf": [' + a + ']}'
		} else if (xand) {
			// if any of the values is true, but not all
			if (negate) {
				return '{"not": {"allOf": [{"anyOf": [' + a + ']},{"not": {"allOf": [' + a + ']}}]}}'
			}
			return '{"allOf": [{"anyOf": [' + a + ']},{"not": {"allOf": [' + a + ']}}]}'
		} else if (mutual) {
			// if all values are true
			if (negate) {
				return '{"not": {"allOf": [' + a + ']}}'
			}
			return '{"allOf": [' + a + ']}'
		} else {
			// if some of the values is true
			if (negate) {
				return '{"not": {"anyOf": [' + a + ']}}'
			}
			return '{"anyOf": [' + a + ']}'
		}
	}
	// return parser dictionary with all methods and variables
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

PLCParser.evaluateInput = function(input, table) {
	var c = PLCParser()
	try {
		return c.evaluate(input, table)
	} catch(err) {
		return null
	}
}

PLCParser.deformatInput = function(a, operator_type) {
	var c = PLCParser()
	try {
		return c.deformat(a, operator_type)
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

PLCParser.jsonSchema = function(input, table) {
	var c = PLCParser()
	try {
		return c.schema(input, table)
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
