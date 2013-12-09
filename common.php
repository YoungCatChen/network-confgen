<?php

if (runFunc($result))
	exit($result);

function runFunc(&$retval)
{
	if (!isset($_SERVER['argv'][2]))
		return false;

	$args = $_SERVER['argv'];
	array_shift($args);
	$run = array_shift($args);
	$funcname = array_shift($args);

	if (!function_exists($funcname))
		return false;

	switch ($run)
	{
	case 'run':
		$retval = call_user_func_array($funcname, $args);
		return true;
	case 'print':
		$retval = call_user_func_array($funcname, $args);
		print_r($retval);
		return true;
	default:
		return false;
	}
}


function endsWith($haystack, $needle)
{
	$length = strlen($needle);
	$start = $length * -1;
	return (substr($haystack, $start) === $needle);
}

function checkCmdArg($arg)
{
	if (isset($_REQUEST[$arg]))
		return $_REQUEST[$arg];
	if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == $arg)
		return $arg;
	return false;
}


class INIConf
{
	public static function inis2mk()
	{
		$paths = func_get_args();
		$arr = array();
		$allenabled = array();
		$alldownloadable = array();

		foreach ($paths as $path)
		{
			$flat = self::flattenIni($path);
			$arr = array_merge($arr, $flat);
		}

		foreach ($arr as $key => $value)
		{
			if (preg_match('/^_([A-Z]+)_(.+)$/', $key, $matched))
			{
				switch ($matched[1])
				{
				case 'ENABLED':
					if ($value)
						$allenabled[] = $matched[2];
					break;
				case 'DOWNLOADABLE':
					$alldownloadable[] = $matched[2];
					break;
				}
			}
			else if (endsWith($key, '_vars'))
			{
				echo "$key = ", preg_replace('/[^ ]+/', '\$($0)', $value), "\n";
			}
			else
			{
				echo "export $key := $value\n";
			}
		}

		//echo "export all_enabled := ", join(' ', $allenabled), "\n";
		//echo "export all_downloadable := ", join(' ', $alldownloadable), "\n";
	}

	private static function flattenIni($path)
	{
		$dict = parse_ini_file($path, true);
		$arr = array();

		foreach ($dict as $key => $value)
		{
			if (is_array($value))
			{
				if (preg_match('/ paths$/', $key))
					$prefix = '';
				else
					$prefix = str_replace(array('/', ' '), '_', $key) . '_';

				foreach ($value as $key2 => $value2)
				{
					$arr[$prefix . $key2] = $value2;

					switch ($key2) {
					case 'enabled': $arr['_ENABLED_' . $key] = $value2; break;
					case 'url':     $arr['_DOWNLOADABLE_' . $key] = 1;  break;
					}
				}
			}
			else
			{
				$arr[$key] = $value;
			}
		}

		return $arr;
	}


	public static function getInisSection($patharray, $section)
	{
		$funcargs = func_get_args();
		$groupmatch = (count($funcargs) >= 3 ? $funcargs[2] : '');
		$arr = array();

		foreach ($patharray as $path)
		{
			$got = self::getIniSection($path, $section, $groupmatch);
			self::arrayMergeRecursive(&$arr, $got);
		}

		return $arr;
	}

	private static function getIniSection($path, $section)
	{
		$funcargs = func_get_args();
		$groupmatch = (count($funcargs) >= 3 ? $funcargs[2] : '');

		$dict = parse_ini_file($path, true);
		$dict['bin paths'][''] = '';
		$dict['dir paths'][''] = '';
		$arr = array_merge($dict['bin paths'], $dict['dir paths']);

		foreach ($dict as $sectionname => $sectiontree)
		{
			if ($sectionname == $section)
				self::arrayMergeRecursive(&$arr, $sectiontree);
			else if (strpos($sectionname, $section.'/') === 0)
				$arr[str_replace($section.'/', '', $sectionname)] = $sectiontree;
		}

		if ($groupmatch != '')
			self::walkAndGroup($groupmatch, &$arr);

		unset($arr['']);
		return $arr;
	}

	private static function walkAndGroup($groupmatch, &$arr)
	{
		$append = array();

		foreach ($arr as $key => &$value)
		{
			if (is_array($value))
				self::walkAndGroup($groupmatch, &$value);

			if (preg_match($groupmatch, $key, $matched))
			{
				$subkey = (count($matched) > 1 ? $matched[1] : $matched[0]);
				$replacedkey = preg_replace($groupmatch, '', $key);
				$append[$subkey][$replacedkey] =& $value;
				unset($arr[$key]);
			}
		}

		self::arrayMergeRecursive(&$arr, $append);
	}

	private static function arrayMergeRecursive(&$arr, $otherarr)
	{
		foreach ($otherarr as $key => $value)
		{
			if (is_array($value) && isset($arr[$key]) && is_array($arr[$key]))
				self::arrayMergeRecursive(&$arr[$key], $value);
			else
				$arr[$key] = $value;
		}
	}
}

function inis2mk()  { $x = func_get_args(); return call_user_func_array(array('INIConf', 'inis2mk'), $x); }
function getInisSection($patharray, $section)  { return INIConf::getInisSection($patharray, $section); }


class PortMapping
{
	private $configs;

