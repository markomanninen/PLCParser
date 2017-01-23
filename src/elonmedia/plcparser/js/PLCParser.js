"use strict";

function PLCParser(parentheses, wrappers) {

	var parser = parser || {}
  
	parser.OPEN_PARENTHESES = null
	if (parentheses && parentheses[0]) parser.OPEN_PARENTHESES = parentheses[0]
	else parser.OPEN_PARENTHESES = '('
	parser.CLOSE_PARENTHESES = null
	if (parentheses && parentheses[1]) parser.CLOSE_PARENTHESES = parentheses[1]
	else parser.CLOSE_PARENTHESES = ')'
	//http://stackoverflow.com/questions/430759/regex-for-managing-escaped-characters-for-items-like-string-literals  
	parser.wrappers = wrappers || ["'", '"']

	var re = new RegExp("a|b", "i")
	/*var STRING_LITERALS = re.compile('|'.join([r"%s[^%s\\]*(?:\\.[^%s\\]*)*%s" % 
									 (w,w,w,w) for w in self.wrappers]))
	*/
  parser.mutual = null
  parser.input_string = ""
  parser.literals = {}
  
	parser.setLiterals = function(input_string) {

	}

	parser.recursiveParenthesesGroups = function(i, level) {
		var i = i || 0
		var level = level || 0
    console.log(this.input_string)
	}

	parser.parse = function(input_string) {
		//""" main method """
		this.input_string = input_string.trim()
		this.setLiterals(input_string)
		return this.recursiveParenthesesGroups()
	}

	parser.deFormat = function(lst, short, firstOnly) {
		return null
	}

	parser.evaluate = function(input, table) {
		if (typeof(input) == typeof("")) {
			input = this.parse(input)
		}
		if (typeof(input) == typeof([])) {

		}
		return null
	}

	return parser
}

PLCParser.parseInput = function(input_string) {
	//""" bypass object construct """
	var c = PLCParser()
	try {
		return c.parse(input_string)
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
