#!/usr/bin/gawk -f

BEGIN {
	print "*nat";
}

/^:MAP(PRE|POST)/ {
	sub(/^:/, "", $1);
	print "-F", $1;
	chains[length(chains)] = $1;
}

/^-A / && / -j MAP(PRE|POST)/ {
	sub(/^-A /, "-D ", $0);
	print $0;
}

END {
	for (chain in chains)
		print "-X", chains[chain];

	print "COMMIT";
}
