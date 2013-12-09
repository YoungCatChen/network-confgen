#!/usr/bin/gawk -f

$1 ~ "." && $1 !~ "^#"  {
	print "var P_" $1 " = 'PROXY " $2 "';";
}
