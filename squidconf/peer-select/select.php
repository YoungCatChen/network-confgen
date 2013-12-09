<?php
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler('exception_error_handler');

// Format:
// cache_peer x parent x 0 no-query allow-miss no-digest name=x login=x:x
// cache_peer_access x allow x x

try
{
	// What task to do?
	$task = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';

	// Load the conf.
	$conf = file_get_contents(dirname(__FILE__) . '/conf.exported');
	eval('$conf = ' . $conf . ';');

	if ($task == 'gen')
	{
		gen_squid_conf($conf);
	}
	else if ($task == 'pick')
	{
		$r = gen_and_pick_squid_conf($conf);
		exit($r);
	}
	else
	{
		fwrite(STDERR, 'Usage: php ' . basename(__FILE__) . " gen|pick\n");
		exit(2);
	}

	exit(0);
}
catch (Exception $ex)
{
	fwrite(STDERR, basename(__FILE__) . ': ' . $ex->getMessage() . "\n");
	exit(1);
}


function gen_and_pick_squid_conf($conf)
{
	echo "Loading the old conf...";
	$dir = dirname(__FILE__);
	
	try
	{
		$oldcontents = file_get_contents("$dir/peer-select.conf");
		echo " Done.\n";
	}
	catch (Exception $ex)
	{
		$oldcontents = '';
		echo " Error. Treated it empty.\n";
	}

	echo "Generating new peer-select conf...";
	$contents = gen_squid_conf_into_str($conf);
	echo " Done.\nThe new one is";

	if ($oldcontents == $contents)
	{
		echo " identical to the old. No replacement needed.\nExit.\n";
		return 0;
	}

	echo " different from the old. Generating a brand new one to make sure...";
	$brandnewcontents = gen_squid_conf_into_str($conf);
	echo " Done.\nThe two newly generated confs";

	if ($contents != $brandnewcontents)
	{
		echo " differ. Can't determine if we should replace the old.\nExit.\n";
		return 1;
	}

	echo " are identical. Replacing the old...";
	file_put_contents("$dir/peer-select.conf.old", $oldcontents);
	file_put_contents("$dir/peer-select.conf", $contents);

	echo " Done.\nTelling squid to verify the whole conf...\n";
	$squid = $conf['squid'];

	try {
		passthru("$squid -k parse", &$r);
	} catch (Exception $ex) { }

	if ($r != 0)
	{
		echo "Telling squid to verify the whole conf... Failed. Squid has found error in the conf file.\nUndoing...";
		file_put_contents("$dir/peer-select.conf", $oldcontents);
		file_put_contents("$dir/peer-select.conf.newfailed", $contents);
		unlink("$dir/peer-select.conf.old");

		echo " Done.\nExit.\n";
		return 1;
	}

	echo "Telling squid to verify the whole conf... Done.\nReconfiguring squid...\n";

	try {
		passthru("$squid -k reconfigure", &$r);
	} catch (Exception $ex) { }

	if ($r != 0)
	{
		echo "Reconfiguring squid... Failed.\nExit.\n";
		return 1;
	}

	echo "Reconfiguring squid... Done.\nExit.\n";
	return 0;
}


function gen_squid_conf_into_str($conf)
{
	ob_start();
	gen_squid_conf($conf);
	$str = ob_get_contents();
	ob_end_clean();
	return $str;
}

