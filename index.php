<?php

define('SERVER_ROOT', dirname($_SERVER['SCRIPT_NAME']).'/');

include __DIR__.'/src/elonmedia/plcparser/php/bootstrap.php';

$i = '(A (!(B or C)))';
$o = PLCPArser::parseInput($i);

?>
<!DOCTYPE html>
<html>
	<head lang="en">
		<meta charset="UTF-8">
		<title>PLCParser - Prepositional logic clause parser (Python, PHP, Javascript)</title>
		<link rel="stylesheet" href="./src/elonmedia/plcparser/css/style.css" />
		<link rel="stylesheet" href="./bower_components/bootstrap/dist/css/bootstrap.min.css" />
		<script src="./bower_components/jquery/dist/jquery.min.js"></script>
		<script src="./bower_components/jquery-ui/jquery-ui.min.js"></script>
		<script src="./bower_components/bootstrap/dist/css/bootstrap.min.js"></script>
		<script src="./bower_components/MathJax/MathJax.js?config=TeX-AMS-MML_HTMLorMML&delayStartupUntil=configured"></script>
		<script src="./dist/PLCParser.min.js"></script>
		<!-- jquery -->
		<script>
			
			Array.prototype.last = function () {
			  return this[this.length - 1];
			};

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
				$('#result').html(s);
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

			var last_input = '', prev2 = '', color = ''
			var _color = 'lightgreen'
			function keyup() {
				//  input value
				var v = $(this).val().trim()
				// if last trimmed input value was different, then continue
				if (v != last_input) {
					// update last input
					last_input = v
					color = "white"
					_color = 'lightgreen'
					var r, o = validate(v)
					if (!o) {
						color = 'pink'
						_color = 'pink'
						$('#object').text(JSON.stringify({}))
						r = v + " = " + 'undefined'
						log(r)
						prev2 = r
					} else {
						var s = deformat(v,
								$('.radio input:checked').val() == "2", 
								$('#firstonly').prop("checked"),
								$('.radio input:checked').val() == "3")
						var t = JSON.parse($('#table').text())
						var e = evaluate(v, t)
						r = s + " = " + e || 'false'
						if (prev2 != r) {
							$('#object').text(JSON.stringify(PLCParser.parseInput(s)))
							log(r)
							prev2 = r
						}
					}
					$(this).css('background-color', color)
				}
			}

			// Shorthand for $( document ).ready()
			$(function() {
				$( "#input" ).on('keyup', keyup);
				$( ".radio input" ).on('change', change_deformat);
				$( "#firstonly" ).on('change', change_deformat);
				$( "#result" ).change(function () {
					  $(this).effect("highlight", {color:_color}, 1000);
				});
				$( "#input" ).trigger('keyup');
			});

		</script>
		<style>
		body {
			font-size: 160%;
		}
		</style>
	</head>
	<body>
		<div class="container">

		<div class="page-header">
			<h1>Prepositional logic clause</h1>
		</div>


		<div class="form-group">
			<p>Symbols that can be used for propositional logic clauses:</p>
			<ul class="list-group">
			<li class="list-group-item">and , &amp; , ∧</li>
			<li class="list-group-item">or , | , ∨</li>
			<li class="list-group-item">not , ! , ¬</li>
			<li class="list-group-item">xor , ^ , ⊕</li>
			<li class="list-group-item">1 , true</li>
			<li class="list-group-item">0 , false</li>
			</ul>
		</div>

		<div class="panel-group">
				<div class="panel panel-primary">
				  <div class="panel-heading">Javascript</div>
				  <div class="panel-body">
				  </div>
				  <div class="panel-body">
					<div class="form-group">
						<label for="input">Input:</label>
						<input type="text" id="input" value="(1 1 (1 0))" class="form-control" />
					</div>

					<div class="form-group">
						<label for="table">Configuration:</label>
						<textarea id="table" class="form-control" rows="5">{
	"T": 1, "T": "1",
	"F": false, "f": "false"
}</textarea>
					</div>
					<div class="form-group">
						<div class="radio">
							<label><input checked="checked" name="type" type="radio" id="type1" value="1">Word</label>
						</div>
						<div class="radio">
							<label><input name="type" type="radio" id="type2" value="2">Short</label>
						</div>
						<div class="radio">
							<label><input name="type" type="radio" id="type3" value="3">Math</label>
						</div>
					</div>
					<div class="form-group">
						<div class="checkbox">
							<label><input type="checkbox" id="firstonly" value="1">First only</label>
						</div>
					</div>
					<div>
						<label>Formatted and evaluated expression:</label>
					</div>
					<pre id="result"></pre>
					<div>
						<label>JSON object:</label>
					</div>
					<pre id="object"></pre>
					<div>
						<label>JSON validation schmema:</label>
					</div>
					<pre id="schema"></pre>
					<div>$$ \left( \left( 1 \land \left( 0 \lor \lnot 0 \right)  \land \oplus \left( 1 \lor 0 \right)  \right)  \right) = True $$</div>
				  </div>
			</div>
				<div class="panel panel-primary">
				  <div class="panel-heading">PHP</div>
				  <div class="panel-body">
					<p><?=$i?> -> </p>
					<pre><?=print_r($o, 1)?></pre>
				  </div>
			</div>
				<div class="panel panel-primary">
				  <div class="panel-heading">Python</div>
				  <div class="panel-body">...</div>
			</div>
	  </div>

		</div><!-- container -->
		<script type="text/javascript">
		  MathJax.Hub.Configured()
		</script>
	</body>
</html>
