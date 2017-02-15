/*
Array.prototype.last = function (c) {
	if (c !== undefined)
		return this[this.length - 1]
	else this[this.length - 1] = c
}
*/

String.prototype.toTitleCase = function () {
  return this.replace(/\w\S*/g, function(txt) {
	return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()
  })
}

function validate(s) {
	return PLCParser.validateInput(s)
}

function evaluate(s, table) {
	return PLCParser.evaluateInput(s, table)
}

function deformat(s, operator_type) {
	return PLCParser.deformatInput(PLCParser.parseInput(s), operator_type)
}

function schema(s, table) {
	return PLCParser.jsonSchema(s, table)
}

function log(s) {
	$('#result').trigger('change')
	$('#result').text(s);
}

function change_deformat() {
	var v = $('#input').val()
	var o = validate(v)
	if (!o) {
		log(v + " = " + 'undefined')
	} else {
		var operator_type = 'word'
		if ($('input[name=type2]:checked').val() == "2")
			operator_type = 'char'
		if ($('input[name=type2]:checked').val() == "3")
			operator_type = 'math'
		var s = deformat(v, operator_type)
		var t = JSON.parse($('#table').text())
		var e = evaluate(v, t)
		log(s + " = " + e || 'false')
	}
}

var last_input = '', previous_expression = '', input_color = ''
var formatted_color = 'lightgreen'

function keyup() {
	//  input value
	var input = $(this).val().trim()
	// if last trimmed input value was different, then continue
	if (input != last_input) {
		// update last input
		last_input = input
		input_color = "white"
		formatted_color = 'lightblue'
		var expression;
		// error on validation
		if (!validate(input)) {
			input_color = 'pink'
			formatted_color = 'pink'
			$('#object').text(JSON.stringify({}))
			expression = input + " = " + 'undefined'
			log(expression)
			previous_expression = expression
		} else {
			var operator_type = 'word'
			if ($('input[name=type2]:checked').val() == "2")
				operator_type = 'char'
			if ($('input[name=type2]:checked').val() == "3")
				operator_type = 'math'
			var formatted_input = deformat(input, operator_type)
			var truth_table = JSON.parse($('#table').text())
			var boolean = ""+evaluate(input, truth_table) || 'false'
			
			expression = formatted_input + " = " + boolean
			// if expression is new parse object and update expression
			if (previous_expression != expression) {
				$('#object').text(JSON.stringify(PLCParser.parseInput(formatted_input)))
				log(expression)
				//$('#schema').text(schema(input, truth_table))
				previous_expression = expression
			}
		}
		// update input color
		$(this).css('background-color', input_color)
	}
}

// Shorthand for $( document ).ready()
$(function() {
	$( "#input" ).on('keyup', keyup);
	$( "input[name=type2]" ).on('change', change_deformat);
	$( "#firstonly" ).on('change', change_deformat);
	$( "#result" ).change(function () {
		  $(this).effect("highlight", {color:formatted_color}, 1000);
	});
	$( "#input" ).trigger('keyup');
});

MathJax.Hub.Config({
	tex2jax: {
		inlineMath: [["$","$"]]
	}
});
/*
var QUEUE = MathJax.Hub.queue
    var mathjax_formula = null
    var $math = $('#mathjax')
    var $input = $('#input')
    
    $math.text('$$'+$input.text()+'$$')
    $math.hide()

    QUEUE.Push(function () {
      mathjax_formula = MathJax.Hub.getAllJax("mathjax")[0]
    })

    window.UpdateMath = function (TeX) {
  $math.hide()
      QUEUE.Push(
          ["resetEquationNumbers", MathJax.InputJax.TeX],
          ["Text", mathjax_formula, "\\displaystyle{" + TeX + "}"]
      )
      $math.show()
    }
  */
