<?php
require dirname(__FILE__).'/../../common.php';

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler('exception_error_handler');


try {
	$argv = $_SERVER['argv'];
	array_shift($argv);
	$configs = getInisSection($argv, 'squidconf/forward');

	foreach ($configs as $key => $value)
		echo "$key = $value\n";
	
	exit(0);
} catch (Exception $ex) {
	fwrite(STDERR, basename(__FILE__) . ': ' . $ex->getMessage() . "\n");
	exit(1);
}
