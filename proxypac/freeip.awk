#!/usr/bin/gawk -f

$1 ~ /./ && $1 !~ /^#/ {
	print "if (isInNet(ip, '" $1 "', '" $3 "')) return 1;";
}