function gen_squid_conf($conf)
{
	// Print all the peers.
	echo "# vim: set ft=squid:\n\n";
	$comment = (isset($conf['comment_out_peers']) && $conf['comment_out_peers']) ? '#' : '';

	foreach ($conf['peers'] as $peername => $peerdesc)
	{
		if ($peerdesc['host'] == 'direct')
			continue;

		if ($peerdesc['user'] != '')
			$login = "login={$peerdesc['user']}:{$peerdesc['pass']}";
		else
			$login = '';

		echo "{$comment}cache_peer {$peerdesc['hostpad']} parent ",
			"{$peerdesc['portpad']} 0 no-query allow-miss no-digest ",
			"name={$peerdesc['peernamepad']} $login\n";
	}

	echo "\n";

	// cURL to test.
	$curls = new Curls(isset($conf['timeout']) ? (int)($conf['timeout']) : 4);

	foreach ($conf['rulesets'] as $ruleset)
	{
		foreach ($ruleset as $rule)
		{
			foreach ($rule['peers'] as $peername)
			{
				if (isset($conf['peers'][$peername]))
				{
					$peerdesc = $conf['peers'][$peername];
					$curls->add($rule['url'], $peername, $peerdesc);
				}
			}
		}
	}

	$curls->exec();

	// Generate the squid directives.
	foreach ($conf['rulesets'] as $rulesetname => $ruleset)
	{
		// For each ACL, ...
		foreach ($ruleset as $acl => $rule)
		{
			// Find the route.
			$foundroute = null;

			foreach ($rule['peers'] as $peername)
			{
				$isgood = $curls->isgood($rule['url'], $peername);

				if ($isgood['good'])
				{
					$foundroute = $peername;
					break;
				}
			}

			// Print them out.
			foreach ($conf['peers'] as $peername => $peerdesc)
			{
				if ($peerdesc['host'] == 'direct')
					continue;

				echo ($foundroute ? 'cache_peer_access  ' : '#cache_peer_access '),
					$peerdesc['peernamepad'],
					($peername == $foundroute ? ' allow ' : ' deny  '),
					$conf['rulesetpad'][$rulesetname], ' ',
					str_replace('^', '!', $acl), "\n";
			}
		}

		// Print 'deny all's .
		foreach ($conf['peers'] as $peername => $peerdesc)
		{
			if ($peerdesc['host'] == 'direct')
				continue;
			echo "cache_peer_access  {$peerdesc['peernamepad']} ",
				"deny  {$conf['rulesetpad'][$rulesetname]} all\n";
		}

		echo "\n";
	}
}


class Curls
{
	var $mh;
	var $timeout;
	var $map = array();

	function __construct($timeout)
	{
		$this->mh = curl_multi_init();
		$this->timeout = $timeout;
	}

	function __destruct()
	{
		foreach ($this->map as $key => $ch)
		{
			$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
			#echo "# $key => $url\n";
			curl_multi_remove_handle($this->mh, $ch);
			curl_close($ch);
		}

		curl_multi_close($this->mh);
	}

	function getmap()
	{
		return $this->map;
	}

	function add($url, $proxyname, $proxydesc)
	{
		if (isset($this->map["$proxyname/$url"]))
			return;

		$ch = curl_init();
		$this->map["$proxyname $url"] = $ch;
		curl_multi_add_handle($this->mh, $ch);
		
		curl_setopt_array($ch, array(
			CURLOPT_URL            => $url,
			CURLOPT_NOBODY         => true,
			CURLOPT_HEADER         => true,
			CURLINFO_HEADER_OUT    => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_TIMEOUT        => $this->timeout,
		));

		if ($proxydesc['host'] != 'direct')
		{
			$phost = $proxydesc['host'];
			if (strpos($phost, ':') !== false)
				$phost = "[$phost]";

			curl_setopt_array($ch, array(
				CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
				CURLOPT_PROXY     => $phost,
				CURLOPT_PROXYPORT => $proxydesc['port'],
			));

			if ($proxydesc['user'] != '')
			{
				curl_setopt_array($ch, array(
					CURLOPT_PROXYAUTH => CURLAUTH_BASIC,
					CURLOPT_PROXYUSERPWD => $proxydesc['user'] . ':' . $proxydesc['pass'],
				));
			}
		}
	}

	function exec()
	{
		do {
			$mrc = curl_multi_exec($this->mh, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

		while ($active && $mrc == CURLM_OK)
		{
			if (curl_multi_select($this->mh, 4.2) != -1)
			{
				do {
					$mrc = curl_multi_exec($this->mh, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
	}

	function isgood($url, $proxyname)
	{
		$key = "$proxyname $url";

		if (!isset($this->map[$key]))
			return array('good' => false, 'found' => false);

		$ch = $this->map[$key];
		$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		$respcode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if (strpos($url, '://always/') !== false)
			return array('good' => true, 'found' => true);
		if ($respcode >= 100 && $respcode < 400)
			return array('good' => true, 'found' => true);

		return array('good' => false, 'found' => true);
	}
}

