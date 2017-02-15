<?php

define('SERVER_ROOT', dirname($_SERVER['SCRIPT_NAME']).'/');

include __DIR__.'/src/elonmedia/plcparser/php/bootstrap.php';

#$default = '(A (!(B or C)))';
$default_input = '(1 ∧ (0  ∨ 1) ⊕ (0 ∨ ¬ 1))';
$default_table_str = '{
	"true": true, "1": true, "⊤": true,
	"false": false, "0": false, "⊥": false
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
//$j = PLCParser::jsonSchema($p, $table);

?>
<!DOCTYPE html>
<html>
	<head lang="en">
		<meta charset="UTF-8">
		<title>PLCParser - Propositional Logic Clause Parser (Python, PHP, Javascript)</title>

		<link rel="stylesheet" href="./bower_components/bootstrap/dist/css/bootstrap.min.css" />
		<link rel="stylesheet" href="./src/elonmedia/plcparser/css/style.css" />

		<script src="./bower_components/jquery/dist/jquery.min.js"></script>
		<script src="./bower_components/jquery-ui/jquery-ui.min.js"></script>
		<script src="./bower_components/bootstrap/dist/css/bootstrap.min.js"></script>
		
		<script src="./bower_components/MathJax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>

		<script src="./dist/PLCParser.min.js"></script>
		<script src="./src/elonmedia/plcparser/js/script.js"></script>
		
		<script>
			// Shorthand for $( document ).ready()
			$(function() {

				$('ul.navbar-nav li').each(function() {
					$(this).click(function() {
						$('ul.navbar-nav li').each(function() {
							$(this).removeClass('active');
						});
						$(this).addClass('active');
					});
				});

				// loop all truth tables, their last th, tbody tds
				// add one empty col to th
				// add one col to td that contains onclick trigger
				// to evaluatePHP
				function evaluatePHP(el) {
					// th last
					var input = $(el).innerHTML
					// th1-n: td1-n
					var table = $(el).innerHTML
					var url = '?' + encodeURIComponent('input='+input+'&table='+table)
					document.location.href = url + "#php-version"
				}
			});
		</script>
		<style>
			
		</style>
	</head>
	<body>
		<div class="container-fluid">

		<div class="row">
			<div class="col-sm-3 col-lg-2">
			  <nav class="navbar navbar-inverse navbar-fixed-side">
				<div class="container">
				  <div class="navbar-header">
					<button class="navbar-toggle" data-target=".navbar-collapse" data-toggle="collapse">
					  <span class="sr-only">Toggle navigation</span>
					  <span class="icon-bar"></span>
					  <span class="icon-bar"></span>
					  <span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="./"><img src="https://raw.githubusercontent.com/markomanninen/PLCParser/master/plcparser_icon.png" style="float:left;width:20px; height:20px;margin:0 7px 5px 0">PLCParser</a>
				  </div>
				  <div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
					  <li class="active">
						<a href="./#Operators">Operators</a>
					  </li>
					  <li class="">
						<a href="./#Parsers">Parsers</a>
						<ul>
						<li><a href="./#Javascript">Javascript</a></li>
						<li><a href="./#PHP">PHP</a></li>
						<li><a href="./#Python">Python</a></li>
						</ul>
					  </li>
					  <li class="">
						<a href="./#Truth-tables">Truth tables</a>
					  </li>
					</ul>
				  </div>
				  <div style="color:grey;text-align:center;font-size:.75em">
					<div><br/>The <a href="https://choosealicense.com/licenses/mit/">MIT</a> License</div>
					<div>Copyright © 2017 Marko Manninen</div>
				  </div>
				</div>
			  </nav>
			</div>

			<div class="col-sm-9 col-lg-10">

