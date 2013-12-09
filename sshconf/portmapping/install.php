<?php
require dirname(__FILE__) . '/' . '../../common.php';

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler('exception_error_handler'); 


try
{
	$argv = $_SERVER['argv'];
	array_shift($argv);
	$pm = new PortMapping($argv, 'sshconf/portmapping', false);

	$r = array('# vi'.'m: set ft=sshconfig:',
		'## Do not make changes to lines below. They may be overwritten by an auto-generator.',
		"## Add your directives above the '==== Generated start ====' line.", '',
		"#Host 127.102.*.*  # ASCII of 'f' is 102",
		'#User whoToLogin', '#IdentityFile /where/is/your/identify', '#ExitOnForwardFailure yes',
		'#BatchMode yes', '#TCPKeepAlive yes', '#ServerAliveInterval 300', '#Compression no');

	foreach ($pm->getConf('maps') as $i => $map)
	{
		$net = preg_replace('/\.\d+$/', '', trim($map['net_range']));
		$gwports = intval($map['gateway_ports_start']);

		for ($j = 1; $j <= 254; ++$j)
		{
			if (($j - 1) % 10 == 0)
			{
				$ipend = ($j - 1) / 10 + 1;
				$r[] = "\nHost  127.102.$i.$ipend  127.102.$i.254  127.102.254.$ipend  127.102.254.254";
			}

			$targetip = "$net.$j";
			list($ports, $override) = $pm->getPortsForTarget($targetip);

			for ($k = 0; $k <= 9; ++$k)
			{
				$gwport = $gwports + $j * 10 + $k;
				$tport = isset($ports[$k]) ? $ports[$k] : 7;

				if ($tport == -2)
					$tport = $gwport;

				$r[] = "LocalForward  *:$gwport  $targetip:$tport";
			}
		}

		$r[] = '';
	}

	echo join("\n", $r);
	exit(0);

} catch (Exception $ex) {
	fwrite(STDERR, $ex . "\n");
	exit(1);
}

