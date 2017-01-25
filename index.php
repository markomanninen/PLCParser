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
        <script src="./dist/PLCParser.min.js"></script>
        <!-- jquery -->
        <script>
        	
        	Array.prototype.last = function () {
			  return this[this.length - 1];
			};

			function validate(s) {
			        var result = true
			        var stack = []
			        var current, previous, prev, i, len=s.length;
			        for (i = 0; i < len; i++) {
			            current = s.substring(i, i+1)
			            if (prev == '\\') {
			            	prev = current
			            	continue;
			             }
			            previous = stack.last()
			            if (
			            ((current == '(' || current == '[' || current == '{') && previous != "'" && previous != "'" ) ||
			                ((current == "'" && previous != "'" && previous != '\\')) ||
			                 (current == '"' && previous != '"' && previous != '\\')) {
			                stack.push(current);
			            } else if(
			            ((current == ')' || current == ']' || current == '}' ) && previous != "'" && previous != '"') || 
			             								  current == "'" ||
			             								  current == '"') {
			                if (stack.length == 0) {
			                    result = false
			                } else {
			                    if((current == ')' && previous == '(') ||
			                       (current == ']' && previous == '[') ||
			                       (current == '}' && previous == '{') ||
			                       (current == '"' && previous == '"') ||
			                       (current == "'" && previous == "'")) {
			                        stack.pop()
			                    } else {
			                        result = false
			                    }
			                }
			            }
			            prev = current
			        }
			        if (stack.length>0) result = false;
			        return result
			    }

			function evaluate(s, table) {
				return PLCParser.evaluateInput(s, table)
			}

			function deformat(s, short, first) {
				return PLCParser.deformat(PLCParser.parseInput(s), short, first)
			}

			function log(s) {
				$('#result').html(s);
			}

			function change_deformat() {
				var v = $('#input').val()
				var o = validate(v)
				if (!o) {
					log(v + " = " + 'undefined')
				} else {
					var s = deformat(v, 
							$('#short').prop("checked"), 
							$('#firstonly').prop("checked"))
					var t = JSON.parse($('#table').text())
					var e = evaluate(v, t)
					log(s + " = " + e || 'false')
				}
			}

			function keyup() {
				var color = "white"
				var v = $(this).val()
				var o = validate(v)
				if (!o) {
					color = 'pink'
					log(v + " = " + 'undefined')
				} else {
					var s = deformat(v, 
							$('#short').prop("checked"), 
							$('#firstonly').prop("checked"))
					var t = JSON.parse($('#table').text())
					var e = evaluate(v, t)
					log(s + " = " + e || 'false')
				}
				$(this).css('background-color', color)
			}

			// Shorthand for $( document ).ready()
			$(function() {
			    $( "#input" ).on('keyup', keyup);
			    $( "#short" ).on('change', change_deformat);
			    $( "#firstonly" ).on('change', change_deformat);
			    $( "#input" ).trigger('keyup');
			});

        </script>
    </head>
    <body>
    	<div class="container">

		<a name="top"></a>

		<div class="page-header">
			<h1>Prepositional logic clause</h1>
		</div>

    	<h2>Javascript</h2>
    	
		<p><input type="text" id="input" value="(1 1 (1 0))" /></p>
		<p><textarea id="table" cols="40" rows="4">
{
	"T": 1, "T": "1",
	"F": false, "f": "false"
}</textarea></p>
		<p>
			Short: <input type="checkbox" id="short" value="1" />
			First only: <input type="checkbox" id="firstonly" value="1" />
		</p>
		<pre id="result"></pre>
		<h4>PHP</h4>
    	<p><?=$i?> -> </p>
    	<pre><?=print_r($o, 1)?></pre>
    	</div>
    </body>
</html>
