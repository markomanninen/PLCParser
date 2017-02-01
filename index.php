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
						<div class="checkbox">
							<label><input <?=($firstonly?'checked="checked" ':'')?> type="checkbox" name="firstonly" value="1" />First only</label>
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
				  <a id="Python"></a>
				  <div class="panel-heading">Python</div>
				  <div class="panel-body">See Python version and similar examples from <a href="http://nbviewer.jupyter.org/github/markomanninen/PLCParser/blob/master/Prepositional%20Logic%20Clause%20Parser%20%28PLCParser%29.ipynb">Jupyter Notebook</a> project.</div>
			</div>
	  </div>

		<a id="Truth-tables"></a>
		<div class="page-header">
			<h3>Truth tables</h3>
		</div>
		
		<div class="col-md-12">
	<h3>Unary</h3>
	<p>An operation with only one operand, i.e. a single input. IS is tautology, not really needed for the practical purposes.</p>
</div>

<div class="table-responsive col-md-5">

<table class="table table-bordered">
<caption>IS</caption>
<thead>
  <tr><th>A</th><th>is (A) = (is A) = (A) = </th></tr>
</thead>
<tbody>
  <tr><td>1</td><td class="success">true</td></tr>
  <tr><td>0</td><td class="danger">false</td></tr>
</tbody>
</table>
  
</div>

<div class="table-responsive col-md-5">

<table class="table table-bordered">
<caption>NOT</caption>
<thead>
  <tr><th>A</th><th>is not (A) = (is not A) = not (A) = (not A) = </th></tr>
</thead>
<tbody>
  <tr><td>1</td><td class="danger">false</td></tr>
  <tr><td>0</td><td class="success">true</td></tr>
</tbody>
</table>
  
</div>

<div class="col-md-12">
	<h3>Binary</h3>
  
	<p>Calculates an operation of a set that combines two elements / inputs.</p>
  
	<p>Complementary operations (NAND, NOR, XNOR) are handled with NOT operator.</p>
</div>

<div class="table-responsive col-md-4">

<table class="table table-bordered">
<caption>OR</caption>
<thead>
  <tr><th>A</th><th>B</th><th>(A or B) = </th></tr>
</thead>
<tbody>
  <tr><td>1</td><td>0</td><td class="success">true</td></tr>
  <tr><td>1</td><td>1</td><td class="success">true</td></tr>
  <tr><td>0</td><td>1</td><td class="success">true</td></tr>
  <tr><td>0</td><td>0</td><td class="danger">false</td></tr>
</tbody>
</table>
  
</div>
  
<div class="table-responsive col-md-4">

<table class="table table-bordered">
<caption>NOR</caption>
<thead><tr><th>A</th><th>B</th><th>not (A or B) = </th></tr></thead>
<tbody>
  <tr><td>1</td><td>0</td><td class="danger">false</td></tr>
  <tr><td>1</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>0</td><td class="success">true</td></tr>
</tbody>
</table>

</div>
  
<div class="table-responsive col-md-4">

<table class="table table-bordered">
<caption>AND</caption>
<thead><tr><th>A</th><th>B</th><th>(A and B) = </th></tr></thead>
<tbody>
  <tr><td>1</td><td>0</td><td class="danger">false</td></tr>
  <tr><td>1</td><td>1</td><td class="success">true</td></tr>
  <tr><td>0</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>0</td><td class="danger">false</td></tr>
</tbody>
</table>

</div>
  
<div class="table-responsive col-md-4">

<table class="table table-bordered">
<caption>NAND</caption>
<thead><tr><th>A</th><th>B</th><th>not (A and B) = </th></tr></thead>
<tbody>
  <tr><td>1</td><td>0</td><td class="success">true</td></tr>
  <tr><td>1</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>1</td><td class="success">true</td></tr>
  <tr><td>0</td><td>0</td><td class="success">true</td></tr>
</tbody>
</table>

</div>

<div class="table-responsive col-md-4">

<table class="table table-bordered">
<caption>XOR</caption>
<thead><tr><th>A</th><th>B</th><th>xor (A B) = </th></tr></thead>
<tbody>
  <tr><td>1</td><td>0</td><td class="success">true</td></tr>
  <tr><td>1</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>1</td><td class="success">true</td></tr>
  <tr><td>0</td><td>0</td><td class="danger">false</td></tr>
</tbody>
</table>

</div>
  
