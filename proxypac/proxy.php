<?php

function proxyFromGet($key) {
	if (!isset($_GET[$key]))
		return 'null';

	$value = $_GET[$key];

	if ($value == 'D' || $value == 'DIRECT')
		return '"DIRECT"';
	elseif (strpos($value, ':') === FALSE)
		return 'P_' . $value;
	else
		return '"PROXY '. $value .'"';
}

if (count($_GET) == 0) {
	header('HTTP/1.0 403 Forbidden');
	echo '<h1>403 Forbidden</h1>';
	exit;
}


chdir(__FILE__ . '.data');
header('Content-type: text/javascript');
readfile("1.php");

echo "var defProxy = ",		proxyFromGet('def'),	";\n";
echo "var v6Proxy = ",		proxyFromGet('v6'),		";\n";
echo "var pkuProxy = ",		proxyFromGet('pku'),	";\n";
echo "var abroadProxy = ",	proxyFromGet('abroad'),	";\n";
echo "var gfwProxy = ",		proxyFromGet('gfw'),	";\n";

readfile("2.php");
