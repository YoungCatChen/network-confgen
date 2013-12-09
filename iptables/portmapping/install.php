<?php
require dirname(__FILE__) . '/' . '../../common.php';

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

set_error_handler('exception_error_handler'); 


try {
	$argv = $_SERVER['argv'];
	array_shift($argv);
	$pm = new PortMapping($argv, 'iptables/portmapping', true);
	$r = array('*nat', '');

	foreach ($pm->getConf('maps') as $i => $map) {
		$r[] = "-N MAPPRE$i\n-N MAPPOST$i\n";
		
		$net = preg_replace('/\.\d+$/', '', trim($map['net_range']));
		$gatewayip = trim($map['gateway_ip']);
		$gwports = intval($map['gateway_ports_start']);
		$ports = $pm->getConf('ports');

		foreach ($ports as $k => $tport) {
			if ($tport > 0)
				$r[] = "-A MAPPOST$i -d $net.0/24 -p tcp --dport $tport -j SNAT --to $gatewayip";
		}

		$r[] = '';

		for ($j = 1; $j <= 254; ++$j) {
			$targetip = "$net.$j";
			list($ports, $override) = $pm->getPortsForTarget($targetip);

			foreach ($ports as $k => $tport) {
				$gwport = $gwports + $j * 10 + $k;

				if ($tport == -2) {
					$tport = $gwport;
					$override[$tport] = 1;
				}

				$r[] = "-A MAPPRE$i -p tcp --dport $gwport -j DNAT --to $targetip:$tport";
			}

			foreach ($override as $tport => $nouse) {
				$r[] = "-A MAPPOST$i -d $targetip -p tcp --dport $tport -j SNAT --to $gatewayip";
			}
		}

		$r[] = "\n-A PREROUTING -p tcp -j MAPPRE$i\n"
			. "-A OUTPUT -p tcp -d $gatewayip -j MAPPRE$i\n"
			. "-A POSTROUTING -d $net.0/24 -p tcp -j MAPPOST$i\n";
	}

	$r[] = "COMMIT\n";
	echo join("\n", $r);
	exit(0);

} catch (Exception $ex) {
	fwrite(STDERR, $ex . "\n");
	exit(1);
}