<div class="table-responsive col-md-4">

<table class="table table-bordered">
<caption>XNOR</caption>
<thead><tr><th>A</th><th>B</th><th>not xor (A B) = </th></tr></thead>
<tbody>
  <tr><td>1</td><td>0</td><td class="danger">false</td></tr>
  <tr><td>1</td><td>1</td><td class="success">true</td></tr>
  <tr><td>0</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>0</td><td class="success">true</td></tr>
</tbody>
</table>

</div>
  
<div class="col-md-12">
  <h3>Multiary</h3>
  
  <p>Ternary and other n-ary operations are handled with XOR and XAND operators. 
	Special attention is required here. XOR is taken as "if one and only one" of the set is true. 
	Thus operation can be done to a group of items, not only for two operands. 
	In logic circuits ternary input is sometimes used and it is even more common operation in programming languages.</p>
  
  <p>XAND is debated and sometimes thought to be a synonym for XNOR, but here it is an extension of XOR and means 
	"if one or more, but not all" of the set is true.</p>
  
  <p>Complements XNOR and XNAND are again handled with not operator.</p>
  
</div>
  
<div class="table-responsive col-md-4">

<table class="table table-bordered">
<caption>XOR</caption>
<thead><tr><th>A</th><th>B</th><th>C</th><th>xor (A B C) = </th></tr></thead>
<tbody>
  <tr><td>1</td><td>0</td><td>0</td><td class="success">true</td></tr>
  <tr><td>0</td><td>1</td><td>0</td><td class="success">true</td></tr>
  <tr><td>0</td><td>0</td><td>1</td><td class="success">true</td></tr>
  <tr><td>1</td><td>1</td><td>0</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>1</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>1</td><td>0</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>1</td><td>1</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>0</td><td>0</td><td class="danger">false</td></tr>
</tbody>
</table>

</div>
  
<div class="table-responsive col-md-4">

<table class="table table-bordered">
<caption>XNOR</caption>
<thead><tr><th>A</th><th>B</th><th>C</th><th>not xor (A B) = </th></tr></thead>
<tbody>
  <tr><td>1</td><td>0</td><td>0</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>1</td><td>0</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>0</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>1</td><td>1</td><td>0</td><td class="success">true</td></tr>
  <tr><td>0</td><td>1</td><td>1</td><td class="success">true</td></tr>
  <tr><td>1</td><td>0</td><td>1</td><td class="success">true</td></tr>
  <tr><td>1</td><td>1</td><td>1</td><td class="success">true</td></tr>
  <tr><td>0</td><td>0</td><td>0</td><td class="success">true</td></tr>
</tbody>
</table>

</div>
  
	
<div class="table-responsive col-md-4">

<table class="table table-bordered">
<caption>XAND</caption>
<thead><tr><th>A</th><th>B</th><th>C</th><th>xand (A B C) = </th></tr></thead>
<tbody>
  <tr><td>1</td><td>0</td><td>0</td><td class="success">true</td></tr>
  <tr><td>0</td><td>1</td><td>0</td><td class="success">true</td></tr>
  <tr><td>0</td><td>0</td><td>1</td><td class="success">true</td></tr>
  <tr><td>1</td><td>1</td><td>0</td><td class="success">true</td></tr>
  <tr><td>0</td><td>1</td><td>1</td><td class="success">true</td></tr>
  <tr><td>1</td><td>0</td><td>1</td><td class="success">true</td></tr>
  <tr><td>1</td><td>1</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>0</td><td>0</td><td class="danger">false</td></tr>
</tbody>
</table>

</div>
  
<div class="table-responsive col-md-4">

<table class="table table-bordered">
<caption>XNAND</caption>
<thead><tr><th>A</th><th>B</th><th>C</th><th>not xand (A B) = </th></tr></thead>
<tbody>
  <tr><td>1</td><td>0</td><td>0</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>1</td><td>0</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>0</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>1</td><td>1</td><td>0</td><td class="danger">false</td></tr>
  <tr><td>0</td><td>1</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>1</td><td>0</td><td>1</td><td class="danger">false</td></tr>
  <tr><td>1</td><td>1</td><td>1</td><td class="success">true</td></tr>
  <tr><td>0</td><td>0</td><td>0</td><td class="success">true</td></tr>
</tbody>
</table>

</div>

				</div><!-- col -->
			</div><!-- row -->
		</div><!-- container -->
	</body>
</html>