<a class="github-ribbon" href="https://github.com/markomanninen/PLCParser"><img alt="Fork me on Github" src="https://camo.githubusercontent.com/e7bbb0521b397edbd5fe43e7f760759336b5e05f/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f677265656e5f3030373230302e706e67" /></a>

		<div class="page-header">
			<h1><img src="https://raw.githubusercontent.com/markomanninen/PLCParser/master/plcparser_icon.png" style="width:40px; height:40px;margin:0 10px 7px 0" />PLCParser</h1>
			<h4>Prepositional Logic Clause Parser for Javascript, PHP &amp; Python</h4>
		</div>

		<div class="alert alert-danger">
			<p><span class="glyphicon glyphicon-alert"></span>&nbsp;&nbsp;<b>NOTE</b> : Project is in draft mode. Any functionality can change at any time!</p>
		</div>

		<a id="Operators"></a>
		<div class="page-header" id="Operators">
			<h3>Operators</h3>
		</div>

		<div class="form-group">
			<p>Symbols that can be used for propositional logic clauses:</p>
			<div class="table-responsive">
			<table class="table table-bordered">
			<tr>
				<th></th>
				<th>Boolean</th>
				<th>Negation</th>
				<th>Conjunction</th>
				<th>Exclusive or</th>
				<th>Disjunction</th>
			</tr>
			<tr>
				<th>Word</th>
				<td>true / false</td>
				<td>not</td>
				<td>and</td>
				<td>xor</td>
				<td>or</td>
			</tr>
			<tr>
				<th>Char</th>
				<td>1 / 0</td>
				<td>!</td>
				<td>&amp;</td>
				<td>^</td>
				<td>|</td>
			</tr>
			<tr>
				<th>Math</th>
				<td>$⊤$ / $⊥$</td>
				<td>$\lnot$</td>
				<td>$\land$</td>
				<td>$\oplus$</td>
				<td>$\lor$</td>
			</tr>
			</table>
			</div>
			<div class="table-responsive">
			<table class="table table-bordered">
			<tr>
				<th></th>
				<th>Negation of the conjunction</th>
				<th>Logical (material) biconditional</th>
				<th>Negation of the disjunction</th>
			</tr>
			<tr>
				<th>Word</th>
				<td>xand</td>
				<td>xnor</td>
				<td>nor</td>
			</tr>
			<tr>
				<th>Char</th>
				<td>/</td>
				<td>=</td>
				<td>†</td>
			</tr>
			<tr>
				<th>Math</th>
				<td>$↑$</td>
				<td>$↔$</td>
				<td>$↓$</td>
			</tr>
			</table>
			</div>
		</div>
		
		<a id="Parsers"></a>
		<div class="page-header">
			<h3>Parsers</h3>
		</div>

		<div class="panel-group">
				<div class="panel panel-primary">
				  <a id="Javascript"></a>
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
							<input <?=($type==1?'checked="checked" ':'')?> name="type2" type="radio" id="type1" value="1">Word</label>
						<label class="radio-inline">
							<input <?=($type==2?'checked="checked" ':'')?> name="type2" type="radio" id="type2" value="2">Char</label>
						<label class="radio-inline">
							<input <?=($type==3?'checked="checked" ':'')?> name="type2" type="radio" id="type3" value="3">Math</label>
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
					
					<!--<div>
						<label>JSON validation schema:</label>
					</div>

					<pre id="schema"></pre>
					<div>$ \left( \left( 1 \land \left( 0 \lor \lnot 0 \right)  \land \oplus \left( 1 \lor 0 \right)  \right)  \right) = True $</div>
					-->
				  </div>
			</div>
			<div class="panel panel-primary">
				  <a id="PHP"></a>
				  <div class="panel-heading" id="php-version">PHP</div>
				  
				  <div class="panel-body">
				  
				  <form method="POST" action="./#php-version">

					<div class="form-group">
						<label for="input">Input:</label>
						<input type="text" id="input" name="input" value="<?=htmlentities($input)?>" class="form-control" />
					</div>

					<div class="form-group">
						<label for="table">Configuration:</label>
						<textarea class="form-control" name="table" rows="5"><?=$table_str?></textarea>
					</div>

					<div class="controls-row">
							<label class="radio-inline control-label">
								<input <?=($type==1?'checked="checked" ':'')?> name="type" type="radio" value="1">Word</label>
						
							<label class="radio-inline control-label">
								<input <?=($type==2?'checked="checked" ':'')?> name="type" type="radio" value="2">Char</label>
						
							<label class="radio-inline control-label">
								<input <?=($type==3?'checked="checked" ':'')?> name="type" type="radio" value="3">Math</label>
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
				  <a id="Python"></a>
				  <div class="panel-heading">Python</div>
				  <div class="panel-body">See Python version and similar examples from <a href="http://nbviewer.jupyter.org/github/markomanninen/PLCParser/blob/master/Propositional%20Logic%20Clause%20Parser%20%28PLCParser%29.ipynb">Jupyter Notebook</a> project.</div>
			</div>
	  </div>

				<?php include 'truth_tables.php'; ?>

				</div><!-- col -->
			</div><!-- row -->
		</div><!-- container -->
	</body>
</html>
