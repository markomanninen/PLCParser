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
        <link rel="stylesheet" href="./src/elonmedia/plcparser/css/style.css">
    </head>
    <body>
    	<p><?=$i?> -> </p>
    	<pre><?=print_r($o, 1)?></pre>
    </body>
</html>
