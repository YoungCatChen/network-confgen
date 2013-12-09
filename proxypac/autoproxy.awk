#!/usr/bin/gawk -f

function wcToRegExp(line) {
	return line;
}

function genProxyLine(line, shouldproxy) {
	regexp = 0;
	find = "";

	if (length(line) == 0 || line ~ "^[;![]") {
		return "";

	} else if (line ~ "^[|][|]") {
		regexp = 1;
		find = "^[\\w\\-]+:\\/+(?!\\/)(?:[^\\/]+\\.)?" wcToRegExp(substr(line, 3));

	} else if (line ~ "^[|]") {
		find = substr(line, 2) "*";

	} else if (line ~ "[|]$") {
		find = "*" substr(line, 1, length(line-1));

	} else if (line ~ "^/.+/$") {
		regexp = 1;
		find = substr(line, 2, length(line)-2);

	} else {
		find = "http://*" line "*";
	}

	return "if (" (regexp?"reg":"sh") "ExpMatch(url, '" find "')) return " shouldproxy ";";
}

BEGIN {
	nlines = 0;
}

$0 ~ "^@@" {
	print genProxyLine(substr($0, 3), 0);
}

$0 !~ "^@@" {
	lines[nlines++] = genProxyLine($0, 1);
}

END {
	for (line in lines) {
		print lines[line];
	}
}
