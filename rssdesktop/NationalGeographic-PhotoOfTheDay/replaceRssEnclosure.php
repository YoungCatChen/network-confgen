<?php

$rss = $_SERVER['argv'][1];
$map = $_SERVER['argv'][2];

$rss = file_get_contents($rss);
$map = file($map);


$find = array();
$rep = array();

foreach ($map as $line)
{
	$parts = explode('  @@@  ', $line);
	if (!isset($parts[1]))
		continue;

	$parts[0] = trim($parts[0]);
	$find[] = '<feedburner:origLink>' . $parts[0];
	$rep[] = '<enclosure type="image/jpeg" url="{$BASE}/images/' 
		. basename(trim($parts[1])) . '" /><feedburner:origLink>' . $parts[0];
}

$rss = preg_replace('/<enclosure [^>]*>/', '', $rss);
$rss = str_replace($find, $rep, $rss);

echo $rss;
