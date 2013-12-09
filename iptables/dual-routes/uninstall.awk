#!/usr/bin/gawk -f

BEGIN {
	print "*mangle";
	havecernet = 0;
}

/^:DUALPRE/ {
	havecernet = 1;
}

/^-A / && / -j DUALPRE/ {
	sub(/^-A /, "-D ", $0);
	print $0;
}

END {
	if (havecernet) {
		print "-F DUALPRE";
		print "-X DUALPRE";
	}

	print "COMMIT";
}