	public function __construct($patharray, $section, $needGatewayIp)
	{
		$conf = INIConf::getInisSection($patharray, $section,
			'/^(port|map\d+_|\d+\.\d+\.\d+\.\d+_port)/');

		// Ports
		if (isset($conf['port']))
		{
			if (isset($conf['port']['s'])) // We have a [.../ports] section.
				$conf['ports'] = self::normalizePorts($conf['port']['s']);
			else // We have portX=... directives.
				$conf['ports'] = self::normalizePorts($conf['port']);

			unset($conf['port']);
		}
		else
		{
			// We don't have anything about ports.
			$conf['ports'] = array();
		}

		// Mapping ranges
		$conf['maps'] = array();

		for ($i=1; ; ++$i)
		{
			if (isset($conf["map$i"])) // We have a [.../mapX] section.
				$arr = $conf["map$i"];
			else if (isset($conf["map${i}_"])) // We have mapX_... directives.
				$arr = $conf["map${i}_"];
			else
				break;

			$conf['maps'][$i] = $arr;
			unset($conf["map$i"]);
			unset($conf["map${i}_"]);

			if (!isset($arr['net_range']) || !isset($arr['gateway_ports_start']) ||
				($needGatewayIp && !isset($arr['gateway_ip'])) )
			{
				throw new RuntimeException(
					"'net_range', 'gateway_ports_start' " .
					($needGatewayIp ? "and 'gateway_ip' " : '') .
					"must all exist for mapping policy No.$i.");
			}
		}

		$this->configs = $conf;
	}

	private function normalizePorts($ports)
	{
		$newarr = array();

		for ($i=0; $i<=9; ++$i) {
			if (!isset($ports["$i"]))
				continue;

			$val = $ports["$i"];

			if (trim($val) == '-') {
				$newarr[$i] = -2;
				continue;
			}

			$val = intval($val);

			if ($val <= 0 || $val >= 65536)
				continue;

			$newarr[$i] = $val;
		}

		return $newarr;
	}

	public function getConf($key)
	{
		return $this->configs[$key];
	}

	public function getPortsForTarget($targetip)
	{
		$generalports = $this->configs['ports'];
		$override = array();

		if (isset($this->configs[$targetip]))  // We have a [.../1.2.3.4] section.
			$ports = self::normalizePorts($this->configs[$targetip]);
		else if (isset($this->configs["{$targetip}_port"]))  // We have 1.2.3.4_portX directives.
			$ports = self::normalizePorts($this->configs["{$targetip}_port"]);
		else
			$ports = array();

		foreach ($ports as $ofs => $port)
		{
			$generalports[$ofs] = $port;
			$override[$port] = 1;
		}

		unset($override[-2]);
		return array($generalports, $override);
	}
}


function forkAsBackground()
{
	// Switch over to daemon mode.
	//  Thanks to comment posted by Tony at 29-Oct-2009 11:05
	//  on http://www.php.net/manual/en/function.pcntl-fork.php .

	if (! function_exists('_forkAsBackground_shutdown')) {
		function _forkAsBackground_shutdown() {
			posix_kill(posix_getpid(), SIGHUP);
		}
	}


	$args = func_get_args();
	$funcname = array_shift($args);

	if (!function_exists($funcname))
		return -1;


	if ($pid = pcntl_fork())
	{
		// We are the parent.
		pcntl_wait($status);
		return 0;
	}

	// We are the child.
	register_shutdown_function('_forkAsBackground_shutdown');

	// Discard the output buffer and close all of the standard file descriptors.
	try
	{
		@ob_end_clean();
		@fclose(STDIN);
		@fclose(STDOUT);
		@fclose(STDERR);
	} catch (Exception $ex) { }

		if (posix_setsid() < 0)
			exit(1);

		if ($pid = pcntl_fork())
			exit(0); // We are the parent.

		// We are the child.
		// Now running as a daemon. This process will even survive an apachectl stop.
		$r = call_user_func_array($funcname, $args);
		exit($r);
}


class RequireExtract
{
	private static function extractFunction($path, $funcname)
	{
		$content = file_get_contents($path);
		$pattern = '%\r?\nfunction ' . $funcname . '\([^\n]*\n*\{(\r?\n[^}][^\n]*)+\r?\n\}%';

		if (!preg_match($pattern, $content, $matched))
			return FALSE;

		return trim($matched[0]);
	}

	private static function callback($matched)
	{
		global $replaceRequire_dir;
		$arr = array("\n// ", trim($matched[0]), "\n");
		$arr[] = self::extractFunction($replaceRequire_dir . $matched[2], $matched[4]);
		$arr[] = "\n";
		return implode('', $arr);
	}

	public static function replaceRequire($path)
	{
		global $replaceRequire_dir;
		$replaceRequire_dir = dirname($path) . '/';
		$content = file_get_contents($path);

		echo preg_replace_callback(
			'%\nrequire(_once)?\b[^\r\n]+\bdirname\(__FILE__\)[^\r\n]+\'([^\'\r\n]+)\'[^\r\n]+(//|#) RequireFunction (\w+)%',
				array('RequireExtract', 'callback'), $content);
	}
}

function replaceRequire($path)  { return RequireExtract::replaceRequire($path); }

