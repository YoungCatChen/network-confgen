<?php

$pwd = dirname(__FILE__);
$base = dirname($_SERVER['PHP_SELF']);
$request = isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '';

if ($request == '' || preg_match('%/$%', $request))
	indexpage_noreturn();

if (!preg_match('%/([^/]+)\.([^./]+)$%m', $request, $matches))
	notfound_noreturn();

$name = $matches[1];
$type = $matches[2];

if ($type != 'rss' && $type != 'theme')
	notfound_noreturn();

$content = file_get_contents("$pwd/$name.$type.in");

if ($content === FALSE)
	notfound_noreturn();

$content = str_replace(
	array('{$HOST}', '{$BASE}', '{$RSS}'),
	array($_SERVER['HTTP_HOST'], "http://{$_SERVER['HTTP_HOST']}$base", "$name.rss"),
	$content);


if ($type == 'theme') {
	header('Content-type: application/octet-stream');
	header("Content-Disposition: attachment; filename=$name.theme");
} else {
	header('Content-type: text/xml; charset=utf-8');
}

echo $content;



function notfound_noreturn()
{
	header('HTTP/1.0 404 Not Found');
	header('Status: 404 Not Found');
	echo '<h1>404 Not Found</h1>';
	exit(1);
}

function indexpage_noreturn()
{
	global $pwd, $base;
	$files = scandir($pwd);
	$title = basename($base) . ' @ ' . $_SERVER['HTTP_HOST'];

	echo "<html><head><title>$title</title></head><body><h1>$title</h1><ul>";

	foreach ($files as $file)
	{
		if (strstr($file, '.theme.in'))
		{
			$x = str_replace('.theme.in', '.theme', $file);
			echo "<li><a href=\"$x\">$x</a></li>";
		}
	}

	echo '</ul></body></html>';
	exit(0);
}

