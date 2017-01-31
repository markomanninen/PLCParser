<?php

define('SERVER_ROOT', dirname($_SERVER['SCRIPT_NAME']).'/');

include __DIR__.'/src/elonmedia/plcparser/php/bootstrap.php';

#$default = '(A (!(B or C)))';
$default_input = '(1 ∧ (0 1) ⊕ (1 ¬ 1))';
$default_table_str = '{
	"T": 1, "t": "1", "true": true, "TRUE": "true",
	"F": 0, "f": "0", "false": false, "FALSE": "false"
}';
#$default = '("\"")';
#$default = '("\\\\")';

$input = isset($_POST['input']) ? $_POST['input'] : $default_input;
$firstonly = isset($_POST['firstonly']);
$type = isset($_POST['type']) ? $_POST['type'] : 1;
$table_str = isset($_POST['table']) ? $_POST['table'] : $default_table_str;
# array format with TRUE
$table = json_decode($table_str, TRUE);

$v = PLCParser::validateInput($input);
$p = PLCParser::parseInput($input);
$d = PLCParser::deformatInput($p, $type==2, $firstonly, $type==3);
$e = PLCParser::evaluateInput($p, $table);

?>
<!DOCTYPE html>
<html>
	<head lang="en">
		<meta charset="UTF-8">
		<title>PLCParser - Prepositional Logic Clause Parser (Python, PHP, Javascript)</title>
		<link rel="stylesheet" href="./src/elonmedia/plcparser/css/style.css" />
		<link rel="stylesheet" href="./bower_components/bootstrap/dist/css/bootstrap.min.css" />
		<script src="./bower_components/jquery/dist/jquery.min.js"></script>
		<script src="./bower_components/jquery-ui/jquery-ui.min.js"></script>
		<script src="./bower_components/bootstrap/dist/css/bootstrap.min.js"></script>
		
		<script src="./bower_components/MathJax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>

		<script src="./dist/PLCParser.min.js"></script>
		<!-- jquery -->
		<script>
			
			Array.prototype.last = function () {
			  return this[this.length - 1]
			}

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

			function deformat(s, short, first, latex) {
				return PLCParser.deformatInput(PLCParser.parseInput(s), short, first, latex)
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
					var s = deformat(v, 
							$('.radio input:checked').val() == "2", 
							$('#firstonly').prop("checked"),
							$('.radio input:checked').val() == "3")
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
						var isFirstOnly = $('#firstonly').prop("checked")
						var isShort = $('.radio input:checked').val() == "2"
						var isMath = $('.radio input:checked').val() == "3"
						var formatted_input = deformat(input, isShort, isFirstOnly, isMath)
						var truth_table = JSON.parse($('#table').text())
						var boolean = ""+evaluate(input, truth_table) || 'false'
						
						expression = formatted_input + " = " + boolean
						// if expression is new parse object and update expression
						if (previous_expression != expression) {
							$('#object').text(JSON.stringify(PLCParser.parseInput(formatted_input)))
							log(expression)
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
				$( ".radio input" ).on('change', change_deformat);
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
		</script>
		<style>
		body {
			font-size: 160%;
		}
		table.table td {
			font-size: 120%;
			font-family: courier;
		}
		input#input {
			font-size: 145%;
			height: 45px;
		}
		</style>
	</head>
	<body>
		<div class="container">

		<div class="page-header">
			<h1>PLCParser</h1>
			<h4>Prepositional Logic Clause Parser for Javascript, PHP &amp; Python</h4>
		</div>

		<div>
			<h3>...</h3>
		</div>

		<div class="form-group">
			<p>Symbols that can be used for propositional logic clauses:</p>
			<div class="table-responsive">
			<table class="table">
			<tr>
				<th></th>
				<th>Negation</th>
				<th>Conjunction</th>
				<th>Exclusive or</th>
				<th>Exclusive and</th>
				<th>Disconjunction</th>
				<th>Boolean</th>
			</tr>
			<tr>
				<th>Word</th>
				<td>not</td>
				<td>and</td>
				<td>xor</td>
				<td>xand</td>
				<td>or</td>
				<td>true / false</td>
			</tr>
			<tr>
				<th>Char</th>
				<td>!</td>
				<td>&amp;</td>
				<td>^</td>
				<td>+</td>
				<td>|</td>
				<td>1 / 0</td>
			</tr>
			<tr>
				<th>Math</th>
				<td>$\lnot$</td>
				<td>$\land$</td>
				<td>$\oplus$</td>
				<td>$\ominus$</td>
				<td>$\lor$</td>
				<td></td>
			</tr>
			</table>
			</div>
			<!--
			<ul class="list-group">
			<li class="list-group-item">and , &amp; , ∧</li>
			<li class="list-group-item">or , | , ∨</li>
			<li class="list-group-item">not , ! , ¬</li>
			<li class="list-group-item">xor , ^ , ⊕</li>
			<li class="list-group-item">1 , true</li>
			<li class="list-group-item">0 , false</li>
			</ul>
			-->
		</div>

		<div class="panel-group">
				<div class="panel panel-primary">
				  <div class="panel-heading">Javascript</div>
				  <div class="panel-body">
				  </div>
				  <div class="panel-body">
					<div class="form-group">
						<label for="input">Input:</label>
						<input type="text" id="input" value="<?=htmlentities($input)?>" class="form-control" />
					</div>

					<div class="form-group">
						<label for="table">Configuration:</label>
						<textarea id="table" class="form-control" rows="5"><?=$table_str?></textarea>
					</div>

					<div class="controls-row">
						<label class="radio-inline">
							<input <?=($type==1?'checked="checked" ':'')?> name="type" type="radio" id="type1" value="1">Word</label>
						<label class="radio-inline">
							<input <?=($type==2?'checked="checked" ':'')?> name="type" type="radio" id="type2" value="2">Char</label>
						<label class="radio-inline">
							<input <?=($type==3?'checked="checked" ':'')?> name="type" type="radio" id="type3" value="3">Math</label>
					</div>

					<div class="form-group">
						<div class="checkbox">
							<label><input <?=($firstonly?'checked="checked" ':'')?> type="checkbox" id="firstonly" value="1">First only</label>
						</div>
					</div>

					<div>
						<label>Formatted and evaluated expression:</label>
					</div>

					<div>
						<pre id="result"></pre>
						<!--<div id="mathjax">asdf</div>-->
					</div>

					<div>
						<label>JSON object:</label>
					</div>

					<pre id="object"></pre>
					
					<div>
						<label>JSON validation schema:</label>
					</div>

					<pre id="schema"></pre>
					<!--<div>$ \left( \left( 1 \land \left( 0 \lor \lnot 0 \right)  \land \oplus \left( 1 \lor 0 \right)  \right)  \right) = True $</div>-->
				  </div>
			</div>
			<div class="panel panel-primary">
				  
				  <div class="panel-heading" id="php-version">PHP</div>
				  
				  <div class="panel-body">
				  
				  <form method="POST" action="./#php-version">

				  	<div class="form-group">
						<label for="input">Input:</label>
						<input type="text" id="input" name="input" value="<?=htmlentities($input)?>" class="form-control" />
					</div>

					<div class="form-group">
						<label for="table">Configuration:</label>
						<textarea id="table" class="form-control" name="table" rows="5"><?=$table_str?></textarea>
					</div>

					<div class="controls-row">
							<label class="radio-inline control-label">
								<input <?=($type==1?'checked="checked" ':'')?> name="type" type="radio" id="type1" value="1">Word</label>
						
							<label class="radio-inline control-label">
								<input <?=($type==2?'checked="checked" ':'')?> name="type" type="radio" id="type2" value="2">Char</label>
						
							<label class="radio-inline control-label">
								<input <?=($type==3?'checked="checked" ':'')?> name="type" type="radio" id="type3" value="3">Math</label>
					</div>

					<div class="form-group">
						<div class="checkbox">
							<label><input <?=($firstonly?'checked="checked" ':'')?> type="checkbox" id="firstonly" name="firstonly" value="1" />First only</label>
						</div>
					</div>
				
					<div class="form-group">
						<input type="submit" value="submit" class="btn btn-primary"/>
					</div>

					</form>

					<p>Validated input: <b><?=$v?'true':'false'?></b></p>
					<p>Evaluated input: <?="$d = ".($e?'true':'false')?></p>
					<p>Parsed input:</p>
					<pre><?=dump($p)?></pre>
				  </div>
			</div>
				<div class="panel panel-primary">
				  <div class="panel-heading">Python</div>
				  <div class="panel-body">...</div>
			</div>
	  </div>

		</div><!-- container -->
	</body>
</html>
