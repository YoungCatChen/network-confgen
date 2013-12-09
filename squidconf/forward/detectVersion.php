<?php

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler('exception_error_handler');


try {
	exec('$SQUID -v', &$output, &$returnval);
	
	if ($returnval != 0)
		throw new Exception('detect squid version failed.');
	if (!isset($output[0]))
		throw new Exception('"squid -v" showed unrecognized results.');

	preg_match_all('/(\d+)\./', $output[0], $result, PREG_PATTERN_ORDER);
	$result = $result[1];

	if (!isset($result[1]))
		throw new Exception('"squid -v" showed unrecognized results.');
	
	echo $result[0], ' ', $result[1], "\n";
	exit(0);
} catch (Exception $ex) {
	fwrite(STDERR, basename(__FILE__) . ': ' . $ex->getMessage() . "\n");
	exit(1);
}
