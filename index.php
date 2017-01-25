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

			function evaluate(s) {
				return PLCParser.evaluateInput(s)
			}

			function deformat(s) {
				return PLCParser.deformat(PLCParser.parseInput(s))
			}

			function log(s) {
				$('#result').html(s);
			}

			function keyup() {
				var color = "white"
				var v = $(this).val()
				var o = validate(v)
				if (!o) {
					color = 'pink'
					log(v + " = " + 'undefined')
				} else {
					var s = deformat(v)
					var e = evaluate(v)
					log(s + " = " + e || 'false')
				}
				$(this).css('background-color', color)
			}

			// Shorthand for $( document ).ready()
			$(function() {
			    $( "#input" ).on('keyup', keyup);
			    $( "#input" ).trigger('keyup');
			});

        </script>
    </head>
    <body>
    	<h3>Prepositional logic clause</h3>
    	<h4>Javascript</h4>
		<p><textarea id="input" cols="40" rows="4">(1 1 (1 0))</textarea></p>
		<pre id="result"></pre>
		<h4>PHP</h4>
    	<p><?=$i?> -> </p>
    	<pre><?=print_r($o, 1)?></pre>
    </body>
</html>
