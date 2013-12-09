<?php
require dirname(__FILE__).'/../../common.php';

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler('exception_error_handler');

try
{
	// Get commandline arguments.
	$argv = $_SERVER['argv'];
	array_shift($argv);
	$inisection = getInisSection($argv, 'squidconf/peer-select');

	// Find peers.
	$peers = array();
	$peernamemaxlen = 0;
	$hostnamemaxlen = 0;

	foreach ($inisection['peers'] as $key => $value)
	{
		$parts = preg_split('/\s+/', $value);
		if (count($parts) < 1)
			throw new Exception("Invalid peer description: $key. Please check the conf.");

		$peers[$key]['host'] = $parts[0];
		$peernamemaxlen = max($peernamemaxlen, strlen($key));
		$hostnamemaxlen = max($hostnamemaxlen, strlen($parts[0]));

		if (count($parts) == 1)
		{
			if ($parts[0] == 'direct')
				$peers[$key]['port'] = 0;
			else
				throw new Exception("Invalid peer port: $key. Please check the conf.");
		}
		else
		{
			$peers[$key]['port'] = $parts[1];
		}

		if (count($parts) < 4)
		{
			$peers[$key]['user'] = $peers[$key]['pass'] = '';
		}
		else
		{
			$peers[$key]['user'] = $parts[2];
			$peers[$key]['pass'] = $parts[3];
		}
	}

	// Find padded peer names.
	foreach ($peers as $peername => &$peerdesc)
	{
		$peerdesc['peernamepad'] = str_pad($peername, $peernamemaxlen);
		$peerdesc['hostpad'] = str_pad($peerdesc['host'], $hostnamemaxlen);
		$peerdesc['portpad'] = str_pad($peerdesc['port'], 5);
	}

	// Find routing rules.
	unset($inisection['peers']);
	$rulesets = array();
	$rulesetnamemaxlen = 0;

	foreach ($inisection as $rulesetname => $ruleset)
	{
		if (! is_array($ruleset))
			continue;

		$rulesetnamemaxlen = max($rulesetnamemaxlen, strlen($rulesetname));

		foreach ($ruleset as $acl => $rulestr)
		{
			$rule = array();
			$parts = preg_split('/\s+/', $rulestr);

			if (count($parts) < 2)
				throw new Exception("Invalid rule: $acl. Please check the conf.");

			$url = $parts[0];
			array_shift($parts);

			if (strpos($url, '://') === false)
				$url = "http://$url";
			if (strpos($url, '?') === false)
				$url = $url . '/?';

			$rule['url'] = $url;
			$rule['peers'] = $parts;
			$ruleset[$acl] = $rule;
		}

		$rulesets[$rulesetname] = $ruleset;
		unset($inisection[$rulesetname]);
	}

	// Find padded rule names.
	$rulesetpad = array();
	foreach ($rulesets as $rulesetname => $ruleset)
		$rulesetpad[$rulesetname] = str_pad($rulesetname, $rulesetnamemaxlen);

	// Print our results.
	$inisection['peers'] = $peers;
	$inisection['rulesets'] = $rulesets;
	$inisection['rulesetpad'] = $rulesetpad;
	//print_r($inisection);
	var_export($inisection);

	// And finally exit.
	exit(0);
}
catch (Exception $ex)
{
	fwrite(STDERR, basename(__FILE__) . ': ' . $ex->getMessage() . "\n");
	exit(1);
}
